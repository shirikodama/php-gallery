<!DOCTYPE html>
<html>
    <head>
	<title>M & A Gallery</title>
	<link rel="stylesheet" href="gcss.css">
    </head>
    <!-- was overflow hidden -->
    <body  style="overflow-y: auto" onload="setgridheight ()" onresize="setgridheight ()">
	<script type="text/javascript">
	    <?php print file_get_contents ("jscmn.js"); ?>
	</script>
	<!-- div for the image popup expander -->
	<div id="display_div" class="display_div"></div>
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

if (isset($gjconfig['data'][$dir]) && $gjconfig['data'][$dir]['hidden'])
    $galleryhidden = true;
else
    $galleryhidden = false;
$title = $dir;
// use the gjconfig file in preference for title
if ($gjconfig['data'][$dir]['desc'])
    $title = $gjconfig['data'][$dir]['desc'];
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
print "<td class=\"redtext fsi20\" colspan=$cspan valign=bottom align=center class=fs20><span style\"padding-left:10px\" class=\"fs30\" onclick=\"editTitle(this, '', '$jsdir')\">$title</span><br>$dtitle$dtoggle$dhidden</span></td>";

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
//print '<div id="display_grid" style="height:1500px; overflow-y:auto; width:100%">';                            // the grid for the gallery thumbnails
print '<div id="display_grid" style="width:100%">';                            // the grid for the gallery thumbnails
print '<table style="width:100%; padding:20px;"><tr>';
foreach ($files as $file) {
    $ftype = strtolower (substr($file, strrpos($file, '.') + 1));
    if  ($file == '.' || $file == '..' || ! in_array($ftype, $picmov))
	continue;
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
    } else
	$title = $file;
    $sf = jsesc ($file);
    print ("<td align=center valign=bottom><a onclick=\"display_cur('$sf')\"><img $style class=thumbimg src=\"$tfile\" onload=\"thumbload(this)\"></a><br><span class=\"fs20 titlePadding\" onclick=\"editTitle(this, '$dir', '$file')\" id=\"gallery-title-$file\">$title</span><br><span class=fsi15 id=\"gallery-hidden-$file\">$hid</span></td>");
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

	 function setgridheight () {
	     var h = window.innerHeight;
	     var dh = document.getElementById ('display_head').offsetHeight;
	     var dg = document.getElementById ('display_grid');
	     dg.style.height = (h-dh)+500+'px';
	 }		 

	 function display_close () {
	     var el = document.getElementById ('cur_video');
	     if (el)
		 el.pause ();
	     var el = document.getElementById ('display_div');
	     el.style.display = 'none';
	 }

	 function display_imgtoggle (el) {
	     if (el.toggled) {
		 el.style.height = el.oheight+'px';
		 el.style.width = el.owidth+'px';
		 el.title = 'click for natural size (' + el.naturalWidth + 'x' + el.naturalHeight + ')';
		 el.toggled = false;
	     } else {
		 el.style.height = el.naturalHeight+'px';
		 el.style.width = el.naturalWidth+'px';
		 el.title = 'click for normal fit size ('+ el.owidth + 'x' + el.oheight + ')';
		 el.toggled = true;
	     }
	 }

	 function display_cur (cur) {
	     var hfile = hgallerydir + curdir + '/' + cur;
	     var pn = get_prevnext  (cur, curfiles);
	     var prev = pn['prev'];
	     var next = pn['next'];
	     var title, hidden;
	     var ent = jconfig.data[cur];

	     if (ent) {
		 title = ent.desc || cur;
		 hidden = '<span id="hidden-'+cur+'" class=fsi15>';
		 hidden += (ent.hidden ? '[hidden from public]' : '');
		 hidden += '</span>';
	     } else {
		 console.log ("no ent ", cur);
		 title = cur;
		 hidden = '';
	     }
	     var el = document.getElementById ('display_div');
	     el.style.top = window.scrollY+'px';
	     el.style.display = 'block';	     
	     var html = '';
	     var movie_type = null;
	     var cur_l = cur.toLowerCase ();
	     for (var idx in movies) {
		 var m = movies [idx];
		 var l = m.length;
		 if (cur_l.indexOf ('.'+m) >= 0) {
		     movie_type = m;
		     break;
		 }
	     }
	     html += '<table width=100% align=center>';
	     // start of header
	     html += '<tr id="cur_header" >';
	     html += '<td class="panetop" valign=top><a onclick="display_close ()"> <img width=32 src=erase.png></a></td>';
	     html += '<td width=160>';
	     if (prev) {
		 html += '<a onclick="display_cur(\''+jsesc(prev)+'\')"><img width=32 src=arrow_left_green.png><br>' +prev+'</a>';
	     }
	     html += '</td>';
	     html += '<td align=center class="fs40 redtext"><span onclick="editTitle(this, \''+curdir+'\', \''+cur+'\')">' + title +'</span><br><span class=fs20>('+cur+')</span><br>'+hidden;
	     var img = jconfig.data[cur].hidden ? "open-eye.png" : "hide.png";
	     html += '<td width=160 align=right>';
	     if (next) {
		 html += '<a onclick="display_cur(\''+jsesc(next)+'\')"><img width=32 src=arrow_right_green.png><br>' +next+'</a>';
	     }  
	     html += '</td>';
	     html += '</td><td align=right>';
	     if (is_local)
		 html += '<span><img width=20 src='+img+' onclick="toggleHide(\''+curdir+'\', this, \''+cur+'\')" class=hbutton></span>';
	     html += '</td></tr>'; 
	     // start of body
	     html += '<tr><td id="cur_img" align=center valign=top colspan=5>';
	     if (movie_type) {
		 switch (movie_type) {
		     case 'MP4':
		     case 'mp4':
			 html += '<video id=cur_video width="1024" height="768" controls>';
			 html += '<source src="'+hfile+'" type="video/mp4">';
			 html += '</video>';			 
			 break;
		     case 'ogg':
			 html += '<video id=cur_video width="1024" height="768" controls>';
			 html += '<source src="'+hfile+'" type="video/ogg">';
			 html += '</video>';			 
			 break;
		     case 'webm':
			 html += '<video id=cur_video width="1024" height="768" controls>';
			 html += '<source src="'+hfile+'" type="video/webm">';
			 html += '</video>';			 
			 break;
		     default:
			 html += 'Your browser does not support '+movie_type;
			 break;
		 }
	     } else {
		 html += '<img onload="imgload(this)" class="paneimg" src="' + htmlquote (hfile) + '" title="click for natural size" onclick="display_imgtoggle(this)">';
	     }
	     html += '</td></tr>';
	     // start of footer
	     var img = jconfig.data[cur].hidden ? "open-eye.png" : "hide.png";
	     html += '<table id="cur_footer" width=100%><tr>';
	     html += '<tr><th>Size</th><th>Camera</th><th>Date</th></tr><tr>';
	     html += '<td width=25% align=center>';
	     if (ent.dx && ent.dy)
		 html += '<span class=fs20>'+ent.dx + 'x' +ent.dy+'</span>';
	     html += '</td><td width=50% align=center>';
	     if (ent.make) {
		 html += '<span class=fs20 style="display:table-cell;">'+ent.make;
		 if (ent.model)
		     html += '/'+ent.model;		 
		 html += '</span>';
		     }
	     html += '</td><td width=25% align=center>';
	     if (ent.date)
		 html += '<span class=fs20 style="width:70%; display:table-cell; margin:0 auto;" >'+ent.date+' </span>';
	     html += '</td>';
	     html += '</tr></table>';
	     html += '</td>';
	     html += '</table>';
	     el.innerHTML = html;
	 }

	 function get_prevnext (curfile, curfiles) {
	     var prev = next = null;
	     var rv = {'prev': prev, 'next': next};
	     
	     for (var i = 0; i < curfiles.length; i++) {
		 var cur = curfiles[i];
		 if (cur == curfile) {
		     if (i > 0)
			 prev = curfiles [i-1];
		     if (i < curfiles.length-1)
			 next = curfiles [i+1];
		     rv ['prev'] = prev;
		     rv ['next'] = next;	    
		     return rv; 
		 }
	     }
	     return rv;
	 }

	 document.onkeydown = function(evt) {
	     evt = evt || window.event;
	     var isEscape = false;
	     if ("key" in evt) {
		 isEscape = (evt.key === "Escape" || evt.key === "Esc");
	     } else {
		 isEscape = (evt.keyCode === 27);
	     }
	     if (isEscape) {
		 display_close ();
	     }
	 };

	 function imgload (el) {
	     // bordersize is actually the css border-radius of display_div * 2 (16 for each border)
	     var bordersize = 16*2;
	     var dispel = document.getElementById ('display_div');
	     var headel = document.getElementById ('cur_header');
	     var footel = document.getElementById ('cur_footer');
	     var imgel = document.getElementById ('cur_img');
	     var maxw = (window.innerWidth-bordersize);
	     // the max height the pic can be is screen heigyh - the header - footer - borders
	     var maxh = (window.innerHeight-(headel.offsetHeight+footel.offsetHeight+bordersize));
	     var imgratio = el.naturalHeight / el.naturalWidth;
	     var w = maxw;
	     var h = imgratio * maxw;
	     dispel.style.width = maxw+'px';
	     if (h > maxh) {
		 h = maxh;
		 w = maxh*(1/imgratio);
	     }
	     el.style.width = w +'px';
	     el.style.height = h + 'px';
	     el.owidth = el.width;
	     el.oheight = el.height;
	     el.toggle = false;
	     el.title = 'click for natural size (' + el.naturalWidth + 'x' + el.naturalHeight + ')';	     
	 }

	</script>
    </body>
</html>
