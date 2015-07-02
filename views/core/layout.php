<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>pad manager</title>

    <script>
    var padman_data = { "activegroup" : "<?= $current_group ?>", "groups" : <?= json_encode($groupmap) ?> };
    </script>
    
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
<div class="top_colorbar" style="background-color: <?= HEADER_ACCENT_COLOR ?>;"></div>

  <img src="<?= HEADER_LOGO_URL ?>" class="top_logo">
  <h1 style="font-weight:normal; padding: 15px 0 30px;"><?= HEADER_H1 ?></h1>

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

//foreach ($groupmap as $name => $id) {
$i = 0;
foreach($group_titles as $name){
	if ($i++ == 8) {
		echo "<li class='dropdown'><a href='#' class='dropdown-toggle' data-toggle='dropdown' style='font-size:140%;font-weight:bold;padding-top:9px;'>&hellip;</a><ul class='dropdown-menu'>";
	}
	$url = SELF_URL.strtolower($name);
	if ($current_group === strtolower($name)) {
		echo "<li class=\"active\"><a href=\"$url\">".$name."</a></li>";
	}
	else {
		echo "<li data-id=\"".strtolower($name)."\"><a href=\"$url\">".$name."</a></li>";
	}

}
if ($i > 8) {
	echo "</ul></li>";
}
?>
        <li><a href="<?= ADD_GROUP_LINK ?>"><i class="glyphicon glyphicon-plus"></i></a></li>
     </ul>
      <ul class="nav navbar-nav navbar-right">
      	<?php load_view("login_info", $login); ?>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>

<?= $infoBox ?>


<div class="panel panel-default">
  <div class="panel-heading">Vorhandene Pads für Gruppe: <b><code><?=$current_group?></code></b>
		 <form class="panel-header-form form-inline" action="<?=SELF_URL?>?group=<?=$current_group?>" method="POST">

<?php if ($allow_pad_create): ?>
		<div class="form-group-sm">
		     <input type="text" class="form-control" placeholder="neues Pad in <?=$current_group?>" name="pad_name">
		   <button type="submit" class="btn btn-sm <?= ($current_group == "sitzung" ? "btn-default" : "btn-success") ?>" name="createPadinGroup">Pad erstellen</button>
		</div>
		 </form>
<?php if ($current_group == "sitzung"): ?>
		<form class="panel-header-form form-inline" action="<?=SELF_URL?>?group=<?=$current_group?>" method="POST" id="createSitzungPadForm">
		<input type="hidden" name="start_sitzung" value="true">
    <input type="hidden" name="createPadinGroup" value="1" />
		<button type="submit" class="btn btn-sm btn-success" id="createSitzungPad"><i class="glyphicon glyphicon-leaf"></i> Sitzung starten</button>
		</form>
<?php endif; ?>
<?php endif; ?>
</div>

   <ul class="list-group" id="pad_list">
     Eile mit Weile ...
   </ul>
</div>

</div>

<?php include "template-modal-options.html"; ?>

<div class="modal fade" id="modal_sitzungconfirm">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title"><i class="glyphicon glyphicon-question-sign"></i> Sitzungspad</h4>
      </div>
      <div class="modal-body">
        Es ist nicht Mittwoch. Möchtest du wirklich ein Sitzungspad erstellen?
      </div>
      <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-primary" id="confirm_createsitzungpad">Erstellen</button>
        <button type="button" data-dismiss="modal" class="btn btn-default">Abbrechen</button>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="modal_rename">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title"><i class="glyphicon glyphicon-question-sign"></i> Pad umbenennen</h4>
      </div>
      <div class="modal-body pleasewait">
	<p>Eile mit Weile...</p>
	<div class="progress">
	  <div class="progress-bar progress-bar-striped active" role="progressbar" style="width: 45%">
	  </div>
	</div>
      </div>
      <div class="modal-body rename">
        Gib einen neuen Namen für das Pad ein:<br>
	<input type="text" id="rename_pad" class="form-control"><br><br>
	Falls du das Pad verschieben möchtest, wähle die neue Kategorie aus:<br>
	<select id="rename_group" class="form-control"></select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="confirm_rename">Umbenennen</button>
        <button type="button" data-dismiss="modal" class="btn btn-default">Abbrechen</button>
      </div>
    </div>
  </div>
</div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="js/jquery.js"></script>
    <script src="js/jquery-ui.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
    <script src="js/padmanager.js"></script>


    <script>
    var self_url = <?= json_encode(SELF_URL) ?>, group = <?= json_encode($current_group) ?>;
    var padMan = new PadManager(self_url, group);
    padMan.loadPadList();
    </script>

<div class="footer container">
Etherpad-Lite Manager by Max Weller et al  &middot;  <a href="https://github.com/d120/padman">This program is free software</a>
&middot; <a href="https://github.com/d120/padman/issues">Issues</a>
</div>

  </body>
</html>
