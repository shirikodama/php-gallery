<?php

include ("gcmn.php");

if (! is_localip ())
    die ("{\"sts\": \"403 not authorized (must be on local network)\"}");    
$dir = $_POST["dir"];
$file = $_POST["file"];
$hidden = $_POST["hidden"] == "true" ? true : false;
$jconfig = get_jconfig ($agallerydir . '/' . $dir);
$jconfig['data'][$file]['hidden'] = $hidden;
if (write_jconfig ($jconfig, $dir))
    $status = "200 ok";
else
    $status = "500 can't write";
die ("{\"sts\": \"$status\"}");
?>
