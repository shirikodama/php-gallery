<?php

include ("gcmn.php");

if (! is_localip ())
    die ("{\"sts\": \"403 not authorized (must be on local network)\"}");    
$dir = $_POST["dir"];
$file = $_POST["file"];
$title = $_POST["title"];
$jconfig = get_jconfig ($agallerydir . '/' . $dir);
if (! isset ($jconfig['data'][$file]))
    $jconfig['data'][$file] = array ('desc' => $file, 'hidden' => false);
$jconfig['data'][$file]['desc'] = $title;
if (write_jconfig ($jconfig, $dir))
    $status = "200 ok";
else
    $status = "500 can't write";
die ("{\"sts\": \"$status\"}");
?>
