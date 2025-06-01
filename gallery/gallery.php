<!DOCTYPE html>
<html>
    <head>
	<title>M & A Gallery</title>
	<link rel="stylesheet" href="gcss.css">
	<script type="text/javascript" src="jscmn.js"></script>
	<script type="text/javascript" src="gallery.js"></script>
    </head>
    <body style="overflow-y: hidden;" onload="setgridheight ()" onresize="setgridheight()">
       <div id="display_div" class="display_div" style="display:none"></div>
<?php
include ("gcmn.php");
$dir = $_GET["d"];
if (! is_dir($agallerydir . $dir))
    die ("<center><h1> Gallery not found: $dir</h1></center>");
$files = scandir ($agallerydir . $dir);
$curfiles = [];
$pics = array ("jpg", "jpeg", "png");
$movies = array ("avi", "mp4", "mov", "ogg", 'webm');
$picmov = array_merge ($pics, $movies);
$i = 0;
$ncells = 5;
$tdir = '/medium/';
$gfiles = get_index ();
$prevnext = get_prevnext ($dir, $gfiles);
$gjconfig = get_jconfig($agallerydir);
$jconfig = get_jconfig($agallerydir . $dir);

if (! isset($gjconfig['data'][$dir]))
    $gjconfig['data'][$dir] = array ('desc' => $dir, 'hidden' => false);
if ($gjconfig['data'][$dir]['hidden'])
    $galleryhidden = true;
else
    $galleryhidden = false;
if ($gjconfig['data'][$dir]['desc'])
    $title = $gjconfig['data'][$dir]['desc'];
else
    $title = $dir;
$dtitle = "($dir)";
if ($galleryhidden)
    $dhidden = '[hidden from public]';
else
    $dhidden = '&nbsp;';
$jsdir = jsesc ($dir);
print '<div>';
print("<div id=\"display_head\" class=\"display_nav\"><table width=100% cellpadding=0 class=\"menunav\"><tr >");
$pn = $prevnext['prev'];
print "<td width=5% align=center valign=bottom>";
print '<span style="float:left; padding-left:20px; padding-top:5px"><a href="index.php"><img title="Gallery Index" width=48 src="house.png"></a></span></td><td width=20% valign=bottom>';
if ($pn)
    print "<a href=\"gallery.php?d=$pn\"><img width=32 src=arrow_left_green.png><span class=fs20 style=\"padding-left:20px\">($pn)</span></a>";
print "</td>";
$cspan = $ncells;
if ($gjconfig['data'][$dir]['hidden']) {
    $img = "open-eye.png";
    $htitle = 'Unhide Gallery';
} else {
    $img = "hide.png";
    $htitle = 'Hide Gallery';    
}
$htitle = '';
if (is_localip ())
    $dtoggle = "<br><span id=\"gallery-hidden\">";
else
    $dtoggle = '<br>';
print "<td class=\"redtext fsi20\" colspan=$cspan valign=bottom align=center class=fs20><span style\"padding-left:10px\" class=\"fs32\" onclick=\"editTitle(this, '', '$jsdir')\">$title</span><br>$dtitle$dtoggle$dhidden</span></td>";

$pn = $prevnext['next'];
print "<td width=20% valign=bottom align=right valign=bottom style=\"padding-right:20px\">";
if ($pn)
    print "<a href=\"gallery.php?d=$pn\"><span class=fs20 style=\"padding-right:20px\">($pn)</span><img width=32 src=arrow_right_green.png></a>";
print "</td>";
print "<td width=5% valign=bottom align=center>";
if (is_localip ())
    print "<img title=\"toggle gallery public visibility\" width=16 src=$img onclick=\"toggleHide('', this, '$dir')\" class=hbutton>$htitle";
print "</td>";
print "</tr></table></div>";     // display_head
// seems to be getting cutoff
// the grid for the gallery thumbnails
print '<div id="display_grid" style="height:500px; overflow-y: auto; width:100%">';
print '<table style="width:100%; padding:20px;"><tr>';
foreach ($files as $file) {
    $ftype = strtolower (substr($file, strrpos($file, '.') + 1));
    if  ($file == '.' || $file == '..' || ! in_array($ftype, $picmov))
	continue;
    if (! isset ($jconfig['data'][$file]))
	$jconfig['data'][$file] = array ('desc' => $file, 'hidden' => false);
    $ishidden = $jconfig['data'][$file]['hidden'];
    if (($galleryhidden || $ishidden) && ! is_localip ())
	continue;    
    $hfile = $hgallerydir . $dir . '/' . $file;
    $pfile = $hgallerydir . $dir . $tdir . $file;
    $apfile = $agallerydir . $dir . $tdir . $file;
    if (in_array ($ftype, $movies)) {
	// ignore anything that's not an mp4
	if ($ftype != 'mp4' && $ftype != 'ogg' && $ftype != 'webm')
	    continue;	
	$vfile = $apfile . '.jpg';
	if (is_file ($vfile)) {
	    $tfile = $pfile . '.jpg';
	} else
	$tfile = 'video-camera-icon.png';
	$style = 'style="border:4px dashed gold""';
    } else {
	if (! is_file ($apfile))
	    $pfile = $hgallerydir . $dir . '/' . $file;
	$tfile = htmlspecialchars ($pfile);
	$style = 'style="border:2px solid gold""';	
    }
    $curfiles[] = $file;    
    $hid = ($galleryhidden || $ishidden) ? "[hidden from public]" : '&nbsp;';
    if (isset ($jconfig['data'][$file])) {
	$title = $jconfig['data'][$file]['desc'];
	if (! $title) $title = $file;
    } else {
        $title = $file;
    }
    $title = abbrev($title, 20, 520);
    $sf = jsesc ($file);
    print ("<td style=\"max-width:20%\" align=center valign=bottom><a onclick=\"display_cur('$sf')\"><img $style class=thumbimg src=\"$tfile\" onload=\"thumbload(this)\"></a><br><span class=\"fs20 titlePadding\" onclick=\"editTitle(this, '$dir', '$file')\" id=\"gallery-title-$file\">$title</span><br><span class=fsi16 id=\"gallery-hidden-$file\">$hid</span></td>");
    if (++$i == $ncells) {
	$i = 0;
	print ("</tr><tr >");
    }    
}
print ("</tr></table>");  // grid table
print ("</div>");         // display_grid
print '</div>';

function jsesc ($str) {
    return str_replace ("'", "\\'", $str);
}

function get_prevnext ($dir, $dirs) {
    $i = 0;
    $prev = $next = NULL;
    $rv = array ('prev' => $prev, 'next' => $next);
    foreach ($dirs as $d) {	
	if ($d == $dir) {
	    if ($i > 0)
		$prev = $dirs [$i-1];
	    if ($i < count ($dirs)-1)
		$next = $dirs [$i+1];
	    $rv ['prev'] = $prev;
	    $rv ['next'] = $next;	    
	    return $rv; 
	}
	$i++;
    }
    return $rv;
}

?>
       <script type="text/javascript">
	var hgallerydir = '<?php print $hgallerydir; ?>';
	var gfiles = <?php print json_encode ($gfiles); ?>;
	var jconfig = <?php print json_encode ($jconfig); ?>;
	var gjconfig = <?php print json_encode ($gjconfig); ?>;
	var curfiles = <?php print json_encode ($curfiles); ?>;
	var curdir = '<?php print $dir; ?>';
	var is_local = <?php print is_localip () ? 'true' : 'false'; ?>;
	var movies = <?php print json_encode($movies); ?>;
	var curfile = "";
	var gallery_pn = <?php print json_encode($prevnext); ?>;
       </script>
    </body>
</html>
