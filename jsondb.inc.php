<?php

define("DATA_DIR", dirname(__FILE__)."/data/");

function storeJson($filename, $key, $value) {
  $p=loadJson($filename);
  $p[$key] = $value;
  file_put_contents(DATA_DIR.$filename.'.json', json_encode($p));
}

function readJson($filename, $key) {
  $p=loadJson($filename);
  return isset($p[$key]) ? $p[$key] : '';
}

function loadJson($filename) {
  $p=@json_decode(file_get_contents(DATA_DIR.$filename.'.json'),true);
  if (!is_array($p)) $p=array();
  return $p;
}

function moveJson($filename, $oldkey, $newkey) {
  storeJson($filename, $newkey, readJson($filename, $oldkey));
  storeJson($filename, $oldkey, null);
}

