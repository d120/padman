<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);
header('Content-Type: text/html; charset=utf-8');

include "config.inc.php";
include "etherpad-lite-client.php";

$infoBox = "";

if (isset($_SERVER["REDIRECT_STATUS"]) && $_SERVER["REDIRECT_STATUS"] == "404") {
	$url = $_SERVER["REDIRECT_SCRIPT_URL"];
	if (preg_match('#^/pad/(.*)$#', $url, $res) && array_search($res[1], $shown_groups) !== FALSE) {
		$_GET["group"] = $res[1];
	} elseif (preg_match('#^/pad/p/(Sitzung.*)$#', $url, $res)) {
		$padID = $res[1];
		$_GET["redirect"] = 'g.UcY0Rd8jOCxWgyBK$' . $padID;
	} elseif (preg_match('#^/pad/p/(.*)$#', $url, $res)) {
		$padID = $res[1];
		$_GET["redirect"] = 'g.UzYcuI1Dd9d3FOLJ$' . $padID;
	} else {
		header("HTTP/1.1 404 Not Found");
		echo "<h3>File not found</h3>";
		exit;
	}
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


if (isset($_GET['redirect'])) {
	$padname = urlencode($_GET['redirect']);
	//header("Location: ".$padurl.$padname); #+$padurl+$padname);
	echo "<style> html,body {margin:0;padding:0;} iframe { width: 100%; height: 100%; border: 0; } </style>\n";
	echo '<iframe src="'.$padurl.$padname.'"></iframe>';
	exit;
}

if (isset($_GET['makepublic'])) {
	$padname = urldecode($_GET['makepublic']);
	//echo "Padname: ". $padname;
	$instance->setPublicStatus($padname, True);
}
if (isset($_GET['public_pad'])) {
	$padname = urldecode($_GET['public_pad']);
	//echo "Group: $group Padname: ". $padname;
	$instance->setPublicStatus($padname, True);
}

if (isset($_GET['nonpublic_pad'])) {
	$padname = urldecode($_GET['nonpublic_pad']);
	//echo "Padname: ". $padname;
	$instance->setPublicStatus($padname, False);
}

if (isset($_GET['setpassw_pad'])) {
	$padname = urldecode($_GET['setpassw_pad']);
	//echo "Padname: ". $padname;
	$instance->setPassword($padname, $_GET['passw']);
}


if (isset($_POST['createPadinGroup'])) {
	
	if ($_POST['start_sitzung']) {
		$padname = 'xxxSitzung' . date('Ymd');
		$passwd = mt_rand(10000, 99999);
		$starttext = file_get_contents('template-sitzung.txt');
		$starttext = "Kurzlink zum Pad: http://d120.de/p/$padname\nPasswort: $passwd\n\n" . $starttext;
	} else {
    $padname = $_POST['pad_name'];
		$starttext = "Willkommen im wesentlichen Etherpad auf D120.de!\r\n\r\n";
	}

	try {
		$instance->createGroupPad($groupmap[$group], $padname, $starttext);
		if ($_POST['start_sitzung']) {
			$instance->setPublicStatus($groupmap[$group] . '$' . $padname, true);
			$instance->setPassword($groupmap[$group] . '$' . $padname, $passwd);
			file_put_contents('./passwords/' . $groupmap[$group] . '$' . $padname . '.txt', $passwd);
		}
		$infoBox .= "<div class='alert alert-success'>Pad ".$padname." in Gruppe ".$group.' erstellt. <a href="'.SELF_URL.'?redirect='.$groupmap[$group].'$'.$padname.'">Direkt zum Pad</a><br><br><h3>Passwort: '.$passwd.'</h3></div>\n';
	} catch (Exception $e) {
		$infoBox .= "<div class='alert alert-danger'>Fehler beim Erstellen des Pads: ".$e->getMessage()."</a></div>\n";

	}

}

include "template.inc.php";

?>