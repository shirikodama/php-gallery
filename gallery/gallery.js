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
    curfile = cur;
    
    if (ent) {
	title = ent.desc || cur;
	hidden = '<div id="hidden-'+cur+'" class=fsi16>';
	hidden += (ent.hidden ? '[hidden from public]' : '');
	hidden += '</div>';
    } else {
	console.log ("no ent ", cur);
	title = cur;
	hidden = '';
	ent = {desc: title, hidden: false};
	jconfig.data[cur] = ent;
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
    html += '<div id="display_cur_head">';
    html += '<table width=100% align=center>';
    html += '<tr>';
    html += '<td class="panetop" valign=top><a onclick="display_close ()"> <img width=32 src=erase.png></a></td>';
    html += '<td width=160>';
    if (prev) {
	html += '<a onclick="display_cur(\''+jsesc(prev)+'\')"><img width=32 src=arrow_left_green.png><br>' +abbrev(prev,12, 400)+'</a>';
    }
    html += '</td>';
    title = abbrev (title, 20, 500);
    html += '<td align=center class="fs40 redtext"><span onclick="editTitle(this, \''+curdir+'\', \''+cur+'\')">' + title +'</span><div class=fs20>('+cur+')</div>'+hidden;
    var img = jconfig.data[cur].hidden ? "open-eye.png" : "hide.png";
    html += '<td width=160 align=right>';
    if (next) {
	html += '<a onclick="display_cur(\''+jsesc(next)+'\')"><img width=32 src=arrow_right_green.png><br>' +abbrev(next, 12, 400)+'</a>';
    }  
    html += '</td>';
    html += '</td><td align=right>';
    if (is_local)
	html += '<span><img width=20 src='+img+' onclick="toggleHide(\''+curdir+'\', this, \''+cur+'\')" class=hbutton></span>';
    html += '</td></tr></table></div>';
    // start the main body of the display image
    html += '<div id="display_cur_grid" style="overflow: auto; height:100%; width:100%" onresize="setgridheight(\'display_cur_head\', \'display_cur_grid\')">';
    html += '<table align=center>';
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
    html += '<table id="cur_footer" align=center  width=90%><tr>';
    html += '<tr><th>Gallery</th><th>Size</th><th>Camera</th><th>Date</th></tr><tr>';
    html += '<td width=10% align=center class=fs20>'+curdir+'</td><td width=10% align=center>';
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
    // end of footer
    html += '</td></tr></table>';
    html += '</td></tr>';
    html += '</table></div>';
    el.innerHTML = html;
    setgridheight ('display_cur_head', 'display_cur_grid');
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
    var isEscape = false, isRArrow = false, isLArrow = false;
    if ("key" in evt) {
	isEscape = (evt.key === "Escape" || evt.key === "Esc");
	isRArrow = evt.key === 'ArrowRight';
	isLArrow = evt.key === 'ArrowLeft';
    } else {
	isEscape = (evt.keyCode === 27);
	isRArrow = evt.keyCode == 39;
	isLArrow = evt.keyCode == 38;
    }
    if (isEscape) {
	var el = document.getElementById ('display_div');
	if (el.style.display == 'block') {
	    display_close ();
	} else {
	    // not sure why this settmo is needed, but weirdness requires...
	    setTimeout (function () {
		window.location.href = 'index.php';
	    }, 10);
	}
    }
    if (isRArrow || isLArrow) {
	var el = document.getElementById ('display_div');
	if (el.style.display == 'block') {
	    var pn = get_prevnext  (curfile, curfiles);
	    if (isLArrow && pn.prev)
		display_cur (pn.prev);
	    else if (isRArrow && pn.next)
		display_cur (pn.next);
	} else {
	    if (isLArrow && gallery_pn.prev)
		window.location.href = "gallery.php?d="+gallery_pn.prev;
	    else if (isRArrow && gallery_pn.next)
		window.location.href = "gallery.php?d="+gallery_pn.next;
	}
    }
};

function imgload (el) {
    // bordersize is actually the css border-radius of display_div * 2 (16 for each border)
    var bordersize = 16*2;
    var dispel = document.getElementById ('display_div');
    var headel = document.getElementById ('display_cur_head');
    var footel = document.getElementById ('cur_footer');
    var imgel = document.getElementById ('cur_img');
    var maxw = (window.innerWidth-bordersize);
    // the max height the pic can be is screen heigyh - the header - footer - borders
    var maxh = (window.innerHeight-(headel.offsetHeight+footel.offsetHeight+bordersize));
    var imgratio = el.naturalHeight / el.naturalWidth;
    var w = maxw-20;    // the -20 gives about 10px of padding
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
