<?php
ini_set("display_errors","on");
include "../init.php";

if (isset($_SERVER["REDIRECT_STATUS"]) && $_SERVER["REDIRECT_STATUS"] == "404") {
  $url = array_key_exists("REDIRECT_SCRIPT_URL", $_SERVER) ? $_SERVER["REDIRECT_SCRIPT_URL"] : $_SERVER["REDIRECT_URL"];
}
if (isset($_GET["lnk"])) $url = $_GET["lnk"];

function get_link($pad) {
  if ($pad['is_archived'])
    return 'index.php?lnk='.$pad['shortlink'].'&id='.$pad['id'];
  else
    return PAD_URL . ep_pad_id($pad);
}

if (preg_match('#/?([a-z0-9-]+)$#', $url, $res)) {
  $pads = sql("SELECT * FROM padman_pad_cache WHERE shortlink = ? AND access_level = 1 ORDER BY pad_name", array($res[1]));
  if (isset($_GET['id'])) $pads=array_values(array_filter($pads, function($x) { return $x['id'] == $_GET['id']; }));

  if (count($pads) >= 1) {
    //header("Location: ".PAD_URL. $k);
    header("HTTP/1.1 200 So fluffy");
    echo '<!doctype html><html><head><meta charset="utf8">';
    echo "<title>".htmlspecialchars($pads[0]["pad_name"])." - " . HEADER_TITLE ."</title>\n";
    echo "<style>   html,body {margin:0;padding:0;}
       iframe { width: 100%; height: 100%; border: 0; }
       #toolbar{padding: 5px; position: fixed; bottom: 0; left: 10px; background: #bbb;}
       .padlink { background: #fefe00; padding: 5px 10px; color: #000; font-weight: bold; }
       .padlink.active { background: #55bb55; color:white;  }
       .text { padding: 10px; }  body { font: 12pt 'Helvetica',sans-serif; } 
       .archived { padding: 10px; background: #fea; } </style>
       </head><body>\n";
    echo '<div id="toolbar"><a href="/pad/?group='.htmlentities(urlencode($pads[0]['group_alias'])).'&show='.htmlentities(urlencode($pads[0]['pad_name'])).'">Login</a>';
    if (count($pads) > 1) {
      foreach($pads as $padlink) {
        echo ' <a href="'.htmlentities(get_link($padlink)).'" target="i" class=padlink>'.$padlink['pad_name'].'</a>';
      }
    } else {
      if ($pads[0]['is_archived']) {
        echo '</div>';
        $group = sql("SELECT * FROM padman_group WHERE group_alias=?", array($pads[0]['group_alias']))[0];
      echo "<div class=archived><p>Das Pad <b>$group[group_alias]/".$pads[0]['pad_name']."</b> wurde automatisch archiviert, da es seit ".ARCHIVE_AFTER_MONTHS." Monaten nicht verwendet wurde.</p><p>Wenn Du es bearbeiten möchtest, wende dich bitte mit der Bitte um Wiederherstellung an fss <i>at</i> d120 <i>punkt</i> de und gib dabei einfach den Link in der Adresszeile an.</div> <div class=text>";

        if (!$pads[0]['password'] || (isset($_POST['password']) && $_POST['password'] == $pads[0]['password'])) {
          $fn = DATA_DIR."/archive/".urlencode($group["group_alias"])."/".urlencode($pads[0]['pad_name']).".html";
          $cont=file_get_contents($fn);
          $cont = str_replace("<a href=\"", "<a target=\"_blank\" href=\"", $cont);
          echo "\n\n".$cont;
        } else {
          if (isset($_POST["password"])) echo "Falsch!";
          echo "<h2>Passwort zum Betrachten eingeben:</h2><form action='index.php?lnk=".$pads[0]["shortlink"]."&id=".$pads[0]["id"]."' method='post'><input type='password' name='password'> <input type='submit' value='ok'></form>";
        }
        echo "</div><br><br>";
        exit;
      }
    }
    echo '</div>';
    echo '<style> html,body{height:100%;overflow:hidden;}</style>
      <iframe name=i src="'.htmlentities(get_link($pads[0])).'"></iframe>
       <script>
       document.getElementById("toolbar").onclick=function(e){
       try{document.querySelector(".padlink.active").className="padlink";}catch(x){}
       e.target.className="padlink active";
       };
       </script>
       </body></html>';
    exit;
  }
}

ob_start();
?>
<div class="msg alert alert-danger">
<h4>Pad nicht gefunden</h4>

<p>Pr&uuml;fe bitte nochmals den Link, vielleicht hast du dich ja vertippt... </p>
<p>Ansonsten kann es auch sein, dass das Pad nicht mehr &ouml;ffentlich zug&auml;nglich ist, oder dass es gel&ouml;scht wurde.</p>
</div>

<?php
load_view("error_layout", array("content" => ob_get_clean()));
?>

