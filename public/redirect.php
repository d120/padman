<?php
ini_set("display_errors","on");
include "../init.php";

if (isset($_SERVER["REDIRECT_STATUS"]) && $_SERVER["REDIRECT_STATUS"] == "404") {
  $url = $_SERVER["REDIRECT_SCRIPT_URL"];
}
if (isset($_GET["lnk"])) $url = $_GET["lnk"];

if (preg_match('#/?([a-z0-9-]+)$#', $url, $res)) {
  $pads = sql("SELECT * FROM padman_pad_cache WHERE shortlink = ? ORDER BY pad_name", array($res[1]));
  if (count($pads) >= 1) {
    $pad = $pads[0];
    //header("Location: ".PAD_URL. $k);
    header("HTTP/1.1 200 So fluffy");
    echo '<!doctype html><html><head><meta charset="utf8">';
    echo "<title>$pad[pad_name] - " . HEADER_TITLE ."</title>\n";
    echo "<style>   html,body {margin:0;padding:0;height:100%;overflow:hidden;}
       iframe { width: 100%; height: 100%; border: 0; }
       #toolbar{padding: 5px; position: absolute; bottom: 0; left: 10px; background: #bbb;}
       .padlink { background: #fefe00; padding: 5px 10px; color: #000; font-weight: bold; }
       .padlink.active { background: #55bb55; color:white;  }</style>
       </head><body>\n";
    echo '<div id="toolbar"><a href="/pad/?group='.htmlentities(urlencode($pad['group_alias'])).'&show='.htmlentities(urlencode($pad['pad_name'])).'">Login</a>';
    if (count($pads) > 1) {
      foreach($pads as $padlink) {
        echo ' <a href="'.htmlentities(PAD_URL. ep_pad_id($padlink)).'" target="i" class=padlink>'.$padlink['pad_name'].'</a>';
      }
    }
    echo '</div>';
    echo '<iframe name=i src="'.htmlentities(PAD_URL. ep_pad_id($pad)).'"></iframe>
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

