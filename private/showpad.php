<?php
if(!$instance) exit;

  $padname = htmlspecialchars($_GET['show']);
  //header("Location: ".$padurl.$padname); #+$padurl+$padname);
  $padID = $groupmap[$group].'$'.$padname;
  $password = readJson('passwords', $groupmap[$group].'$'.$padname);
  $passw = "";
  if ($password) $passw = "Passwort: <input type='text' value='$password' readonly ondblclick='event.stopPropagation();return false' onclick='this.select()' id='padview_pw'>";
  $shortnam = readJson('shortlnk', $groupmap[$group].'$'.$padname);
  $shortlnk = "";
  if ($shortnam) $shortlnk = "Kurz-Link: <br><b><a href='".SHORTLNK_PREFIX."$shortnam' class='elipsis'>".SHORTLNK_PREFIX."$shortnam</a></b>";
  try {
    $public = $instance->getPublicStatus($padID); $tags = "";
  } catch(InvalidArgumentException $ex) {
    load_view("pad_not_found", array("pad" => "$group/$padname"));
  }
    if ($public->publicStatus) {
      $icon_html = '<span class="glyphicon glyphicon-globe"></span> '; $public="true"; $tags="<span class='label label-success'>öffentlich</span>";
    } else{
      $icon_html = '<span class="glyphicon glyphicon-home"></span> '; $public="false";
    }

  echo "<meta charset='utf8'><title>$padname - $group - d120.de/pad</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <style>
    @import url(css/bootstrap.min.css); 
    @import url(css/pads.css);
    html, body { margin: 0; padding: 0; }
    </style>
    <script src='js/jquery.js'></script>
    <script src='js/bootstrap.js'></script>
    <script src='js/padmanager.js'></script>
    <script src='js/pad_iframe.js'></script>
    <div id='padview_info' data-padID='$padID' data-public='$public' data-shortlnk='$shortnam' data-passw='$password'>
    <div class='row'><div class='col-sm-5 noselect '>
    <a href='#' id='padview_x' class='imgbutton x' title='Toggle Menu Bar'><span class='glyphicon glyphicon-chevron-down'></span></a>
    <div class='content'>
    <a href='".SELF_URL."' class='imgbutton' title='Go to Pad Index'><span class='glyphicon glyphicon-home'></span></a>
    <a href='#' class='imgbutton pad_opts' title='Pad Properties'><span class='glyphicon glyphicon-cog'></span></a>";
  if ($shortlnk)
    echo "  <a href='mailto:?subject=Etherpad&body=Hallo,%0a%0ahier der Link zum Pad:%0a".SHORTLNK_PREFIX."$shortnam%0aPasswort:%20$password%0a%0aViele%20Grüße,%0a%0a' title='Share' class='imgbutton'><span class='glyphicon glyphicon-share'></span></a>";
  echo "<a class='imgbutton last' href='?pad_id=$padID&export=wiki' onclick='return export_popup(this.href);' title='Wiki Export'><span class='glyphicon glyphicon-export'></span></a>";

  echo "<div class='title elipsis'><a href='?group=$group'>$group</a> &#187; $padname $tags  </div>
    <div class='elipsis'>".$padurl.$groupmap[$group].'$'.$padname."</div> </div></div><div class='content col-sm-3'>$passw
    </div><div class='content col-sm-4'>$shortlnk</div></div>";
  
  echo "</div>";
  echo '<iframe id="padview_iframe" src="'.$padurl.$padID.'"></iframe>';
  include "template-modal-options.html";
  echo '<script> var pm = new PadManager("' . SELF_URL . '", "' . $group . '"); </script>';

