<?php
$verbose = false;
if (!$argv[1] || !$argv[2]) die("Usage: $argv[0] group_alias new_group_mapper\n");

include "init.php";

$thegroup = sql("SELECT * FROM padman_group WHERE group_alias=?", [$argv[1]])[0];
if (!$thegroup) die("Group not found\n");

$instance = new EtherpadLiteClient(API_KEY, API_URL);

$groupmap = array();
$sessions = array();

$updategroupId = $db->prepare("UPDATE padman_group SET group_id=?,group_mapper=? WHERE group_alias=?");
$updateQ = $db->prepare("UPDATE padman_pad_cache SET group_id=?,group_mapper=''  WHERE group_alias=?");

$oldmapGroup = $instance->createGroupIfNotExistsFor($thegroup["group_mapper"]);
$newmapGroup = $instance->createGroupIfNotExistsFor($argv[2]);

$pads = sql("SELECT * FROM padman_pad_cache where group_alias=? and group_id=?", [$thegroup["group_alias"], $oldmapGroup->groupID]);
foreach ($pads as $pad) {
  $fromName = ep_pad_id($pad);
  $toName = $newmapGroup->groupID.'$'.$thegroup["group_alias"].'_'.$pad["pad_name"];
  $instance->movePad($fromName, $toName);
  echo "Rename: $fromName -> $toName\n";
}

$updateQ->execute([ $newmapGroup->groupID, $thegroup["group_alias"] ]);
$updategroupId->execute([ $newmapGroup->groupID, $argv[2], $thegroup["group_alias"] ]);

