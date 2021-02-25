<?php
 require_once "util_ace.php";

 function WriteM3uEntry($F,$cat,$name,$infohash,$aceserver)
 {
    fwrite($F,"#EXTINF:-1 group-title=\"$cat\",$name\nhttp://$aceserver/ace/getstream?infohash=$infohash\n");
 }
 function WriteFile(&$chns,$fname,$aceserver)
 {
    if (!($F = fopen("$fname.m3u", "wt")))
    {
        AceLog(4, "WriteFile: couldn't create $fname.m3u");
        return false;
    }
    fwrite($F,"#EXTM3U\n");
    $cats=[];
    foreach ($chns as $c)
    {
	WriteM3uEntry($F,$c["cat"],$c["name"],$c["infohash"],$aceserver);
        $cats[$c["cat"]]='x';
    }
    fclose($F);
    foreach($cats as $cat => $x)
    {
	$catfix = FixFilename($cat);
	if (!strlen($catfix)) continue;
    	$fn = "$fname.$catfix.m3u";
	if (!($F = fopen($fn, "wt")))
        {
            AceLog(4, "WriteFile: couldn't create $fn");
            return false;
        }
	foreach ($chns as $c)
        {
	    if ($c["cat"] == $cat)
	        WriteM3uEntry($F,$c["cat"],$c["name"],$c["infohash"],$aceserver);
	}
        fclose($F);
    }
    return true;
 }
 
 function cmp_nocase($a, $b)
 {
    return strcmp(mb_strtoupper(trim($a)), mb_strtoupper(trim($b)));
 }

 if ($argc<2)
 {
    echo "params : output_file_basename [aceserver]\n";
    die(1);
 }
 
 $fname = $argv[1];
 $aceserver = $argc>=3 ? $argv[2] : "127.0.0.1:6878";
 $fname_prev = "$fname.prev.json";
 $ACELogLevel=2;

 if (AceGetChannelListJson($chns,$fname_prev,86400))
 {
    uksort($chns,"cmp_nocase");
    exit (WriteFile($chns,$fname,$aceserver) ? 0 : 2);
 }
 else
    exit(1);

?>
