<?php
if(!$instance) exit;
  header("Content-Type: text/plain; charset=utf-8");
  $padname = $_GET['pad_id'];
  $result = $instance->getHTML($padname);
  $txt = $result->html;
  $txt = str_replace(array("<br>", "</li>", "<em>", "</em>", "&nbsp;", "<ul"  , "&#x2F;", "&lt;bteil&gt;", "&lt;/bteil&gt;"  ),
		     array("\n"  , "\n"   , "''"  , "''"   , " "     , "<ul", "/",      "<bteil>",       "</bteil>"        ),
		     $txt);
  $txt = preg_replace('#<!DOCTYPE HTML><html><body>|</body></html>|<a href="[^"]+">|</a>|<strong>|</strong>|</a>#', '', $txt);
  $l = explode("\n", $txt); $ul = 0;
  $iscomment = false;
  foreach($l as $d) {
    if (trim($d)=="&lt;!--") $iscomment = true;
    if ($iscomment) {
      if (trim($d)=="--&gt;") $iscomment = false;
      continue;
    }
    $ul += substr_count($d, "<ul");
    $ul -= substr_count($d, "</ul>");
    $lenbefore = count($d);
    $d = str_replace(array("<ul class=\"bullet\">","<ul>", "<ul class=\"indent\">", "</ul>", "<li>"), array("","","","",str_repeat("*",$ul)), $d);
    if ($lenbefore > 0 && count($d) == 0) continue;
    echo "$d\n";
  }
  //echo '<textarea rows=20 cols=100>'.htmlspecialchars($txt).'</textarea>';

