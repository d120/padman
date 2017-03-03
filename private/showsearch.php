<?php
if (!ALLOW_SEARCH) { header("403 Forbidden"); return; }

$cmd = "cd ".escapeshellarg(DATA_DIR.'/index')."; grep -inR ".escapeshellarg($_GET["q"])." .";
$out = shell_exec($cmd);
$out = htmlspecialchars($out);
$out = preg_replace("/^\\.\\/([^\\/]+)\\/([^:]+)\\.txt:([0-9]+):/m", "<a href='?group=$1&show=$2'>./$1/$2.txt</a>:<font color=grey>$3</font>: ", $out);
$content = "<h3><a href='".SELF_URL."' class='btn btn-default'><span class='glyphicon glyphicon-arrow-left'></span> Zurück</a> Suchergebnisse für <b>".htmlentities($_GET["q"])."</b></h2><pre>".$out."</pre>";

load_view("error_layout", array(
  "login" => array("cn" => $author_cn, "name" => $author_name), "content" => $content
));

