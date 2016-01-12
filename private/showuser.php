<?php

try {
  $author = $instance->createAuthorIfNotExistsFor($author_cn, $author_name);
  $authorID = $author->authorID;
} catch(Exception $ex) {
  die("<h2>Eile mit Weile - Etherpad ist zur Zeit nicht erreichbar</h2>");
}

// $sessions = $instance->listSessionsOfAuthor($authorID);

$edited = $instance->listPadsOfAuthor($authorID);
$edited_pads = array();
$map_group = array_flip($groupmap);
for($i = count($edited->padIDs) - 1; $i >= 0; $i--) {
  $padID = $edited->padIDs[$i];
  #$edited_pads [] = sql("SELECT group_mapper, pad_name FROM padman_pad_cache WHERE 
  $pad = explode('$', $padID);
  $pad[0] = $map_group[$pad[0]];
  $edited_pads[] = $pad;
}

load_view("error_layout", [
  "content" => get_view("user", [
    "userinfo" => $userinfo,
    "edited_pads" => $edited_pads,
    "authorID" => $authorID,
    "sessions" => $sessions
  ])
]);


