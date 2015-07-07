<?php

class JsonDB {
  
  public static $DATA_DIR;
  public $p;
  public $filename;
  
  public function __construct($filename) {
    $this->filename = $filename;
    $this->p = JsonDB::load($filename);
  }
  
  public function store($key, $value) {
    $this->p[$key] = $value;
    file_put_contents(JsonDB::$DATA_DIR.$this->filename.'.json', json_encode($this->p));
  }
  
  public function read($key) {
    return isset($this->p[$key]) ? $this->p[$key] : '';
  }
  
  public static function load($filename) {
    $p=@json_decode(file_get_contents(JsonDB::$DATA_DIR.$filename.'.json'),true);
    if (!is_array($p)) $p=array();
    return $p;
  }
  
  public function move($oldkey, $newkey) {
    $this->store($newkey, $this->read($oldkey));
    $this->store($oldkey, null);
  }

}

JsonDB::$DATA_DIR = dirname(__FILE__)."/data/";
