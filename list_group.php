<?php
$verbose = false;
if (!$argv[1]) die("Usage: $argv[0] group_alias\n");

include "init.php";

$group = sql("SELECT * FROM padman_group WHERE group_alias=?", [$argv[1]])[0];
if (!$group) die("Group not found\n");

$instance = new EtherpadLiteClient(API_KEY, API_URL);

$groupmap = array();
$sessions = array();

$updategroupId = $db->prepare("UPDATE padman_group SET group_id=? WHERE group_mapper=?");
$updateQ = $db->prepare("UPDATE padman_pad_cache SET group_id=?  WHERE group_alias=? LIMIT 1");

$oldmapGroup = $instance->createGroupIfNotExistsFor($group["group_mapper"]);
echo "Etherpad API:\n";
$pads = $instance->listPads($oldmapGroup->groupID);
foreach($pads->padIDs as $pad) {
  echo " $pad\n";
}
echo "DB:\n";
$pads = sql("SELECT * FROM padman_pad_cache where group_alias=?", [$group["group_alias"]]);
foreach ($pads as $pad) {
  echo " ".ep_pad_id($pad)."\n";
}


