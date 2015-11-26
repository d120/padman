<?php
ini_set("display_errors","on");
include "../init.php";

if (isset($_SERVER["REDIRECT_STATUS"]) && $_SERVER["REDIRECT_STATUS"] == "404") {
  $url = $_SERVER["REDIRECT_SCRIPT_URL"];
}
if (isset($_GET["lnk"])) $url = $_GET["lnk"];

if (preg_match('#/([a-z0-9-]+)$#', $url, $res)) {
  $pads = sql("SELECT * FROM padman_pad_cache WHERE shortlink = ?", array($res[1]));
  if (count($pads) == 1) {
    $pad = $pads[0];
    //header("Location: ".PAD_URL. $k);
    header("HTTP/1.1 200 So fluffy");
    echo '<!doctype html><html><head><meta charset="utf8">';
    echo "<title>$pad[pad_name] - etherpad</title>\n";
    echo "<style>   html,body {margin:0;padding:0;height:100%;overflow:hidden;}
       iframe { width: 100%; height: 100%; border: 0; }   </style>
       </head><body>\n";
    echo '<div style="padding: 5px; position: absolute; bottom: 0; left: 10px; background: #bbb;"><a href="/pad/?group='.htmlentities(urlencode($pad['group_mapper'])).'&show='.htmlentities(urlencode($pad['pad_name'])).'">Login</a></div>';
    echo '<iframe src="'.htmlentities(PAD_URL. $pad['group_id'].'$'.$pad['pad_name']).'"></iframe></body></html>';
    exit;
  }
}


?>
<!doctype html>
<html>
<head>
<meta charset="utf8">
<meta name='viewport' content='width=device-width, initial-scale=1'>
<style>body { max-width: 600px; margin: 10px auto; }</style>

</head>
<body>
<h3>Pad nicht gefunden</h3>

<p>Pr&uuml;fe bitte nochmals den Link, vielleicht hast du dich ja vertippt... </p>
<p>Ansonsten kann es auch sein, dass das Pad nicht mehr &ouml;ffentlich zug&auml;nglich ist, oder dass es gel&ouml;scht wurde.</p>

</body>
</html>

