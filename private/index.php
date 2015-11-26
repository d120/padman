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
  $url = $_SERVER["REDIRECT_SCRIPT_URL"];
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
    echo "<h3>File not found</h3>";
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
if (file_exists("/home/" . $author_cn . "/.padname")) {
  $author_name = file_get_contents("/home/" . $author_cn . "/.padname");
} else {
  $author_name = $author_cn;
}
//$author_cn = $_SERVER['HTTP_AUTH_CN'];
//$groups = "sitzung; fachschaft; inforz; ophase"; //base64_decode($_SERVER['HTTP_AUTH_GROUPS']);
//$author_groups = preg_split("/[\s;]+/", $groups);
$author_groups = $group_keys;

$author_groups = array_intersect($author_groups, $group_keys);

if (isset($_GET['group'])) $group = $_GET['group'];
else $group = $author_groups[0];

if (!in_array($group, $author_groups)) {
    header("HTTP/1.1 404 Not found");
    load_view("group_not_found", array("group" => $group));
    return;
}

$groupmaplist = $db->query("select group_id,group_mapper from padman_group_cache");
$groupmap = array();
foreach($groupmaplist as $d)
    if (array_search($d['group_mapper'], $author_groups) !== false)
        $groupmap[$d['group_mapper']] = $d['group_id'];

// if sessionID is older than one hour,  ...
if (!isset($_COOKIE['sessionIDExpiration']) || $_COOKIE['sessionIDExpiration'] < time() - 3600) {
    try {
        $author = $instance->createAuthorIfNotExistsFor($author_cn, $author_name);
        $authorID = $author->authorID;
    } catch(Exception $ex) {echo $ex;
        die("<h2>Eile mit Weile - Etherpad ist zur Zeit nicht erreichbar</h2>");
    }

    $validUntil = mktime(23, 0, 0, date("m"), date("d")+1, date("y")); // One day in the future

    $sessions = array();
    foreach ($author_groups as $group_name) {
        $sessions[] = $instance->createSession($groupmap[$group_name], $authorID, $validUntil)->sessionID;
    }

    setcookie('sessionIDExpiration', time(), $validUntil, '/', HOST_NAME);
    setcookie('sessionID', implode(",",$sessions), $validUntil, '/', HOST_NAME);
}

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
  ob_start();
  if ($_GET['tag']) $tagWhere = ' tags LIKE ' . $db->quote("%$_GET[tag]%");
    else $tagWhere = ' NOT (tags LIKE "%archiv%") ';
  $pads = sql('SELECT * FROM padman_pad_cache WHERE group_mapper = ? AND '.$tagWhere.' ORDER BY last_edited DESC', array($group));
  echo '<div class="table-responsive"><table class="table table-hover">';
  echo '<thead><tr><th width=30></th><th>Name</th><th width=350>Passwort</th><th width=100></th></tr></thead><tbody>';
  foreach ($pads as $PAD) {
    $PAD["icon_html"] = ""; $PAD["className"] = "";
    if ($PAD["access_level"] == 1) {
      $PAD["icon_html"] = '<span class="glyphicon glyphicon-globe"></span> '; $PAD["public"]="true"; $PAD["className"]="";
    } else{
      $PAD["icon_html"] = '<span class="glyphicon glyphicon-home"></span> '; $PAD["public"]="false";
    }
    if ($PAD["shortlink"]) $PAD["shortlink"] = SHORTLNK_PREFIX.$PAD["shortlink"];
    
    load_view("pad_list_item", $PAD);
  }
  echo "</tbody></table></div>";
  if (count($pads) == 0) echo "<div style='padding:100px 0;text-align:center;color:#aaa;'>- In dieser Kategorie gibt es noch keine Pads -</div>";
  $result["html"] = ob_get_clean();
  
  $groupinfo = sql("SELECT tags FROM padman_group_cache WHERE group_mapper = ?", array($group))[0];
  $result["tags"] = $groupinfo["tags"]=="" ? [] : explode(" ",$groupinfo["tags"]);
  
  header("Content-Type: application/json; charset=utf8");
  die(json_encode($result));
}

if (isset($_COOKIE["infobox"])) {
  $infoBox = $_COOKIE["infobox"];
  setcookie("infobox", null);
}

load_view("layout", array(
  "group_titles" => $group_titles, "groupmap" => $groupmap, "current_group" => $group, "allow_pad_create" => $allow_pad_create,
  "login" => array("cn" => $author_cn, "name" => $author_name)
));

?>
