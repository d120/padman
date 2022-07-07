<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL & ~E_NOTICE);
header('Content-Type: text/html; charset=utf-8');

include "../init.php";

$infoBox = "";

if ($_SERVER["HTTP_HOST"] != HOST_NAME) {
  header("Location: https://".HOST_NAME.$_SERVER["REQUEST_URI"]);
  exit;
}

if (isset($_SERVER["REDIRECT_STATUS"]) && $_SERVER["REDIRECT_STATUS"] == "404") {
  $url = $_SERVER["REDIRECT_SCRIPT_URL"] ?: $_SERVER["REDIRECT_URL"];
  if (preg_match('#^'.SELF_URL.'(.*)$#', $url, $res)) {
    header("HTTP/1.1 200 OK");
    $_GET["group"] = $res[1];
  } elseif (preg_match('#^/pad/p/(Sitzung.*)$#', $url, $res)) {
    $padID = $res[1];
    header("Location: ".SELF_URL."?group=Sitzung&show=$padID");
  } elseif (preg_match('#^/pad/p/(.*)$#', $url, $res)) {
    $padID = $res[1];
    header("Location: ".SELF_URL."?group=Fachschaft&show=$padID");
    exit;
  } elseif (preg_match('#^/pad/(.*)/(.*)$#', $url, $res)) {
    header("HTTP/1.1 200 OK");
    $_GET["group"] = $res[1];
    $_GET["show"] = $res[2];
  } else {
    header("HTTP/1.1 404 Not Found");
    load_view("group_not_found", array("group" => $url));
    exit;
  }
}

$padurl = PAD_URL;

$instance = new EtherpadLiteClient(API_KEY, API_URL);

$allow_pad_create = false;
if (isset($_SERVER['PHP_AUTH_USER']) || ALLOW_ANON_PAD_CREATE) $allow_pad_create = true;

$author_cn = (isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : 'ip_'.preg_replace('/[^0-9a-f]+/', '_', $_SERVER["REMOTE_ADDR"]));
$userinfo = sql("SELECT * FROM padman_user WHERE user = ?", [ $author_cn ]);
if (count($userinfo) == 1) {
  $userinfo = $userinfo[0];
  $author_name = $userinfo["alias"];
} else {
  $userinfo = null;
  $author_name = $author_cn;
}

foreach($group_aliases as $d)
  if (!isset($_GET['group']) || $d["group_alias"] == $_GET["group"]) {
    $group = $d; break;
  }

if (!in_array($group["group_mapper"], $author_groups)) {
    header("HTTP/1.1 404 Not found");
    load_view("group_not_found", array("group" => $_GET["group"]));
    return;
}

// JSON API
if (count($_POST) || isset($_GET["api"])) {
  require "showapi.php";
  exit;
}

if (isset($_GET['q'])) {
  require "showsearch.php";
  exit;
}

if (isset($_GET['do']) && $_GET['do'] == 'user_config') {
  require "showuser.php";
  exit;
}

if (isset($_GET['do']) && $_GET['do'] == 'mass_editor') {
  require "showmasseditor.php";
  exit;
}

if (isset($_GET['show'])) {
  require "showpad.php";
  exit;
}

if (isset($_GET['do']) && $_GET['do'] == 'export_mdhtml') {
  require "showmarkdown.php";
  exit;
}

if (isset($_COOKIE["infobox"])) {
  $infoBox = $_COOKIE["infobox"];
  setcookie("infobox", null);
}

pad_session_check();

load_view("layout", array(
  "groups" => $group_aliases, "current_group" => $group, "allow_pad_create" => $allow_pad_create,
  "login" => array("cn" => $author_cn, "name" => $author_name), "infoBox" => $infoBox
));

?>
