<?php

 function curl_init_session($ua,$timeout_ms,$conn_timeout_ms)
 {
    $curl = curl_init();
    if ($curl!==FALSE)
     curl_setopt_array($curl, array(
 	CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_USERAGENT => $ua,
	CURLOPT_TIMEOUT_MS => $timeout_ms,
	CURLOPT_CONNECTTIMEOUT_MS => $conn_timeout_ms,
	CURLOPT_ACCEPT_ENCODING => "",
	CURLOPT_SSL_VERIFYPEER =>false
    ));
    return $curl;
 }
 function curl_get_contents2($curl,$url)
 {
    curl_setopt($curl,CURLOPT_URL,$url);
    $resp = curl_exec($curl);
    $http_code=curl_getinfo($curl,CURLINFO_HTTP_CODE);
    return array("code"=>$http_code,"resp"=>$resp);
 }

 function curl_get_contents($url,$ua,$timeout_ms,$conn_timeout_ms)
 {
    $curl = curl_init_session($ua,$timeout_ms,$conn_timeout_ms);
    if ($curl===FALSE) return FALSE;
    curl_setopt($curl,CURLOPT_URL,$url);
    $resp = curl_exec($curl);
    $http_code=curl_getinfo($curl,CURLINFO_HTTP_CODE);
    curl_close($curl);
    return array("code"=>$http_code,"resp"=>$resp);
 }

 function FixFilename($fname)
 {
  $fname=str_replace(array("\"","\\","//",":","|"),"_",$fname);
  if (strtoupper(substr(PHP_OS, 0, 3))!="WIN")
    return $fname;
  $ffix = iconv("UTF-8", "windows-1251//TRANSLIT//IGNORE", $fname);
  return $ffix===NULL ? $fname : $ffix;
 }

?>
