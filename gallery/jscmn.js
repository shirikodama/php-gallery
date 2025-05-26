function set_config (gallery, file, key, val) {
    if (gallery)
	jconfig.data[file][key] = val;
    else
	gjconfig.data[file][key] = val;	
}
function updateTitle (gallery, file, newtitle, el) {
    var url = "update_title.php";
    var req = new XMLHttpRequest ();
    const formData = new FormData();
    formData.append('dir', gallery);
    formData.append('file', file);
    formData.append('title', newtitle);
    
    req.onreadystatechange = function () {
	if (req.readyState == 4) {
	    if (req.status == 200) {
		var src = req.responseText;
		var resp = JSON.parse(src);
		if (parseInt (resp.sts) == 200) {
		    set_config (gallery, file, 'desc', newtitle);
		    if (el)
			el.otitle = newtitle;
		} else {
		    alert ("can't update title: " + resp.sts);
		    console.log ("update jsonerr", resp.sts);
		}
		if (el)
		    el.innerHTML = el.otitle;
		// update the grid if needed
		var e = document.getElementById('gallery-title-' + file);
		if (e)
		    e.innerHTML = el.otitle;
	    } else
		console.log ("update error: ", resp.status);
	}
    };
    req.open ("POST", url), true;
    req.send (formData);
}

function toggleHide (gallery, el, file) {
    var config = gallery == '' ? gjconfig : jconfig;
    var ent = config.data[file];
    if (confirm ((ent.hidden ? "Allow public view " : "Hide from public ")+file+"?")) {
	updateHidden (gallery, file, ! ent.hidden, el);
    }
}
	 


function updateHidden (gallery, file, newhidden, el) {
    var url = "update_hidden.php";
    var req = new XMLHttpRequest ();
    const formData = new FormData();
    formData.append('dir', gallery);
    formData.append('file', file);
    formData.append('hidden', newhidden ? "true" : "false");
    
    req.onreadystatechange = function () {
	if (req.readyState == 4) {
	    if (req.status == 200) {
		var src = req.responseText;
		var resp = JSON.parse(src);
		if (parseInt (resp.sts) == 200) {
		    set_config (gallery, file, 'hidden', newhidden);
		    if (el) {
			el.src = newhidden ? "open-eye.png" : "hide.png";
			if (gallery) {
			    var htext = (newhidden ? "[hidden from public]" : "");
			    var e = document.getElementById ('hidden-' + file);
			    e.innerHTML = htext;
			    var htext = (newhidden ? "[hidden from public]" : "&nbsp;");
			    // update the grid if needed
			    var e = document.getElementById ('gallery-hidden-' + file);
			    e.innerHTML = htext;
			} else {
			    var htext = (newhidden ? "[hidden from public]" : "&nbsp;");
			    var e = document.getElementById ('gallery-hidden');
			    e.innerHTML = htext;
			}
		    }
		} else {
		    alert ("can't update hidden: " + resp.sts);
		    console.log ("update jsonerr", resp.sts);
		}
	    } else
		console.log ("update hidden error: ", resp.status);
	}
    };
    req.open ("POST", url), true;
    req.send (formData);
}

function editTitleKey(el, dir, file) {
    var evt = window.event;
    var isReturn = false;
    var isEscape = false;
    evt.stopPropagation ();
    if ("key" in evt) {
	isEscape = (evt.key === "Escape" || evt.key === "Esc");
	isReturn = (evt.key === "Enter");
    } else {
	isEscape = (evt.keyCode === 27);
	isReturn = (evt.keyCode === 13);
    }
    if (isEscape) {
	editTitleClose (el);
    } else if (isReturn) {
	var parent = el.parentNode;
	parent.editing = false;
	if (parent.otitle != el.value) {
	    updateTitle (dir, file, el.value, parent);
	} else {
	    parent.innerHTML = parent.otitle;
	}
    }
    return false;
}

function editTitleClose(el) {
    var evt = window.event;
    evt.stopPropagation ();
    var parent = el.parentNode;
    parent.editing = false;
    parent.innerHTML = parent.otitle;	     
}

function jsesc(val) {
    return val.replaceAll ("'", "\\'"); //.replace ('"', '\\"');
}

function htmlquote (val) {
    return val.replaceAll ('"', '&quot;');
}


function editTitle(el, dir, file) {
    // we have a really crude access control for writing which is that it's on the local subnet, hardwired to be 192.168.1.*
    if (! is_local || el.editing)
	return;
    el.editing = true;
    var otitle = el.innerHTML;
    el.otitle = otitle;
    var html = '<input onkeydown="editTitleKey(this, \''+dir+'\', \''+file+'\')" size="40" type=input value="'+htmlquote (otitle)+'"> <img width=20 src="erase.png" onclick="editTitleClose(this)"><br><span class=fsi20 style="color:blue;">(enter to update, escape to close)</span>';
    el.innerHTML = html;
}

function thumbload (el) {
    if (el.naturalHeight > el.naturalWidth) {
	el.classList.add ('heightcrop');
    } else
	el.classList.add ('widthcrop');      
}

function abbrev (str, charlen, maxlen) {
    if ((str.length * charlen) > maxlen) {
	var maxchars = (maxlen - 3*charlen) / charlen;
	var rv =  str.substring (0, maxchars) + '...';
	return rv;
    }
    return str;
}
