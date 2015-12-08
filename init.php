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

function sql($query, $args) {
  global $db;
  $q = $db->prepare($query);
  $q->execute($args);
  return $q->fetchAll();
}

function update_pad($padid, $update) {
  global $db;
  $args = array_values($update);
  $q = $db->prepare("UPDATE padman_pad_cache SET `".implode('`=?,`',array_keys($update))."`=? WHERE group_id=? AND pad_name=?");
  $p = explode('$', $padid);
  $args[] = $p[0]; $args[] = $p[1];
  $q->execute($args);
}
