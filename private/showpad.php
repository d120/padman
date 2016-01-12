<?php
if(!$instance) exit;

  $padname = htmlspecialchars($_GET['show']);
  $pad = sql("SELECT * FROM padman_pad_cache WHERE group_mapper=? AND pad_name=?", array($group, $padname))[0];
  if (!$pad) {
    header("HTTP/1.1 404 Not Found");
    load_view("pad_not_found", array("pad" => "$group/$padname"));
    return;
  }
  //header("Location: ".$padurl.$padname); #+$padurl+$padname);
  $padID = $pad['group_id'].'$'.$pad['pad_name'];
  
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
    $result = $instance->getText($padID);
    $fn = DATA_DIR."/index/".urlencode($group)."/".urlencode($padname).".txt";
    @mkdir(DATA_DIR."/index"); @mkdir(dirname($fn));
    file_put_contents($fn, $result->text);
  } catch(Exception $ex) {
    header("HTTP/1.1 500 Internal Server Error");
    load_view("error_layout", array("content" => "Das Pad $group/$padname ist zur Zeit nicht verfügbar, da es ein Problem mit Etherpad Lite gibt.<br><br>Letzter Inhalt:"."<pre>".htmlspecialchars(file_get_contents(DATA_DIR."/index/$group/$padname.txt"))."</pre>"));
    return;
  }

  pad_session_check();

  if ($pad['access_level'] == 1) {
    $icon_html = '<span class="glyphicon glyphicon-globe"></span> '; $public="true"; $tags="<span class='label label-success'>öffentlich</span>";
  } else{
    $icon_html = '<span class="glyphicon glyphicon-home"></span> '; $public="false"; $tags="";
  }

  echo "<meta charset='utf8'><title>$padname - $group - d120.de/pad</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <style>
    @import url(css/bootstrap.min.css); 
    @import url(css/pads.css);
    html, body { margin: 0; padding: 0; }
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

  echo "<div class='title elipsis'><a href='?group=$group'>$group</a> &#187; $padname $tags  </div>
    <div class='elipsis'>".$padurl.$padID."</div> </div></div><div class='content col-sm-3'>$passw
    </div><div class='content col-sm-4'>$shortlnk</div></div>";
  
  echo "</div>";
  echo '<iframe id="padview_iframe" src="'.PAD_URL.$padID.'"></iframe>';
  load_view("modal_options", array());
  load_view("modal_export", array("padID"=>$padID, "shortlnk" => $shortlnk, "shortnam" => $pad['shortlink'], "password" => $pad['password']));
  echo '<script> var pm = new PadManager("' . SELF_URL . '", "' . $group . '"); </script>';
  
