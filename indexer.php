<?php
$verbose = false;
if (isset($argv[1]) && $argv[1]=="-v") $verbose=true;

include "init.php";

$instance = new EtherpadLiteClient(API_KEY, API_URL);
$sessions = array();

$updategroupId = $db->prepare("UPDATE padman_group SET group_id=? WHERE group_mapper=?");
$updateQ = $db->prepare("UPDATE padman_pad_cache SET last_edited=FROM_UNIXTIME(?), access_level=?
                        WHERE id=? LIMIT 1");

if (ARCHIVE_AFTER_MONTHS)
  $archive_before = (new DateTime(ARCHIVE_AFTER_MONTHS." months ago"))->gettimestamp();
else
  $archive_before = false;

$groups = sql("SELECT * FROM padman_group" , []);
foreach($groups as $group) {
  $pads = sql("SELECT * FROM padman_pad_cache WHERE group_alias=? AND is_archived=0", [$group["group_alias"]]);
  foreach($pads as $pad) {
    $padID = ep_pad_id($pad);
    if($verbose)echo "$padID\n";
    try {
      $tmpTimest = $instance->getLastEdited($padID);
      $tmpPublic = $instance->getPublicStatus($padID);
      $timestamp = floor($tmpTimest->lastEdited/1000);
      $accessLevel = $tmpPublic->publicStatus ? 1 : 0;
      if($verbose)echo "   $timestamp    $accessLevel\n";
      dump_pad_to_file($padID, $pad['pad_name'], $group);
      if($archive_before && ($timestamp < $archive_before)) {
        echo "Archiving the pad $padID  $pad[pad_name] $group[group_alias] \n";
        $ok=$instance->deletePad($padID);
        echo "deletePad($padID);\n";
        update_pad($pad['id'], [ "is_archived" => 1, "group_mapper" => "" ]);
      }
      $updateQ->execute([ $timestamp, $accessLevel, $pad["id"] ]);
    } catch(Exception $ex) {
      fprintf(STDERR, "%s", "Pad $padID found in DB, but not in Etherpad! Please check!\n$ex\n";
      $updateQ->execute([ 0, 999, $pad["id"] ]);
    }
  }
}

