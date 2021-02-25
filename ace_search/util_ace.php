<?php
 require_once "util.php";

 // 1 - very verbose debug messages
 // 2 - debug messages
 // 3 - info messages
 // 4 - error messages
 $ACELogLevel=4;
 function AceLog($severity,$msg)
 {
    global $ACELogLevel;
    if ($severity>=$ACELogLevel) echo "$msg\n";
 }

 const AS_JSON_URL = "https://search.acestream.net/all?api_version=1.0&api_key=test_api_key";
 const AVAIL_THRESHOLD = (8*86400);

 function AceFixChanName($name)
 {
   return preg_replace('/[\x00-\x1F]/', '', $name);
 }

 function AceGetChannelListJson(&$chns,$fname_prev,$chn_exp_time)
 {
   $resp = curl_get_contents(AS_JSON_URL,"okhttp/2.5.0",5000,5000);
   if ($resp["code"]==200)
   {
     $a=json_decode($resp["resp"]);
     if ($a)
     {
       if (count($a)<100)
	 AceLog(4,"too few records in array can be bad");
       else
       {
         $prev = file_exists($fname_prev) ? json_decode(file_get_contents($fname_prev),TRUE) : NULL;
         if (!is_array($prev)) $prev = [];
         $tnow = time();
         $chns = [];
         foreach ($a as $c)
         {
	    $avail = isset($c->availability) ? $c->availability : 0;
	    $upd = $c->availability_updated_at;
	    if ($avail<0.8 || ($tnow-$upd)>AVAIL_THRESHOLD) continue;
	    $name = AceFixChanName($c->name);
	    if (!array_key_exists($name,$chns) ||
		array_key_exists($name,$chns) && $chns[$name]["upd"] < $upd)
	    {
	    	$updt = $tnow-$upd;
		$cat = (isset($c->categories) && !empty($c->categories) && !empty($c->categories[0])) ? $c->categories[0] : "none";
		$chns[$name] = array('name' => $name, 'avail' => $avail, 'upd' => $upd, 'cat' => $cat, 'infohash' => $c->infohash, "t" => $tnow);
                AceLog(2, "adding search channel \"$name\" (infohash ".$c->infohash." upd $updt)");
	    }
	 }
	 foreach ($prev as $c)
	 {
	    $name = $c["name"];
    	    $upd = $c["upd"];
	    $age = $tnow-$c["t"];
	    $updt = $tnow-$upd;
	    if (($tnow-$c["t"])<$chn_exp_time && ($tnow-$upd)<AVAIL_THRESHOLD)
	    {
                if (!array_key_exists($name, $chns))
		{
		    $chns[$name] = $c;
                    AceLog(2, "adding previous search channel \"$name\" (infohash ".$c["infohash"]." age $age upd $updt)");
		}
		else if ($chns[$name]["upd"] < $upd)
		{
		    $chns[$name] = $c;
                    AceLog(2, "replacing search channel \"$name\" ".$c["infohash"].")");
		    if ($chns[$name]["infohash"] != $c["infohash"])
                	AceLog(2, "infohash mismatch for \"$name\" (infohash ".$chns[$name]["infohash"]." => ".$c["infohash"]." age $age upd $updt)");
		}
	    }
	 }
         file_put_contents($fname_prev,json_encode($chns,JSON_UNESCAPED_UNICODE));
         return true;
       }
     }
     else
       AceLog(4,"bad json");
  }
  else
    AceLog(4,"json download failed");
  return false;
}

?>
