<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);
header('Content-Type: text/html; charset=utf-8');

include "../config.inc.php";
include "etherpad-lite-client.php";

$infoBox = "";

if ($_SERVER["HTTP_HOST"] != HOST_NAME) {
  header("Location: https://".HOST_NAME.$_SERVER["REQUEST_URI"]);
  exit;
}

if (isset($_SERVER["REDIRECT_STATUS"]) && $_SERVER["REDIRECT_STATUS"] == "404") {
	$url = $_SERVER["REDIRECT_SCRIPT_URL"];
	if (preg_match('#^/pad/(.*)$#', $url, $res) && array_search($res[1], $shown_groups) !== FALSE) {
    header("HTTP/1.1 200 OK");
		$_GET["group"] = $res[1];
	} elseif (preg_match('#^/pad/p/(Sitzung.*)$#', $url, $res)) {
		$padID = $res[1];
		header("Location: ".SELF_URL."?group=sitzung&show=$padID");
	} elseif (preg_match('#^/pad/p/(.*)$#', $url, $res)) {
		$padID = $res[1];
    header("Location: ".SELF_URL."?group=fachschaft&show=$padID");
    exit;
	} elseif (preg_match('#^/pad/(.*)/(.*)$#', $url, $res) && array_search($res[1], $shown_groups) !== FALSE) {
		header("HTTP/1.1 200 OK");
    $_GET["group"] = $res[1];
    $_GET["show"] = $res[2];
	} else {
		header("HTTP/1.1 404 Not Found");
		echo "<h3>File not found</h3>";
		exit;
	}
}

function storeJson($filename, $key, $value) {
	$p=@json_decode(file_get_contents('../data/'.$filename.'.json'),true);
  if (!is_array($p)) $p=array();
  $p[$key] = $value;
  file_put_contents('../data/'.$filename.'.json', json_encode($p));
}

function readJson($filename, $key) {
	$p=@json_decode(file_get_contents('../data/'.$filename.'.json'),true);
  if (!is_array($p)) $p=array();
  return isset($p[$key]) ? $p[$key] : '';
}

function setPassword($padID, $passwd) {
  global $instance;
	$ok=$instance->setPassword($padID, $passwd);
	storeJson('passwords', $padID, $passwd);
  return $ok;
}

$padurl = PAD_URL;

$group = "fachschaft";

if (isset($_GET['group'])) {
	$group = $_GET['group'];
}

$instance = new EtherpadLiteClient(API_KEY, API_URL);

$author_name = $_SERVER['PHP_AUTH_USER']; //$_SERVER['HTTP_AUTH_USER'];
//$author_cn = $_SERVER['HTTP_AUTH_CN'];
$author_cn= $author_name;
//$groups = "sitzung; fachschaft; inforz; ophase"; //base64_decode($_SERVER['HTTP_AUTH_GROUPS']);
//$author_groups = preg_split("/[\s;]+/", $groups);
$author_groups = $shown_groups;

$author_groups = array_intersect($author_groups, $shown_groups);
if (!in_array($group, $author_groups)) {
    $group = $author_groups[0];
}

$author = $instance->createAuthorIfNotExistsFor($author_name, $author_cn);
$authorID = $author->authorID;

$validUntil = mktime(0, 0, 0, date("m"), date("d")+1, date("y")); // One day in the future

$groupmap = array();
$sessions = array();

foreach ($author_groups as $group_name) {
	$mapGroup = $instance->createGroupIfNotExistsFor($group_name);
	$groupmap[$group_name] = $mapGroup->groupID;
	$sessions[] = $instance->createSession($mapGroup->groupID, $authorID, $validUntil)->sessionID;
}


setcookie('sessionID', implode(",",$sessions), $validUntil, '/', HOST_NAME);


if (isset($_GET['show'])) {
	$padname = htmlspecialchars($_GET['show']);
	//header("Location: ".$padurl.$padname); #+$padurl+$padname);
  $passw = readJson('passwords', $groupmap[$group].'$'.$padname);
  if ($passw) $passw = "<br><b>Passwort: $passw</b>";
  $shortlnk = readJson('shortlnk', $groupmap[$group].'$'.$padname);
  if ($shortlnk) $shortlnk = "<br><b>Kurz-Link: <a href='".SHORTLNK_PREFIX."$shortlnk'>".SHORTLNK_PREFIX."$shortlnk</a></b>";
	echo "<style> 
  html,body {margin:0;padding:0;} 
  iframe { width: 100%; height: 100%; border: 0; } 
  #info b {font-size:150%;}  
  #info {position:absolute;bottom:0;left:50%;margin-left:-210px;width:400px;padding:5px 10px;
    border:1px solid #393;background:#afa;font:status-bar;}
  </style>
  <div id='info' ondblclick='if(this.style.height==\"0px\")this.style.height=\"inherit\";else this.style.height=\"0px\";'>
  (Doppelklick zum ein/ausblenden)<br>
  Pad: ".$padurl.$groupmap[$group].'$'.$padname."$passw$shortlnk</div>";
	echo '<iframe src="'.$padurl.$groupmap[$group].'$'.$padname.'"></iframe>';
	exit;
}

// JSON API
if (isset($_POST['set_public']) && isset($_POST['pad_id'])) {
	$padname = $_POST['pad_id'];
  $public = $_POST['set_public'] == 'true';
	$ok=$instance->setPublicStatus($padname, $public);
  $sl=$public ? substr(md5($padname),0,7) : null;
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
if (isset($_GET['list_pads'])) {
  $pads = $instance->listPads($groupmap[$group]);
  $pad_lastedited = Array();
  foreach ($pads->padIDs as $padID) {
  	$tmp = $instance->getLastEdited($padID);
  	$pad_lastedited[$padID] = (int)$tmp->lastEdited/1000;
  }

  asort($pad_lastedited);
  $pad_lastedited = array_reverse($pad_lastedited);
  echo '<div class="table-responsive"><table class="table">';
  echo '<thead><tr><th width=30></th><th>Name</th><th width=350>Passwort</th></tr></thead><tbody>';
  foreach ($pad_lastedited as $padID => $last_edited) {
  	$tmp = $instance->getPublicStatus($padID);

  	$shortname = substr($padID,strpos($padID, "$")+1);
  	$icon_html = ""; $className = "";
  	if ($tmp->publicStatus) {
  		$icon_html = '<span class="glyphicon glyphicon-globe"></span> '; $public="true"; $className="";
  	} else{
  		$icon_html = '<span class="glyphicon glyphicon-home"></span> '; $public="false";
  	}
    $passw = readJson('passwords', $padID);
    $shortlnk = readJson('shortlnk', $padID);
    if ($shortlnk) $shortlnk = SHORTLNK_PREFIX.$shortlnk;
    
    echo '<tr class="'.$className.'" data-padID="'.$padID.'" data-public="'.$public.'" data-passw="'.$passw.'" data-shortlnk="'.$shortlnk.'"> 
  	  <td class="icon"><button type="button" class="btn btn-link btn-xs pad_opts">
  	    '.$icon_html.'
  	  </button></td>
      <td class="name"><a href="'.SELF_URL.'?group='.$group.'&show='.$shortname.'">'.$shortname.'</a></td><td>';
    if ($passw) echo ' <code>'.$passw.'</code>';
    echo ' <span class="pull-right"> ';
    if ($public=="true") echo '<span class="label label-success ">Öffentlich</span> ';
    echo '<span class="label label-default ">'.date("d.m.y H:i",$last_edited).'</span> ';
    echo '</span></td></tr>';
  }
  echo "</tbody></table></div>";
  die();
}

if (isset($_POST['createPadinGroup'])) {
	
	if (isset($_POST['start_sitzung'])) {
		$padname = 'Sitzung' . date('Ymd');
		$passwd = mt_rand(10000, 99999);
		$starttext = file_get_contents('template-sitzung.txt');
    $starttext = str_replace("{{heute}}", date("r"), $starttext);
		$starttext = "Kurzlink zum Pad: ".SHORTLNK_PREFIX.'si'.date('md')."\nPasswort: $passwd\n\n" . $starttext;
	} else {
    $padname = $_POST['pad_name'];
		$starttext = "Willkommen im wesentlichen Etherpad auf D120.de!\r\n\r\n";
	}

	try {
		$instance->createGroupPad($groupmap[$group], $padname, $starttext);
		if ($_POST['start_sitzung']) {
      storeJson('shortlnk', $groupmap[$group] . '$' . $padname, 'si'.date('md'));
			$instance->setPublicStatus($groupmap[$group] . '$' . $padname, true);
			setPassword($groupmap[$group] . '$' . $padname, $passwd);
			
		}
		$infoBox .= "<div class='alert alert-success'>Pad ".$padname." in Gruppe ".$group.' erstellt. <a href="'.SELF_URL.'?group='.$group.'&show='.$padname.'">Direkt zum Pad</a><br><br><h3>Passwort: '.$passwd.'</h3></div>';
	} catch (Exception $e) {
		$infoBox .= "<div class='alert alert-danger'>Fehler beim Erstellen des Pads: ".$e->getMessage()."</a></div>\n";

	}

}

include "template.inc.php";

?>