<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>pad manager</title>

    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/pads.css" rel="stylesheet">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

<div class="container">

<img src="https://www.fachschaft.informatik.tu-darmstadt.de/d120de/images/das-wesen-der-informatik.png" style="position:absolute; right: 30px; top: 5px; z-index: 10; ">
	<h1 style="font-weight:normal; padding: 15px 0 30px;"><b>Etherpad</b> der Fachschaft Informatik</h1>
<nav class="navbar navbar-default" role="navigation">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#grouplist-navbar">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#">Gruppen</a>
    </div>

    <div class="collapse navbar-collapse" id="grouplist-navbar">
      <ul class="nav navbar-nav">


<?php

foreach ($groupmap as $name => $id) {
	if ($group === $name) {
		echo "<li class=\"active\"><a href=\"".SELF_URL.$name."\">".$name."</a></li>";
	}
	else {
		echo "<li><a href=\"".SELF_URL."".$name."\">".$name."</a></li>";
	}

}
?>
     </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>

<?= $infoBox ?>


<div class="panel panel-default">
  <div class="panel-heading">Vorhandene Pads für Gruppe: <b><code><?=$group?></code></b>
		 <form class="pull-right form-inline" style="margin-top:-5px" action="<?=SELF_URL?>?group=<?=$group?>" method="POST">
		<div class="form-group-sm">
		     <input type="text" class="form-control" placeholder="neues Pad in <?=$group?>" name="pad_name">
		   <button type="submit" class="btn btn-sm <?= ($group == "sitzung" ? "btn-default" : "btn-success") ?>" name="createPadinGroup">Pad erstellen</button>
		</div>
		 </form>
<?php if ($group == "sitzung"): ?>
		<form class="pull-right form-inline" style="margin-top:-5px; padding-right: 20px;" action="<?=SELF_URL?>?group=<?=$group?>" method="POST">
		<input type="hidden" name="start_sitzung" value="true">
		<button type="submit" class="btn btn-sm btn-success" name="createPadinGroup">Sitzung starten</button>
		</form>
<?php endif; ?>

</div>

   <ul class="list-group">

<?php


$pads = $instance->listPads($groupmap[$group]);
$pad_lastedited = Array();
foreach ($pads->padIDs as $padID) {
	$tmp = $instance->getLastEdited($padID);
	$pad_lastedited[$padID] = (int)$tmp->lastEdited/1000;
}

asort($pad_lastedited);
$pad_lastedited = array_reverse($pad_lastedited);

foreach ($pad_lastedited as $padID => $last_edited) {
	$tmp = $instance->getPublicStatus($padID);

	$shortname = substr($padID,strpos($padID, "$")+1);
	$icon_html = "";
	if ($tmp->publicStatus) {
		$icon_html = '<span class="glyphicon glyphicon-globe"></span> ';
	}
	else{
		$icon_html = '<span class="glyphicon glyphicon-home"></span> ';
	}
	echo '<li class="list-group-item" data-padID="'.$padID.'"> 
		<!-- Single button -->
		<div class="btn-group">
		  <button type="button" class="btn btn-link dropdown-toggle btn-xs" data-toggle="dropdown">
		    '.$icon_html.'<span class="caret"></span>
		  </button>
		  <ul class="dropdown-menu" role="menu">
		    <li><a href="'. SELF_URL .'?group='.$group.'&public_pad='.$padID.'">Öffentlich machen</a></li>
		    <li><a href="'. SELF_URL .'?group='.$group.'&nonpublic_pad='.$padID.'">Nicht-öffentlich machen</a></li>
		    <li><a href="#" class="setpassw">Passwort setzen</a></li>
		    <li><a href="'. SELF_URL .'?group='.$group.'&setpassw_pad='.$padID.'&passw=">Passwort aus</a></li>
			<li><a href="#" class="delpad">Löschen</a></li>
		  </ul>
		</div>
        <a href="'.SELF_URL.'?redirect='.$padID.'">'.$shortname.'</a>
		<span class="badge">'.date("d.m.y H:i",$last_edited).'</span></li>';

}

?>
   </ul>
</div>

</div>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://code.jquery.com/jquery.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>

<script>
$(".setpassw").click(function() {
	var padID=$(this).closest("[data-padID]").attr("data-padID");
	var random = ("00000"+Math.floor(Math.random()*100000));
	random = random.substr(random.length-5);
	var passw = prompt("Setze Passwort für Pad "+padID, random);
	if (passw) location = "<?= SELF_URL?>?group=<?=$group?>&setpassw_pad=" + padID + "&passw=" + passw;
});
</script>



  </body>
</html>
