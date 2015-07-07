<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL );
header('Content-Type: text/html; charset=utf-8');

define('VIEW_DIR', dirname(__FILE__).'/../views');
include "../config.inc.php";
include "../jsondb.inc.php";
include "../views.inc.php";
include "etherpad-lite-client.php";
$shown_groups = array_map("strtolower", $shown_groups_titles);
$infoBox = "";

if ($_SERVER["HTTP_HOST"] != HOST_NAME) {
  header("Location: https://".HOST_NAME.$_SERVER["REQUEST_URI"]);
  exit;
}

if (isset($_SERVER["REDIRECT_STATUS"]) && $_SERVER["REDIRECT_STATUS"] == "404") {
  $url = $_SERVER["REDIRECT_SCRIPT_URL"];
  if (preg_match('#^/pad/(.*)$#', $url, $res) && array_search($res[1], $shown_groups) !== FALSE) {
    header("HTTP/1.1 200 OK");
    $_GET["group"] = $res[1];
  } elseif (preg_match('#^/pad/p/(Sitzung.*)$#', $url, $res)) {
    $padID = $res[1];
    header("Location: ".SELF_URL."?group=sitzung&show=$padID");
  } elseif (preg_match('#^/pad/p/(.*)$#', $url, $res)) {
    $padID = $res[1];
    header("Location: ".SELF_URL."?group=fachschaft&show=$padID");
    exit;
  } elseif (preg_match('#^/pad/(.*)/(.*)$#', $url, $res) && array_search($res[1], $shown_groups) !== FALSE) {
    header("HTTP/1.1 200 OK");
    $_GET["group"] = $res[1];
    $_GET["show"] = $res[2];
  } else {
    header("HTTP/1.1 404 Not Found");
    echo "<h3>File not found</h3>";
    exit;
  }
}

function setPassword($padID, $passwd) {
  global $instance;
  $ok=$instance->setPassword($padID, $passwd);
  (new JsonDB('passwords'))->store($padID, $passwd);
  return $ok;
}

$padurl = PAD_URL;

$instance = new EtherpadLiteClient(API_KEY, API_URL);

if (isset($_SERVER['PHP_AUTH_USER']) || ALLOW_ANON_PAD_CREATE) $allow_pad_create = true;

$author_cn = (isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : 'anonymous_'.substr(md5($_SERVER["REMOTE_ADDR"]),0,10));
if (file_exists("/home/" . $author_cn . "/.padname")) {
  $author_name = file_get_contents("/home/" . $author_cn . "/.padname");
} else {
  $author_name = $author_cn;
}
//$author_cn = $_SERVER['HTTP_AUTH_CN'];
//$groups = "sitzung; fachschaft; inforz; ophase"; //base64_decode($_SERVER['HTTP_AUTH_GROUPS']);
//$author_groups = preg_split("/[\s;]+/", $groups);
$author_groups = $shown_groups;

$author_groups = array_intersect($author_groups, $shown_groups);

if (isset($_GET['group'])) $group = $_GET['group'];
else $group = $author_groups[0];

if (!in_array($group, $author_groups)) {
    header("HTTP/1.1 404 Not found");
    load_view("group_not_found", array("group" => $group));
    return;
}

try {
    $author = $instance->createAuthorIfNotExistsFor($author_cn, $author_name);
    $authorID = $author->authorID;
} catch(Exception $ex) {echo $ex;
    die("<h2>Eile mit Weile - Etherpad ist zur Zeit nicht erreichbar</h2>");
}

$validUntil = mktime(0, 0, 0, date("m"), date("d")+1, date("y")); // One day in the future

$groupmap = array();
$sessions = array();

foreach ($author_groups as $group_name) {
  $mapGroup = $instance->createGroupIfNotExistsFor($group_name);
  $groupmap[$group_name] = $mapGroup->groupID;
  $sessions[] = $instance->createSession($mapGroup->groupID, $authorID, $validUntil)->sessionID;
}


setcookie('sessionID', implode(",",$sessions), $validUntil, '/', HOST_NAME);

if (isset($_GET['q'])) {
  require "showsearch.php";
  exit;
}

if (isset($_GET['show'])) {
  require "showpad.php";
  exit;
}

if (isset($_GET['export']) && $_GET['export'] == 'mdhtml') {
  require "showmarkdown.php";
  exit;
}

// Export as wikitext for MediaWiki
if (isset($_GET['pad_id']) && isset($_GET['export'])) {
  require "showmediawiki.php";
  exit;
}

// JSON API
if (count($_POST)) {
  require "showapi.php";
  exit;

}

if (isset($_GET['list_pads'])) {
  $pads = $instance->listPads($groupmap[$group]);
  $pad_lastedited = Array();
  foreach ($pads->padIDs as $padID) {
    $tmp = $instance->getLastEdited($padID);
    $pad_lastedited[$padID] = (int)$tmp->lastEdited/1000;
  }

  asort($pad_lastedited);
  $pad_lastedited = array_reverse($pad_lastedited);
  echo '<div class="table-responsive"><table class="table table-hover">';
  echo '<thead><tr><th width=30></th><th>Name</th><th width=350>Passwort</th><th width=100></th></tr></thead><tbody>';
  foreach ($pad_lastedited as $padID => $last_edited) {
    $PAD = array("id" => $padID, "last_edited" => $last_edited,  "group" => $group);
    
    $tmp = $instance->getPublicStatus($padID);

    $PAD["shortname"] = substr($padID,strpos($padID, "$")+1);
    $PAD["icon_html"] = ""; $PAD["className"] = "";
    if ($tmp->publicStatus) {
      $PAD["icon_html"] = '<span class="glyphicon glyphicon-globe"></span> '; $PAD["public"]="true"; $PAD["className"]="";
    } else{
      $PAD["icon_html"] = '<span class="glyphicon glyphicon-home"></span> '; $PAD["public"]="false";
    }
    $PAD["passw"] = (new JsonDB('passwords'))->read($padID);
    $PAD["shortlnk"] = (new JsonDB('shortlnk'))->read($padID);
    if ($PAD["shortlnk"]) $PAD["shortlnk"] = SHORTLNK_PREFIX.$PAD["shortlnk"];
    
    load_view("pad_list_item", $PAD);
  }
  echo "</tbody></table></div>";
  if (count($pad_lastedited) == 0) echo "<div style='padding:100px 0;text-align:center;color:#aaa;'>- In dieser Kategorie gibt es noch keine Pads -</div>";
  die();
}

if (isset($_COOKIE["infobox"])) {
  $infoBox = $_COOKIE["infobox"];
  setcookie("infobox", null);
}

load_view("layout", array(
  "group_titles" => $shown_groups_titles, "groupmap" => $groupmap, "current_group" => $group, "allow_pad_create" => $allow_pad_create,
  "login" => array("cn" => $author_cn, "name" => $author_name)
));

?>
