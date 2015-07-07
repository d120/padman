<?php

include "config.inc.php";
include "jsondb.inc.php";
include "private/etherpad-lite-client.php";
$shown_groups = array_map("strtolower", $shown_groups_titles);

$instance = new EtherpadLiteClient(API_KEY, API_URL);

$groupmap = array();
$sessions = array();

foreach ($shown_groups as $group_name) {
  $mapGroup = $instance->createGroupIfNotExistsFor($group_name);
  $groupmap[$group_name] = $mapGroup->groupID;
}
foreach($groupmap as $group => $groupID) {
  echo "Retrieving pads in group \"$group\" ... ";
  $pads = $instance->listPads($groupID);
  echo "OK \n";
  
  echo "Indexing pads in group \"$group\" ";
  foreach ($pads->padIDs as $padID) {
    $parts=explode('$',$padID); $padname = $parts[1];
    //cache content
    $result = $instance->getText($padID);
    $fn = JsonDB::$DATA_DIR."index/".urlencode($group)."/".urlencode($padname).".txt";
    @mkdir(JsonDB::$DATA_DIR."index"); @mkdir(dirname($fn));
    file_put_contents($fn, $result->text);
    echo ".";
  }
  echo " OK \n";
  
}
