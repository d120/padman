<?php
include "config.inc.php";
//include "jsondb.inc.php";
include "views.inc.php";
include "private/etherpad-lite-client.php";

try {
$db = new PDO(DATABASE_URI, DATABASE_USER, DATABASE_PASSWORD);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(Exception $ex) {
// avoid the default error message as it might display the db password
die("<H2>Eile mit Weile</H2>Database connection failed!");
}

define('DATA_DIR', dirname(__FILE__).'/data');
define('VIEW_DIR', dirname(__FILE__).'/views');

$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);



$group_aliases = $db->query("select * from padman_group order by position asc")->fetchAll();
$group_keys = array_unique(array_map(function($d) { return $d['group_mapper']; }, $group_aliases));

$author_groups = $group_keys;

// use this if you got the author groups from somewhere else (e.g. permissions database, LDAP, ...):
// $author_groups = array_intersect($author_groups, $group_keys);


$groupmap = array();
foreach($group_aliases as $d)
    if (array_search($d['group_mapper'], $author_groups) !== false)
        $groupmap[$d['group_mapper']] = $d['group_id'];




function sql($query, $args, $noquery = false) {
  global $db;
  $q = $db->prepare($query);
  $q->execute($args);
  if ($noquery) return $q->rowCount(); else return $q->fetchAll();
}

function set_pad_password($pad, $passwd) {
  global $instance, $db;
  $ok=$instance->setPassword(ep_pad_id($pad), $passwd);
  $db->prepare("UPDATE padman_pad_cache SET password =? WHERE id=?")
     ->execute(array($passwd, $pad["id"]));
  return $ok;
}
function get_pad_by_id($padid) {
  global $db;
  $q = $db->prepare("SELECT * FROM padman_pad_cache WHERE id = ?");
  if (!$q->execute([ intval($padid) ])) throw new Exception("Error loading pad details ".intval($padid));
  $pad = $q->fetch();
  if (!$pad) throw new Exception("Pad not found ".intval($padid));
  return $pad;
}
function ep_pad_id($pad) {
  if ($pad["group_mapper"]) //altes Namensschema:
    return $pad["group_id"].'$'.$pad['pad_name'];
  else
    return $pad["group_id"].'$'.$pad["group_alias"]."_".$pad["pad_name"];
}
function update_pad($padid, $update) {
  global $db;
  $args = array_values($update);
  $q = $db->prepare("UPDATE padman_pad_cache SET `".implode('`=?,`',array_keys($update))."`=? WHERE id=?");
  $args[] = $padid;
  $q->execute($args);
}
function dump_pad_to_file($padID, $padname, $group) {
  global $instance;

  $result = $instance->getHTML($padID);
  if(!$result->html) throw new Exception("dump_pad_to_file failed!");
  $fn = DATA_DIR."/archive/".urlencode($group["group_alias"])."/".urlencode($padname).".html";
  @mkdir(DATA_DIR."/archive"); @mkdir(dirname($fn));
  file_put_contents($fn, $result->html);

  $result = $instance->getText($padID);
  if(!$result->text) throw new Exception("dump_pad_to_file failed!");
  $fn = DATA_DIR."/index/".urlencode($group["group_alias"])."/".urlencode($padname).".txt";
  @mkdir(DATA_DIR."/index"); @mkdir(dirname($fn));
  file_put_contents($fn, $result->text);
}
function get_archived_pad_content($pad) {
  $fn = DATA_DIR."/archive/".urlencode($group["group_alias"])."/".urlencode($pads[0]['pad_name']).".html";
  $cont = file_get_contents($fn);
  $cont = str_replace("<a href=\"", "<a target=\"_blank\" href=\"", $cont);

}
function refresh_group($group_alias) {
  global $db;
  $tags = array();
  $tagresult = sql("SELECT tags FROM padman_pad_cache WHERE group_alias = ?", [ $group_alias ]);
  foreach($tagresult as $d) {
    $tags = array_merge($tags, explode(" ", trim($d["tags"])));
  }
  $tags = array_unique($tags);
  natcasesort($tags);
  $db->prepare("UPDATE padman_group SET tags = ? WHERE group_alias = ?")
      ->execute([ trim(implode(" ", $tags)), $group_alias ]);
}

function pad_session_check() {
  if (!is_session_valid()) {
    load_view("error_layout", [
      "content" => get_view("session_init", [])
    ]);
    exit();
  }
}



function is_session_valid() {
  return isset($_COOKIE['sessionIDExpiration']) && ($_COOKIE['sessionIDExpiration'] > time() - 3600);
}

function create_sessions() {
  global $instance, $author_groups, $groupmap, $author_cn, $author_name;
  // if sessionID is older than one hour,  ...
  $time_start = microtime(true);
  $mutexName = "padman_user_create_session:".$author_cn;
  $ok = sql("SELECT GET_LOCK(?,15) x;", [ $mutexName ])[0]['x'];
  if (!$ok) return false;
  if (!is_session_valid()) {
    try {
        $author = $instance->createAuthorIfNotExistsFor($author_cn, $author_name);
        $authorID = $author->authorID;
    } catch(Exception $ex) {echo $ex;
        return false;
    }
    $timing.="ca() ".(microtime(true)-$time_start).";";
    $validUntil = mktime(23, 0, 0, date("m"), date("d")+1, date("y")); // One day in the future

    $sessions = array();
    foreach ($author_groups as $group_name) {
        $sessions[] = $instance->createSession($groupmap[$group_name], $authorID, $validUntil)->sessionID;
        $timing.="cs($group_name,".$groupmap[$group_name].") ".(microtime(true)-$time_start).";";
    }
    header("X-Timing: $timing");
    setcookie('sessionIDExpiration', time(), $validUntil, '/', HOST_NAME);
    setcookie('sessionID', implode(",",$sessions), $validUntil, '/', HOST_NAME);
  }
  sql("SELECT RELEASE_LOCK(?);", [$mutexName]);
  return true;
}

