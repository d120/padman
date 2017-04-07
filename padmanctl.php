#!/usr/bin/env php
<?php
chdir(dirname(__FILE__));
$opt = getopt("vyLGRNCRVa:t:m:p:");

$verbose = isset($opt['v']);
if (!$opt) usage();

function usage() {
  $CMD = $GLOBALS['argv'][0];
  echo <<<USAGE
List groups:
   $CMD [options] -L
List pads:
   $CMD [options] -L -a <group_alias>
Rehash group ids:
   $CMD [options] -R
New group:
   $CMD [options] -N -a <group_alias> -t <menu_title> -m <group_mapper> -p <position>
Change group:
   $CMD [options] -C -a <group_alias> [-t <menu_title>] [-m <group_mapper>] [-p <position>] [-b <new_group_alias>]
Delete group:
   $CMD [options] -D -a <group_alias>
Version:
   $CMD [options] -V

Options:
     -v    verbose
     -y    yes, do it, don't ask

USAGE;
  exit(1);
}

include "init.php";

$instance = new EtherpadLiteClient(API_KEY, API_URL);

if (isset($opt['L'])) {
  if (isset($opt['a'])) {
    list_pads_in_group($opt['a']);
  } else {
    list_groups();
  }
} elseif (isset($opt['R'])) {
  rehash_groups();
} elseif (isset($opt['N']) && $opt['a'] && $opt['t'] && $opt['m'] && intval($opt['p']) > 0) {
  sql("insert into padman_group (menu_title, group_alias, group_mapper, position) values (?,?,?,?);",
    [$opt['t'], $opt['a'], $opt['m'], $opt['p'] ], TRUE);
  echo "Created group #".$db->lastInsertId()."\n";
  rehash_groups();
} elseif (isset($opt['C']) ) {
  $thegroup = sql("SELECT * FROM padman_group WHERE group_alias=?", [$opt['a']])[0];
  if (!$thegroup) die("Group not found\n");

  $sql=[]; $p=[];
  if ($opt['t']) { $sql[] = " menu_title=? "; $p[] = $opt['t']; }
  if ($opt['b']) { $sql[] = " group_alias=? "; $p[] = $opt['b']; }
  if ($opt['m']) { group_change_mapper($thegroup, $opt['m']); }
  if ($opt['p']) { if(intval($opt['p'])<1)die("Invalid position parameter"); $sql[] = " position=? "; $p[] = $opt['p']; }
  $p[]=$thegroup['group_alias'];

  sql("update padman_group set ".implode(",", $sql)." where group_alias=?", $p, TRUE);
  echo "Updated group #$thegroup[id]\n";
  rehash_groups();
} elseif (isset($opt['D'])) {
  $thegroup = sql("SELECT * FROM padman_group WHERE group_alias=?", [$opt['a']])[0];
  if (!$thegroup) die("Group not found\n");

  if (!isset($opt['y'])) {
    echo "Really delete group #$thegroup[id] with title = \"$thegroup[menu_title]\"? [y/N] "; $ok = fgets(STDIN);
    if (trim($ok) != "y") die();
  }
  sql("DELETE padman_group FROM group_alias=?", [$opt['a']], TRUE);

} elseif (isset($opt['V'])) {
  system('git --no-pager log -1 --format="(%cr) %h %s"');

} else {
  usage();
}



function list_groups() {
global $verbose, $instance, $db;
  $groups = sql("select id, menu_title, group_alias, group_mapper, position, tags from padman_group order by position;", []);
  foreach($groups as $d) {
    printf("% 3d  % -20s % -40s %05d %s\n", $d["id"], $d["group_mapper"], $d["group_alias"], $d["position"], $d["menu_title"]);
  }
}

function list_pads_in_group($group_alias) {
global $verbose, $instance, $db;
  $group = sql("SELECT * FROM padman_group WHERE group_alias=?", [$group_alias])[0];
  if (!$group) die("Group not found\n");


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
}

function group_change_mapper($thegroup, $new_group_mapper) {
global $verbose, $instance, $db;

  $groupmap = array();
  $sessions = array();

  $updategroupId = $db->prepare("UPDATE padman_group SET group_id=?,group_mapper=? WHERE group_alias=?");
  $updateQ = $db->prepare("UPDATE padman_pad_cache SET group_id=?,group_mapper=''  WHERE group_alias=?");

  $oldmapGroup = $instance->createGroupIfNotExistsFor($thegroup["group_mapper"]);
  $newmapGroup = $instance->createGroupIfNotExistsFor($new_group_mapper);

  $pads = sql("SELECT * FROM padman_pad_cache where group_alias=? and group_id=?", [$thegroup["group_alias"], $oldmapGroup->groupID]);
  foreach ($pads as $pad) {
    $fromName = ep_pad_id($pad);
    $toName = $newmapGroup->groupID.'$'.$thegroup["group_alias"].'_'.$pad["pad_name"];
    echo "Rename: $fromName -> $toName\n";
    $instance->movePad($fromName, $toName);
  }

  $updateQ->execute([ $newmapGroup->groupID, $thegroup["group_alias"] ]);
  $updategroupId->execute([ $newmapGroup->groupID, $new_group_mapper, $thegroup["group_alias"] ]);
}

function rehash_groups() {
global $verbose, $instance, $db;
  $groupmap = array();
  $sessions = array();

  $updategroupId = $db->prepare("UPDATE padman_group SET group_id=? WHERE group_mapper=?");
  $updateQ = $db->prepare("UPDATE padman_pad_cache SET group_id=?  WHERE group_alias=?");

  $shown_groups = sql("SELECT group_alias,group_mapper FROM padman_group", []);
  echo "Rehashing ".count($shown_groups)." groups "; if($verbose)echo "\n";
  foreach ($shown_groups as $group) {
    $mapGroup = $instance->createGroupIfNotExistsFor($group["group_mapper"]);
    $groupmap[$group["group_mapper"]] = $mapGroup->groupID;
    $updategroupId->execute([ $mapGroup->groupID, $group["group_mapper"] ]);
    $updateQ->execute([ $mapGroup->groupID, $group["group_alias"] ]);
    if($verbose)echo "$group[group_mapper]\t - $group[group_alias]\t - ".$mapGroup->groupID."\n";
    else echo ".";
    refresh_group($group["group_alias"]);
  }
  echo "\n";
}

