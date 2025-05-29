<!DOCTYPE html>
<html>
    <head>
	<title>M & A Galleries</title>
	<link rel="stylesheet" href="gcss.css">
	<script type="text/javascript" src="jscmn.js"></script>
    </head>
    <body style="overflow-y: hidden;" onload="setgridheight()" onresize="setgridheight()">
<?php
include ("gcmn.php");
$files = scandir ($agallerydir);
$i = 0;
$ncells = 5;
$previewdir = '/medium/';
$curfiles = array ('__title' => '');
// gather the pertinent files
foreach ($files as $file) {
    $afile = $agallerydir . $file;
    if ($file == '.' || $file == '..' || ! is_dir($afile))
	continue;
    $curfiles[] = $file;
}
$gjconfig = get_jconfig($agallerydir);
// the header
print '<div id="display_head" class="center fs48 redtext menunav" style="padding: 20px;">Casa Sanchez and Morningwood Farms</div>';
// the main thumb grid
print '<div id="display_grid" style="overflow-y: auto; height:500px">';
print "<table border=0 align=center>";
print '<tr>';
foreach ($files as $file) {
    $afile = $agallerydir . $file;
    $hfile = $hgallerydir . $file;
    if ($file == '.' || $file == '..' || ! is_dir($afile))
	continue;
    // cons up an ent if missing
    if (! isset ($gjconfig['data'][$file]))
	$gjconfig['data'][$file] = array ('desc' => $file, 'hidden' => false);
    if ($gjconfig['data'][$file]['desc'])
	$title = $gjconfig['data'][$file]['desc'];
    else
	$title = $file;
    $ishidden = $gjconfig['data'][$file]['hidden'];
    // poor man's access control
    if ($ishidden && ! is_localip ())
	continue;
    $thumb = htmlspecialchars (get_thumb ($afile, $hfile, $file));
    $hid = '<div>' . ($ishidden ? '[hidden from public]' : '&nbsp;') . '</div';
    $subtitle = "($file)";
    print ("<td align=center valign=bottom><a href=\"gallery.php?d=$file\"><img class=thumbimg src=\"$thumb\" onload=\"thumbload(this)\"><br></a><span class=fs20 onclick=\"editTitle(this, '', '$file')\">$title</span><div class=fsi16>$subtitle$hid</div></td>");
    if (++$i >= $ncells) {
	$i = 0;
	print ("</tr><tr>");
    }
}

print ("</tr></table></div>");

function get_thumb ($adir, $hdir, $dir, $tdir = '/medium/') {
    $pdir = $adir . $tdir;
    if (! is_dir ($pdir)) {
	error_log ("not found $pdir");
	$pdir = $adir;
	$preview = $hdir . '/';
    } else {
	$preview = $hdir . $tdir;
    }
    $files = scandir ($pdir);
    if (! $files || count ($files) < 3) {
	$pdir = $adir;
	$preview = $hdir . '/';
	$files = scandir ($pdir);
    }
    $pics = array ("jpg", "JPG", "jpeg", "JPEG", "png", "PNG");
    $jconfig = get_jconfig ($adir);
    foreach ($files as $file) {
	if  ($file == '.' || $file == '..' || ! in_array(substr($file, strrpos($file, '.') + 1), $pics))
	    continue;
	if (! is_file ($pdir . '/' .$file))
	    continue;
	if (isset ($jconfig['data'][$file]) && $jconfig['data'][$file]['hidden'])
	    continue;
	return $preview . $file;
    }
    return $hdir;
}
?>
	<script type="text/javascript">
	 var hgallerydir = '<?php print $hgallerydir; ?>';
	 var gjconfig = <?php print json_encode ($gjconfig); ?>;	 	 
	 var curfiles = <?php print json_encode ($curfiles); ?>;
	 var curdir = '';
	 var is_local = <?php print is_localip () ? 'true' : 'false'; ?>;	 
	</script>
    </body>
</html>
