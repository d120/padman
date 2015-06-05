<?php

// JSON api
if (isset($_POST['set_public']) && isset($_POST['pad_id'])) {
  $padname = $_POST['pad_id'];
  $public = $_POST['set_public'] == 'true';
  $ok=$instance->setPublicStatus($padname, $public);
  $sl = null;
  if ($public) {
    if (isset($_POST['shortlnk'])) $sl = preg_replace('/[^a-z0-9]/','',$_POST['shortlnk']);
    if (!$sl) $sl = substr(md5($padname),0,7);
  }
  storeJson('shortlnk', $padname, $sl);
  die(json_encode(array("status"=>"ok","shortlnk"=>SHORTLNK_PREFIX.$sl)));
}
if (isset($_POST['set_passw']) && isset($_POST['pad_id'])) {
  $padname = $_POST['pad_id'];
  $ok=setPassword($padname, $_POST['set_passw']);
  die(json_encode(array("status"=>"ok")));
}
if (isset($_POST['delete_this_pad']) && isset($_POST['pad_id'])) {
  if (defined('DELETE_PASSWORD') && strlen(DELETE_PASSWORD) > 0 && DELETE_PASSWORD != $_POST['delete_this_pad'])
      die(json_encode(array("status"=>"access_denied")));
  $padname = $_POST['pad_id'];
  $ok=$instance->deletePad($padname);
  die(json_encode(array("status"=>"ok")));
}

if (isset($_POST['rename']) && isset($_POST['pad_id'])) {
  $padname = $_POST['pad_id'];
  try {
    $ok=$instance->movePad($padname, $_POST['rename']);
  } catch(Exception $ex) {
    die(json_encode(array("status"=>"error", "msg"=>"$ex")));
  }

  moveJson('passwords', $padname, $_POST['rename']);
  moveJson('shortlnk', $padname, $_POST['rename']);
  
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
    if (isset($_POST['start_sitzung'])) {
      storeJson('shortlnk', $groupmap[$group] . '$' . $padname, 'si'.date('md'));
      $instance->setPublicStatus($groupmap[$group] . '$' . $padname, true);
      setPassword($groupmap[$group] . '$' . $padname, $passwd);
      
      $starttext = file_get_contents('template-sitzung.txt');
      $starttext = str_replace("{{heute}}", date("d.m.Y"), $starttext);
      $starttext = "Kurzlink zum Pad: ".SHORTLNK_PREFIX.'si'.date('md')."\nPasswort: $passwd\n\n" . $starttext;
      // $starttext = nl2br($starttext);
      $instance->setText($groupmap[$group] . '$' . $padname, $starttext);
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
