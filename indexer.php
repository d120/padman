<?php

include "config.inc.php";
include "jsondb.inc.php";
include "private/etherpad-lite-client.php";
try{
  $passwords = json_decode(file_get_contents('data/passwords.json'), true);
  $shortlinks = json_decode(file_get_contents('data/shortlnk.json'), true);
} catch(Exception $ex){ $passwords=array();$shortlinks=array(); }

$shown_groups = array();
foreach($GROUP_TITLES as $d) if (is_array($d)) $group_keys = array_merge($group_keys, $d); else $group_keys [] = $d;
$shown_groups = array_unique(array_map("strtolower", $group_keys));

$instance = new EtherpadLiteClient(API_KEY, API_URL);

$groupmap = array();
$sessions = array();

$insertGroupQ = $db->prepare("INSERT IGNORE INTO padman_group_cache (group_mapper, group_id, tags) VALUES (?, ?, ?)");
$insertQ = $db->prepare("INSERT ignore INTO padman_pad_cache (group_mapper, group_id, pad_name, password, shortlink)
             VALUES (?, ?, ?, ?, ?)");
$updateQ = $db->prepare("UPDATE padman_pad_cache SET group_id=?, last_edited=FROM_UNIXTIME(?), access_level=?
                        WHERE group_mapper=? AND pad_name=? LIMIT 1");
$getTagsQ = $db->prepare("SELECT tags FROM padman_pad_cache WHERE group_mapper=? AND pad_name=? LIMIT 1");

$db->exec("TRUNCATE TABLE padman_group_cache");
foreach ($shown_groups as $group_name) {
  $mapGroup = $instance->createGroupIfNotExistsFor($group_name);
  $groupmap[$group_name] = $mapGroup->groupID;
}
foreach($groupmap as $group => $groupID) {
  echo "Retrieving pads in group \"$group\" ... ";
  $pads = $instance->listPads($groupID);
  echo "OK \n";
  
  echo "Indexing pads in group \"$group\" ";
  
  $delQuery="DELETE FROM padman_pad_cache WHERE group_id = ".$db->quote($groupID);
  $tags = array();
  foreach ($pads->padIDs as $padID) {
    $parts=explode('$',$padID); $padname = $parts[1];
    $delQuery.=" AND pad_name<>".$db->quote($padname);
    //cache content
    $result = $instance->getText($padID);
    $fn = JsonDB::$DATA_DIR."index/".urlencode($group)."/".urlencode($padname).".txt";
    @mkdir(JsonDB::$DATA_DIR."index"); @mkdir(dirname($fn));
    file_put_contents($fn, $result->text);
    echo ".";
    $insertQ->execute(array($group, $groupID, $padname,
                            isset($passwords[$padID])?$passwords[$padID]:null,
                            isset($shortlinks[$padID])?$shortlinks[$padID]:null));
    
    $tmpTimest = $instance->getLastEdited($padID);
    $tmpPublic = $instance->getPublicStatus($padID);
    $updateQ->execute(array($groupID, floor($tmpTimest->lastEdited/1000), $tmpPublic->publicStatus ? 1 : 0,
                            $group, $padname));
    
    $getTagsQ->execute(array($group, $padname));
    $thistags = explode(" ",$getTagsQ->fetchColumn());
    $tags = array_merge($tags, $thistags);
  }
  $db->exec($delQuery);
  
  $insertGroupQ->execute(array($group, $groupID, implode(" ",array_unique($tags))));
  
  echo " OK \n";
}


