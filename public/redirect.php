<?php
include "../config.inc.php";

if (isset($_SERVER["REDIRECT_STATUS"]) && $_SERVER["REDIRECT_STATUS"] == "404") {
	$url = $_SERVER["REDIRECT_SCRIPT_URL"];
	if (preg_match('#^/pubpad/(.*)$#', $url, $res)) {
    
		
    $p = json_decode(file_get_contents("../data/shortlnk.json"), true);
    foreach($p as $k=>$v) {
      if ($v == $res[1]) {
        header("Location: ".PAD_URL. $k);
        exit;
      }
    }
    
  }
}

?><!doctype html>
<meta charset="utf8">
<style>body { max-width: 600px; margin: 10px auto; }</style>
<h3>Pad nicht gefunden</h3>

<p>Pr&uuml;fe bitte nochmals den Link, vielleicht hast du dich ja vertippt... </p>
<p>Ansonsten kann es auch sein, dass das Pad nicht mehr &ouml;ffentlich zug&auml;nglich ist, oder dass es gel&ouml;scht wurde.</p>

