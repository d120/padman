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

function sql($query, $args, $noquery = false) {
  global $db;
  $q = $db->prepare($query);
  $q->execute($args);
  if ($noquery) return $q->rowCount(); else return $q->fetchAll();
}

function update_pad($padid, $update) {
  global $db;
  $args = array_values($update);
  $q = $db->prepare("UPDATE padman_pad_cache SET `".implode('`=?,`',array_keys($update))."`=? WHERE group_id=? AND pad_name=?");
  $p = explode('$', $padid);
  $args[] = $p[0]; $args[] = $p[1];
  $q->execute($args);
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
        $timing.="cs($group_name) ".(microtime(true)-$time_start).";";
    }
header("X-Timing: $timing");
    setcookie('sessionIDExpiration', time(), $validUntil, '/', HOST_NAME);
    setcookie('sessionID', implode(",",$sessions), $validUntil, '/', HOST_NAME);
  }
  sql("SELECT RELEASE_LOCK(?);", [$mutexName]);
  return true;
}

