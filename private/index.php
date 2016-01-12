<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL & ~E_NOTICE);
header('Content-Type: text/html; charset=utf-8');

include "../init.php";

$group_titles = $GROUP_TITLES;
$group_keys = array();
foreach($group_titles as $d) if (is_array($d)) $group_keys = array_merge($group_keys, $d); else $group_keys [] = $d;
$group_keys = array_unique(array_map("strtolower", $group_keys));

$infoBox = "";

if ($_SERVER["HTTP_HOST"] != HOST_NAME) {
  header("Location: https://".HOST_NAME.$_SERVER["REQUEST_URI"]);
  exit;
}

if (isset($_SERVER["REDIRECT_STATUS"]) && $_SERVER["REDIRECT_STATUS"] == "404") {
  $url = $_SERVER["REDIRECT_SCRIPT_URL"] ?: $_SERVER["REDIRECT_URL"];
  if (preg_match('#^/pad/(.*)$#', $url, $res) && array_search($res[1], $group_keys) !== FALSE) {
    header("HTTP/1.1 200 OK");
    $_GET["group"] = $res[1];
  } elseif (preg_match('#^/pad/p/(Sitzung.*)$#', $url, $res)) {
    $padID = $res[1];
    header("Location: ".SELF_URL."?group=sitzung&show=$padID");
  } elseif (preg_match('#^/pad/p/(.*)$#', $url, $res)) {
    $padID = $res[1];
    header("Location: ".SELF_URL."?group=fachschaft&show=$padID");
    exit;
  } elseif (preg_match('#^/pad/(.*)/(.*)$#', $url, $res) && array_search($res[1], $group_keys) !== FALSE) {
    header("HTTP/1.1 200 OK");
    $_GET["group"] = $res[1];
    $_GET["show"] = $res[2];
  } else {
    header("HTTP/1.1 404 Not Found");
    load_view("group_not_found", array("group" => $group));
    exit;
  }
}

function setPassword($padID, $passwd) {
  global $instance, $db;
  $ok=$instance->setPassword($padID, $passwd);
  
  $padid=explode('$',$padID);
  $db->prepare("UPDATE padman_pad_cache SET password =? WHERE group_id=? AND pad_name=?")
     ->execute(array($passwd, $padid[0], $padid[1]));
  return $ok;
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

$author_groups = $group_keys;

$author_groups = array_intersect($author_groups, $group_keys);

if (isset($_GET['group'])) $group = $_GET['group'];
else $group = $author_groups[0];

if (!in_array($group, $author_groups)) {
    header("HTTP/1.1 404 Not found");
    load_view("group_not_found", array("group" => $group));
    return;
}

$groupmaplist = $db->query("select group_id,group_mapper,tags from padman_group_cache")->fetchAll();
$groupmap = array();
foreach($groupmaplist as $d)
    if (array_search($d['group_mapper'], $author_groups) !== false)
        $groupmap[$d['group_mapper']] = $d['group_id'];

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

if (isset($_GET['show'])) {
  require "showpad.php";
  exit;
}

if (isset($_GET['do']) && $_GET['do'] == 'export_mdhtml') {
  require "showmarkdown.php";
  exit;
}

// Export as wikitext for MediaWiki
if (isset($_GET['pad_id']) && isset($_GET['do']) && $_GET['do'] == 'export_wiki') {
  require "showmediawiki.php";
  exit;
}

if (isset($_COOKIE["infobox"])) {
  $infoBox = $_COOKIE["infobox"];
  setcookie("infobox", null);
}

pad_session_check();

load_view("layout", array(
  "group_titles" => $group_titles, "groups" => $groupmaplist, "current_group" => $group, "allow_pad_create" => $allow_pad_create,
  "login" => array("cn" => $author_cn, "name" => $author_name)
));

?>
