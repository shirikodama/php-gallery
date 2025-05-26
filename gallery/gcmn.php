<?php
$agallerydir = "/var/www/html/pubgallery/galleries/";
$hgallerydir = "/pubgallery/galleries/";
$twidth = 192;

function is_localip () {
    $ip = $_SERVER['REMOTE_ADDR'];
    // something is "local" if it's not on the local subnet and it's not the router. this is lame, but i don't want to set up real access control
    if (strpos ($ip, "192.168.1.") !== 0 || strpos ($ip, "192.168.1.1") === 0)
	return false;
    return true;
}
    
function get_index () {
    global $agallerydir;
    $files = scandir ($agallerydir);
    $rv = array ();
    foreach ($files as $dir) {
	if (! is_dir ($agallerydir . $dir) || $dir == '.' || $dir == '..')
	    continue;
	$rv [] = $dir;
    }
    return $rv;
}

function get_jconfig ($adir, $file = '/config.json') {
    $rv = array ();
    $c = @file_get_contents ($adir . $file);    
    if (! $c) {
	error_log ("$adir$file not found");
	return array ('meta' => array ('desc' => '', 'hidden' => false), 'data' => array ());
    }
    $rv = json_decode ($c, true);
    return $rv;
}

function write_jconfig ($config, $dir) {
    global $agallerydir;
    $json = json_encode ($config);
    return file_put_contents ($agallerydir . $dir . '/config.json', $json);
}

function abbrev ($str, $charlen, $maxlen) {
    if ((strlen($str) * $charlen) > $maxlen) {
	$maxchars = ($maxlen - 3*$charlen) / $charlen;
	$rv =  substr ($str, 0, $maxchars) . '...';
	return $rv;
    }
    return $str;
}


?>
