<?php
$verbose = false;
if (isset($argv[1]) && $argv[1]=="-v") $verbose=true;

include "init.php";
try{
  $passwords = @json_decode(@file_get_contents('data/passwords.json'), true);
  $shortlinks = @json_decode(@file_get_contents('data/shortlnk.json'), true);
} catch(Exception $ex){ $passwords=array();$shortlinks=array(); }


$instance = new EtherpadLiteClient(API_KEY, API_URL);

$sessions = array();

$updategroupId = $db->prepare("UPDATE padman_group SET group_id=? WHERE group_mapper=?");
$insertQ = $db->prepare("INSERT ignore INTO padman_pad_cache (group_mapper, group_id, pad_name, password, shortlink)
             VALUES ('', ?, ?, ?, ?)");
$updateQ = $db->prepare("UPDATE padman_pad_cache SET last_edited=FROM_UNIXTIME(?), access_level=?
                        WHERE id=? LIMIT 1");

$groups = sql("SELECT * FROM padman_group" , []);
foreach($groups as $group) {
  $pads = sql("SELECT * FROM padman_pad_cache WHERE group_alias=?", [$group["group_alias"]]);
  foreach($pads as $pad) {
    $padID = ep_pad_id($pad);
    if($verbose)echo "$padID\n";
    try {
      $tmpTimest = $instance->getLastEdited($padID);
      $tmpPublic = $instance->getPublicStatus($padID);
      $timestamp = floor($tmpTimest->lastEdited/1000);
      $accessLevel = $tmpPublic->publicStatus ? 1 : 0;
      if($verbose)echo "   $timestamp    $accessLevel\n";
      $updateQ->execute([  $timestamp, $accessLevel, $pad["id"] ]);
    } catch(Exception $ex) {
      echo "Pad $padID found in DB, but not in Etherpad! Please check!\n$ex\n";
      $updateQ->execute([ 0, 999, $pad["id"] ]);
    }
  }
}

exit;
foreach($groupmap as $group => $groupID) {
  if($verbose)echo "Retrieving pads in group \"$group\" ... ";
  $pads = $instance->listPads($groupID);
  if($verbose)echo "OK \n";
  
  if($verbose)echo "Indexing pads in group \"$group\" ";

  $validIDs = [];
  $tags = array();
  foreach ($pads->padIDs as $padID) {
    $parts=explode('$',$padID); $padname = $parts[1];
    $validIDs[$padID] = true;

    //cache content
    $result = $instance->getText($padID);
    $fn = DATA_DIR."/index/".urlencode($group)."/".urlencode($padname).".txt";
    @mkdir(DATA_DIR."/index"); @mkdir(dirname($fn));
    file_put_contents($fn, $result->text);
    if($verbose)echo ".";
    $insertQ->execute(array($group, $groupID, $padname,
                            isset($passwords[$padID])?$passwords[$padID]:null,
                            isset($shortlinks[$padID])?$shortlinks[$padID]:null));
    
    $tmpTimest = $instance->getLastEdited($padID);
    $tmpPublic = $instance->getPublicStatus($padID);
    $updateQ->execute(array($groupID, floor($tmpTimest->lastEdited/1000), $tmpPublic->publicStatus ? 1 : 0,
                            $group, $padname));
    
    $getTagsQ->execute(array($group, $padname));
    $tagstr = trim($getTagsQ->fetchColumn());
    if ($tagstr) {
      $thistags = explode(" ",$tagstr);
      $tags = array_merge($tags, $thistags);
    }
  }
  $allPads = sql("SELECT * FROM padman_pad_cache WHERE group_id = ?", [$groupID]);
  $db->exec($delQuery);
  
  $insertGroupQ->execute(array($group, $groupID, implode(" ",array_unique($tags))));
  
  if($verbose)echo " OK \n";
}


