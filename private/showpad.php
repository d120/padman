<?php
if(!$instance) exit;

  $padname = htmlspecialchars($_GET['show']);
  $pad = sql("SELECT * FROM padman_pad_cache WHERE group_alias=? AND pad_name=?", array($group["group_alias"], $padname))[0];
  if (!$pad) {
    header("HTTP/1.1 404 Not Found");
    load_view("pad_not_found", array("pad" => "$group[group_alias]/$padname"));
    return;
  }
  //header("Location: ".$padurl.$padname); #+$padurl+$padname);
  $padID = ep_pad_id($pad);

  $passw = "";
  if ($pad['password']) {
    $passw = "Passwort: <input type='text' value='$pad[password]' readonly ondblclick='event.stopPropagation();return false' onclick='this.select()' id='padview_pw'>";
    setcookie("password", $pad['password'], 0, PAD_URL);
  }

  $shortlnk = "";
  if ($pad['shortlink']) $shortlnk = "Kurz-Link: <br><b><a href='".SHORTLNK_PREFIX.$pad['shortlink']."' class='elipsis'>".SHORTLNK_PREFIX.$pad['shortlink']."</a></b>";

  //cache content
  //note: this does also serve as a check whether this pad does really exist in etherpad lite
  try {
    $htmlcode=dump_pad_to_file($padID, $padname, $group);
  } catch(Exception $ex) {
    header("HTTP/1.1 500 Internal Server Error");
    if ($pad["is_archived"]) {
      $errmsg = "Das Pad <b>$group[group_alias]/$padname</b> wurde automatisch archiviert, da es seit ".ARCHIVE_AFTER_MONTHS." Monaten nicht verwendet wurde.<br>Du kannst es jederzeit ohne seine History einfach wiederherstellen.<br><br>
      <form action='?' method='post'><input type='submit' value='Pad wiederherstellen' class='btn btn-success btn-large'><input type='hidden' name='restore_archived_pad' value='$pad[id]'></form><br>";
    } else {
      $errmsg = "Das Pad $group[group_alias]/$padname ist zur Zeit nicht verfügbar, da es ein Problem mit Etherpad Lite gibt.<br><br><small><i>Pad ID: $padID &bull; Error message: ".$ex->getMessage()."</i>";
    }
    $fn = DATA_DIR."/archive/".urlencode($group["group_alias"])."/".urlencode($padname).".html";
    if(is_file($fn)) $errmsg.="<b>Letzter Inhalt:</b><br><br><div class=well>".@file_get_contents($fn)."</div>";
    load_view("error_layout", array("content" => $errmsg));
    echo "<!-- ".$ex."-->";
    return;
  }

  pad_session_check();

  if ($pad['access_level'] == 1) {
    $icon_html = '<span class="glyphicon glyphicon-globe"></span> '; $public="true"; $tags="<span class='label label-success'>öffentlich</span>";
    $shortlink_pads = sql("SELECT * FROM padman_pad_cache WHERE shortlink = ? ORDER BY pad_name", array($pad['shortlink']));
  } else{
    $icon_html = '<span class="glyphicon glyphicon-home"></span> '; $public="false"; $tags="";
  }

    echo '</div>';
echo "<meta charset='utf8'><title>$padname - $group[menu_title] - " . HEADER_TITLE . "</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <style>
    @import url(css/bootstrap.min.css); 
    @import url(css/pads.css);
    html, body { margin: 0; padding: 0; }
    .padlinks { display: table; width: 100%; }
    .padlink { display: table-cell; padding: 5px; font-weight: bold; color: #222; background: #fefe00; border-right: 6px solid #fff; }
    .padlink.active { background: #333; color: #eee; }
    </style>
    <script src='js/jquery.js'></script>
    <script src='js/bootstrap.min.js'></script>
    <script src='js/padmanager.js'></script>
    <script src='js/pad_iframe.js'></script>
    <div id='padview_info' data-padID='$padID' data-public='$public' data-shortlnk='$pad[shortlink]' data-passw='$pad[password]'>
    <div class='row'><div class='col-sm-5 noselect '>
    <a href='#' id='padview_x' class='imgbutton x' title='Toggle Menu Bar'><span class='glyphicon glyphicon-chevron-down'></span></a>
    <div class='content'>
    <a href='".SELF_URL."' class='imgbutton' title='Go to Pad Index'><span class='glyphicon glyphicon-home'></span></a>
    <a href='#' class='imgbutton pad_opts' title='Pad Properties'><span class='glyphicon glyphicon-cog'></span></a>";
  echo "<a class='imgbutton last pad_export' href='#' title='Export'><span class='glyphicon glyphicon-export'></span></a>";

  echo "<div class='title elipsis'><a href='?group=$group[group_alias]'>$group[menu_title]</a> &#187; $padname $tags  </div>
    <div class='elipsis'>".$padurl.$padID."</div> </div></div><div class='content col-sm-3'>$passw
    </div><div class='content col-sm-4'>$shortlnk</div></div>";

  if (count($shortlink_pads) > 1) {
    echo '<div class="padlinks">';
    foreach($shortlink_pads as $padlink) {
      echo ' <a href="?group='.$padlink['group_alias'].'&show='.htmlentities($padlink['pad_name']).'" class="padlink'.($padlink['id']==$pad['id']?' active':'').'">'.$padlink['pad_name'].'</a>';
    }
    echo "</div>";
  }

  echo "</div>";
  echo '<iframe id="padview_iframe" src="'.PAD_URL.$padID.'"></iframe>';
  load_view("modal_options", array());
  load_view("modal_export", array("padID"=>$padID, "shortlnk" => $shortlnk, "shortnam" => $pad['shortlink'], "password" => $pad['password']));
  echo '<script> var pm = new PadManager("' . SELF_URL . '", "' . $group["group_alias"] . '"); </script>';
  
