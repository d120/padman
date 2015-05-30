
function show_pad_info(e) {
	var box = document.getElementById("padview_info");
	if(box.style.height=="19px") {
		box.style.height="inherit";
		document.getElementById("padview_x").innerHTML = "X";
	} else {
		box.style.height="19px";
		document.getElementById("padview_x").innerHTML = "+";
	}
}

function export_popup(url) {
	window.open(url, '', 'width=800,height=600,scrollbars=yes');
	return false;
}

document.addEventListener("DOMContentLoaded", function() {
	document.getElementById("padview_x").addEventListener("click", show_pad_info, false);
}, false);
