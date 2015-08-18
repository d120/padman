
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

