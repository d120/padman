<?php
if(!$instance) exit;

  $padname = htmlspecialchars($_GET['show']);
  //header("Location: ".$padurl.$padname); #+$padurl+$padname);
  $padID = $groupmap[$group].'$'.$padname;
  $passw = readJson('passwords', $groupmap[$group].'$'.$padname);
  if ($passw) $passw = "<br><b>Passwort: <input type='text' value='$passw' readonly ondblclick='event.stopPropagation();return false' onclick='this.select()' id='padview_pw'></b>";
  $shortlnk = readJson('shortlnk', $groupmap[$group].'$'.$padname);
  if ($shortlnk) $shortlnk = "<br><b>Kurz-Link: <a href='".SHORTLNK_PREFIX."$shortlnk'>".SHORTLNK_PREFIX."$shortlnk</a></b>";
  echo "<meta charset='utf8'><title>$padname - $group - d120.de/pad</title><style> 
    @import url(css/pads.css);
    html, body { margin: 0; padding: 0; }
    </style><script src='js/pad_iframe.js'></script>
    <div id='padview_info'><div class='noselect title'>
    <a href='#' id='padview_x' class='x'>X</a>
    Details zum Pad \"$group / $padname\"<br></div><div class='innerDiv'>
    Pad: ".$padurl.$groupmap[$group].'$'.$padname."<br><a href='?pad_id=$padID&export=wiki' onclick='return export_popup(this.href);'>Wiki Export</a>$passw$shortlnk</div></div>";
  echo '<iframe id="padview_iframe" src="'.$padurl.$padID.'"></iframe>';
  echo '';

