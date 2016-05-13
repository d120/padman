<?php
$verbose = false;
if (!$argv[1] || $argv[1]!="--update") exit;

include "init.php";


$instance = new EtherpadLiteClient(API_KEY, API_URL);

$groupmap = array();
$sessions = array();

$updategroupId = $db->prepare("UPDATE padman_group SET group_id=? WHERE group_mapper=?");
$updateQ = $db->prepare("UPDATE padman_pad_cache SET group_id=?  WHERE group_alias=?");

$shown_groups = sql("SELECT group_alias,group_mapper FROM padman_group", []);
foreach ($shown_groups as $group) {
  $mapGroup = $instance->createGroupIfNotExistsFor($group["group_mapper"]);
  $groupmap[$group["group_mapper"]] = $mapGroup->groupID;
  $updategroupId->execute([ $mapGroup->groupID, $group["group_mapper"] ]);
  $updateQ->execute([ $mapGroup->groupID, $group["group_alias"] ]);
  echo "$group[group_mapper] - ".$mapGroup->groupID."\n";
  refresh_group($group["group_alias"]);
}


