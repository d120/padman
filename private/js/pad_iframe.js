
var box, closebtn, iframe;

function on_resize() {
    iframe.style.height = window.innerHeight - (box.className == "collapsed" ? 0 : box.offsetHeight) + "px";
}

function show_pad_info(e) {
    if (box.className=="collapsed") {
	box.className = "";
	closebtn.innerHTML = '<span class="glyphicon glyphicon-chevron-down"></span>';
    } else {
	box.className = "collapsed";
	closebtn.innerHTML = '<span class="glyphicon glyphicon-chevron-up"></span>';
    }
    on_resize();
}

function export_popup(url) {
	window.open(url, '', 'width=800,height=600,scrollbars=yes');
	return false;
}
function loadScriptFile(u, c) {
	  var d = document, t = 'script',
		      o = d.createElement(t),
		      s = d.getElementsByTagName(t)[0];
	  o.src =  u;
	  if (c) { o.addEventListener('load', function (e) { c(null, e); }, false); }
	  s.parentNode.insertBefore(o, s);
}
var outTerm=null;
function call_shell_cmd(cmd) {
	$("#modal_export").modal("hide");
	$("#modal_output").modal("show");
	var xhr=null;
	var idx=0;
	var makeTerm=function() { console.log("maketerm called");
			$("#terminal").html("");
			outTerm = new Terminal();
			outTerm.open(document.getElementById('terminal'));
			//outTerm.write('Eile mit Weile ...\r\n')
			if (xhr) {
				outTerm.write(xhr.responseText.replace(/\n/g,"\r\n"));
				idx=xhr.responseText.length;
			}
	}
	if (typeof Terminal !== "undefined")
			makeTerm();
	else
			loadScriptFile("js/xterm.js", makeTerm);
	xhr=new XMLHttpRequest();
	xhr.open("POST","?api",true);
	xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xhr.send(cmd+"="+escape(padID));
	xhr.onreadystatechange=function(e){
		if(xhr.readyState==3 || xhr.readyState==4){
			var newtext=xhr.responseText.substring(idx);
			idx+=newtext.length;
			if (outTerm) outTerm.write(newtext.replace(/\n/g,"\r\n"));
			console.log(outTerm,newtext);
		}
	}
}

window.addEventListener("resize", on_resize, false);

document.addEventListener("DOMContentLoaded", function() {
    box = document.getElementById("padview_info");
    iframe = document.getElementById("padview_iframe");

    closebtn = document.getElementById("padview_x");
    closebtn.addEventListener("click", show_pad_info, false);

    on_resize();
    
    $(".pad_export").click(function() {
        $("#modal_export").modal("show");
    });
}, false);

