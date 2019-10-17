<?php
header("Content-Type: application/json; charset=utf-8");

// JSON api
if (isset($_GET['api']) && $_GET['api'] == 'pad_info' && isset($_GET['pad_id'])) {
  $pad = sql("SELECT * FROM padman_pad_cache WHERE id=?", array($_GET["pad_id"]));
  $pad['tags'] = trim($pad['tags']);
  die(json_encode($pad));
}

if (isset($_GET['api']) && $_GET['api'] == 'search' && isset($_GET['q'])) {
  $query = explode(" ", $_GET['q']);
  $where = ""; $para = array();
  $limit = " LIMIT ".intval($_GET["offset"]).",21";
  foreach($query as $q) {
    if (substr($q,0,1)=="!") { $where .= " NOT "; $q=substr($q,1); }
    if (substr($q,0,1)=="#") {
      $q = "% ".substr($q,1);
      $where .= " (tags LIKE ? OR tags LIKE ?) AND "; $para[] = "$q %"; $para[] = "$q/%";
    } elseif (substr($q,0,4)=="tag:") {
      $q = "%".substr($q,4)."%";
      $where .= " tags LIKE ? AND "; $para[] = $q;
    } elseif (substr($q,0,1)=="&") {
      $q = "".substr($q,1)."";
      $where .= " group_alias LIKE ? AND "; $para[] = $q; $limit="";
    } else {
      $q = "%$q%";
      $where .= " (pad_name LIKE ? OR tags LIKE ? OR group_alias LIKE ?) AND "; $para[] = $q;$para[] = $q;$para[]=$q;
    }
  }

  $fields="*";
  $pads = sql('SELECT '.$fields.' FROM padman_pad_cache WHERE '.$where.' 1=1 ORDER BY last_edited DESC '.$limit, $para);
  foreach($pads as &$pad) {
    $pad['last_edited_formatted'] = $pad['last_edited']=='0000-00-00 00:00:00' ? '' : date("d.m.y H:i",strtotime($pad['last_edited']));
    $pad['tags'] = trim($pad['tags']);
  }
  $nextPageOffset = false;
  if (count($pads) == 21 && $limit) {
    $nextPageOffset = intval($_GET["offset"])+20;
    array_splice($pads, 20, 1);
  }
  die(json_encode(["query"=>$where,"q"=>$para, "result" => $pads, "next_page_offset" => $nextPageOffset ]));
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
  $pad = get_pad_by_id($_POST['pad_id']);
  $public = $_POST['set_public'] == 'true';
  $ok=$instance->setPublicStatus(ep_pad_id($pad), $public);
  $sl = null;
  if ($public) {
    if (isset($_POST['shortlnk'])) $sl = preg_replace('/[^a-z0-9]/','',$_POST['shortlnk']);
    if (!$sl) $sl = substr(md5(API_KEY.ep_pad_id($pad)),0,8);
  }
  update_pad($pad['id'], array('shortlink' => $sl, 'access_level' => $public ? 1 : 0));
  die(json_encode(array("status"=>"ok","shortlnk"=>SHORTLNK_PREFIX.$sl)));
}
if (isset($_POST['set_passw']) && isset($_POST['pad_id'])) {
  $pad = get_pad_by_id($_POST['pad_id']);
  $ok=set_pad_password($pad, preg_replace('/[^a-zA-Z0-9_.-]/','',$_POST['set_passw']));
  die(json_encode(array("status"=>"ok")));
}
if (isset($_POST['delete_this_pad']) && isset($_POST['pad_id'])) {
  if (defined('DELETE_PASSWORD') && strlen(DELETE_PASSWORD) > 0 && DELETE_PASSWORD != $_POST['delete_this_pad'])
      die(json_encode(array("status"=>"access_denied")));
  $pad = get_pad_by_id($_POST['pad_id']);
  $ok=$instance->deletePad(ep_pad_id($pad));
  
  $padid=explode('$',$padname);
  $db->prepare("DELETE FROM padman_pad_cache  WHERE id=?")
     ->execute(array($pad["id"]));
  
  die(json_encode(array("status"=>"ok")));
}

if (isset($_POST['set_tags']) && isset($_POST['pad_id'])) {
  $pad = get_pad_by_id($_POST['pad_id']);
  update_pad($pad["id"], ['tags' =>' '.
                      trim(preg_replace('/[^a-zA-Z0-9\/ ]/','',
                      preg_replace('/[ ,;]+/',' ',
                            $_POST['set_tags']
                          ))).' ']);
  refresh_group($pad["group_alias"]);

  die(json_encode(array("status"=>"ok")));
}
if (isset($_POST['rename']) && isset($_POST['pad_id']) && isset($_POST['new_group'])) {
  $pad = get_pad_by_id($_POST['pad_id']);
  $newName = $_POST['rename'];
  $new_group = sql("SELECT * FROM padman_group WHERE group_alias=?", array($_POST["new_group"]))[0];
  try {
    if (strlen($new_group['group_alias'].'_'.$newName) > 50) throw new Exception("Pad name too long");
    $oldId = ep_pad_id($pad); $newId = $new_group['group_id'].'$'.$new_group['group_alias'].'_'.$newName;
    #var_dump($oldId, $newId);
    // dangerous stuff going on here...
    ignore_user_abort(true); set_time_limit(120);
    // movePad takes ages on pads with many revisions
    $ok=$instance->movePad($oldId, $newId);
  } catch(Exception $ex) {
    die(json_encode(array("status"=>"error", "msg"=>"$ex")));
  }

  update_pad($pad['id'], ['group_mapper' => '', 'group_id' => $new_group["group_id"], 'group_alias' => $new_group["group_alias"], 'pad_name' => $newName]);
  refresh_group($pad["group_alias"]);
  refresh_group($new_group["group_alias"]);
  die(json_encode(array("status"=>"ok")));
}


if (isset($_POST['restore_archived_pad'])) {
  $pad = get_pad_by_id(intval($_POST['restore_archived_pad']));
  if (!$pad ||  ! $pad['is_archived']) die(json_encode(["status" => "not_archived"]));

  $group = sql("SELECT * FROM padman_group WHERE group_alias=?", array($pad['group_alias']))[0];

  $fn = DATA_DIR."/archive/".urlencode($group["group_alias"])."/".urlencode($pad['pad_name']).".html";
  $archived_html = file_get_contents($fn);
  if (!$archived_html) die(json_encode(["status" => "restore_failed"]));

  $instance->createGroupPad($group["group_id"], $group['group_alias'].'_'.$pad['pad_name'], '');
  update_pad($pad["id"], array( "tags" => ' '.date("Y").' ' , "is_archived" => 0 ));
  $instance->setHTML(ep_pad_id($pad), $archived_html);
  $ok=$instance->setPassword(ep_pad_id($pad), $pad['password']);
  $ok=$instance->setPublicStatus(ep_pad_id($pad), $pad['access_level'] == 1);

  header("Location: ".SELF_URL.'?group='.$group["group_alias"].'&show='.$pad['pad_name']);
  exit;
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
    if (strlen($group['group_alias'].'_'.$padname) > 50) throw new Exception("Pad name too long");
    $db->prepare('INSERT INTO padman_pad_cache (group_mapper, group_id, group_alias, pad_name, last_edited) VALUES (?,?,?,?,NOW())')
       ->execute(array('', $group["group_id"], $group["group_alias"], $padname));
    $pad = get_pad_by_id($db->lastInsertId());
    $instance->createGroupPad($group["group_id"], $group['group_alias'].'_'.$padname, '');
    if (isset($_POST['start_sitzung'])) {
      update_pad($pad["id"], array("shortlink" => 'si'.date('md'), "tags" => ' '.date("Y").' '));
      $instance->setPublicStatus(ep_pad_id($pad), true);
      set_pad_password($pad, $passwd);

      $starttext = file_get_contents('template-sitzung.txt');
      $starttext = str_replace("{{heute}}", date("d.m.Y"), $starttext);
      $starttext = "Kurzlink zum Pad: ".SHORTLNK_PREFIX.'si'.date('md')."\nPasswort: $passwd\n\n" . $starttext;

      $instance->setText(ep_pad_id($pad), $starttext);
    }

    setcookie("infobox", "<div class='alert alert-success'><button type='button' class='close' onclick='location=location.href'><span aria-hidden='true'>&times;</span><span class='sr-only'>Close</span></button>
      <h4><i class='glyphicon glyphicon-ok-circle'></i> Pad ".$padname." erfolgreich angelegt!</h4>".
      '<p><a href="'.SELF_URL.'?group='.$group["group_alias"].'&show='.$padname.'" class="btn btn-success btn-lg">Jetzt öffnen</a></p>
      </div>');
  } catch (Exception $e) {
    setcookie("infobox","<div class='alert alert-danger'><button type='button' class='close' onclick='location=location.href'><span aria-hidden='true'>&times;</span><span class='sr-only'>Close</span></button>
      <h4><i class='glyphicon glyphicon-warning-sign'></i> Neues Pad konnte nicht erstellt werden.</h4>
      <p>".$e->getMessage()."</p></div>\n");

  }
  header("HTTP/1.1 303 See other");
  header("Location: ".SELF_URL."?group=".$group["group_alias"]);
}
