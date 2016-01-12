<?php
header("Content-Type: application/json; charset=utf-8");

// JSON api
if (isset($_GET['api']) && $_GET['api'] == 'pad_info' && isset($_GET['pad_id'])) {
  $padid=explode('$',$_GET['pad_id']);
  $pad = sql("SELECT * FROM padman_pad_cache WHERE group_id=? AND pad_name=?", array($padid[0], $padid[1]));
  die(json_encode($pad));
}

if (isset($_GET['api']) && $_GET['api'] == 'list') {
  if ($_GET['tag']) $tagWhere = ' tags LIKE ' . $db->quote("%$_GET[tag]%");
    else $tagWhere = ' NOT (tags LIKE "%archiv%") ';
  $pads = sql('SELECT * FROM padman_pad_cache WHERE group_mapper = ? AND '.$tagWhere.' ORDER BY last_edited DESC', array($_GET["group"]));
  foreach($pads as &$pad) {
    $pad['last_edited_formatted'] = $pad['last_edited']=='0000-00-00 00:00:00' ? '' : date("d.m.y H:i",strtotime($pad['last_edited']));
  }
  die(json_encode([ "pads" => $pads ]));
}

if (isset($_GET['api']) && $_GET['api'] == 'search' && isset($_GET['q'])) {
  $q = "%$_GET[q]%";
  $pads = sql('SELECT group_mapper,pad_name FROM padman_pad_cache WHERE group_mapper LIKE ? OR pad_name LIKE ? ORDER BY last_edited DESC LIMIT 20', [ $q, $q ]);
  die(json_encode([ "result" => $pads ]));
}

if (isset($_POST['set_config'])) {
  if (!$userinfo) sql("INSERT INTO padman_user SET user = ?", [ $author_cn ], true);
  sql("UPDATE padman_user SET alias = ? WHERE user = ?", [ $_POST["alias"], $author_cn ], true); 
  die(json_encode([ "success" => true ]));
}

if (isset($_POST['create_sessions'])) {
  create_sessions();
  die(json_encode([ "success" => true ]));
}


if (isset($_POST['set_public']) && isset($_POST['pad_id'])) {
  $padname = $_POST['pad_id'];
  $public = $_POST['set_public'] == 'true';
  $ok=$instance->setPublicStatus($padname, $public);
  $sl = null;
  if ($public) {
    if (isset($_POST['shortlnk'])) $sl = preg_replace('/[^a-z0-9]/','',$_POST['shortlnk']);
    if (!$sl) $sl = substr(md5($padname),0,7);
  }
  update_pad($padname, array('shortlink' => $sl, 'access_level' => $public ? 1 : 0));
  die(json_encode(array("status"=>"ok","shortlnk"=>SHORTLNK_PREFIX.$sl)));
}
if (isset($_POST['set_passw']) && isset($_POST['pad_id'])) {
  $padname = $_POST['pad_id'];
  $ok=setPassword($padname, preg_replace('/[^a-zA-Z0-9_.-]/','',$_POST['set_passw']));
  die(json_encode(array("status"=>"ok")));
}
if (isset($_POST['delete_this_pad']) && isset($_POST['pad_id'])) {
  if (defined('DELETE_PASSWORD') && strlen(DELETE_PASSWORD) > 0 && DELETE_PASSWORD != $_POST['delete_this_pad'])
      die(json_encode(array("status"=>"access_denied")));
  $padname = $_POST['pad_id'];
  $ok=$instance->deletePad($padname);
  
  $padid=explode('$',$padname);
  $db->prepare("DELETE FROM padman_pad_cache  WHERE group_id=? AND pad_name=?")
     ->execute(array($padid[0], $padid[1]));
  
  die(json_encode(array("status"=>"ok")));
}

if (isset($_POST['set_tags']) && isset($_POST['pad_id'])) {
  $padname = $_POST['pad_id'];
  update_pad($padname, array('tags' =>
                      preg_replace('/[^a-zA-Z0-9 ]/','',
                      preg_replace('/[ ,;]+/',' ',
                            $_POST['set_tags']
                  ))));

  die(json_encode(array("status"=>"ok")));
}
if (isset($_POST['rename']) && isset($_POST['pad_id'])) {
  $padname = $_POST['pad_id'];
  try {
    $ok=$instance->movePad($padname, $_POST['rename']);
  } catch(Exception $ex) {
    die(json_encode(array("status"=>"error", "msg"=>"$ex")));
  }

  $p = explode('$', $_POST['rename']);
  $new_group_mapper = sql("SELECT group_mapper FROM padman_group_cache WHERE group_id=?", array($p[0]))[0]['group_mapper'];
  update_pad($padname, array('group_mapper' => $new_group_mapper, 'group_id' => $p[0], 'pad_name' => $p[1]));

  die(json_encode(array("status"=>"ok")));
}


// response to create pad form
if (isset($_POST['createPadinGroup'])) {
  
  if (isset($_POST['start_sitzung'])) {
    $padname = 'Sitzung' . date('Ymd');
    $passwd = mt_rand(10000, 99999);
  } else {
    $padname = $_POST['pad_name'];
    $starttext = "Willkommen im wesentlichen Etherpad auf D120.de!\r\n\r\n";
  }

  try {
    $instance->createGroupPad($groupmap[$group], $padname, '');
    $padid = $groupmap[$group] . '$' . $padname;
    $db->prepare('INSERT INTO padman_pad_cache (group_mapper, group_id, pad_name, last_edited) VALUES (?,?,?,NOW())')
       ->execute(array($group, $groupmap[$group], $padname));
    if (isset($_POST['start_sitzung'])) {
      update_pad($padid, array("shortlink" => 'si'.date('md')));
      $instance->setPublicStatus($padid, true);
      setPassword($padid, $passwd);
      
      $starttext = file_get_contents('template-sitzung.txt');
      $starttext = str_replace("{{heute}}", date("d.m.Y"), $starttext);
      $starttext = "Kurzlink zum Pad: ".SHORTLNK_PREFIX.'si'.date('md')."\nPasswort: $passwd\n\n" . $starttext;
      
      $instance->setText($padid, $starttext);
    }
    
    setcookie("infobox", "<div class='alert alert-success'><button type='button' class='close' onclick='location=location.href'><span aria-hidden='true'>&times;</span><span class='sr-only'>Close</span></button>
      <h4><i class='glyphicon glyphicon-ok-circle'></i> Pad ".$padname." erfolgreich angelegt!</h4>".
      '<p><a href="'.SELF_URL.'?group='.$group.'&show='.$padname.'" class="btn btn-success btn-lg">Jetzt öffnen</a></p>
      </div>');
  } catch (Exception $e) {
    setcookie("infobox","<div class='alert alert-danger'><button type='button' class='close' onclick='location=location.href'><span aria-hidden='true'>&times;</span><span class='sr-only'>Close</span></button>
      <h4><i class='glyphicon glyphicon-warning-sign'></i> Neues Pad konnte nicht erstellt werden.</h4>
      <p>".$e->getMessage()."</p></div>\n");

  }
  header("HTTP/1.1 303 See other");
  header("Location: ".SELF_URL.$group);
}
