<?php if (!defined("SELF_URL")) die("Please don't call directly..."); ?>
<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>pad manager</title>

    <script>
    var padman_data = { "activegroup" : "<?= $group ?>", "groups" : <?= json_encode($groupmap) ?> };
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
<div class="top_colorbar"></div>

<img src="https://www.fachschaft.informatik.tu-darmstadt.de/d120de/images/das-wesen-der-informatik.png" class="top_logo">
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

//foreach ($groupmap as $name => $id) {
foreach($shown_groups_titles as $name) {
	$url = SELF_URL.strtolower($name);
	if ($group === strtolower($name)) {
		echo "<li class=\"active\"><a href=\"$url\">".$name."</a></li>";
	}
	else {
		echo "<li data-id=\"".strtolower($name)."\"><a href=\"$url\">".$name."</a></li>";
	}

}
?>
        <li><a href="#"><i class="glyphicon glyphicon-plus"></i></a></li>
     </ul>
      <ul class="nav navbar-nav navbar-right">
        <li style="font-size:8.5pt; color:#999;"><br>Benutzer: <?= $author_cn ?><br>Alias: <?= $author_name ?></li>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>

<?= $infoBox ?>


<div class="panel panel-default">
  <div class="panel-heading">Vorhandene Pads für Gruppe: <b><code><?=$group?></code></b>
		 <form class="panel-header-form form-inline" action="<?=SELF_URL?>?group=<?=$group?>" method="POST">
		<div class="form-group-sm">
		     <input type="text" class="form-control" placeholder="neues Pad in <?=$group?>" name="pad_name">
		   <button type="submit" class="btn btn-sm <?= ($group == "sitzung" ? "btn-default" : "btn-success") ?>" name="createPadinGroup">Pad erstellen</button>
		</div>
		 </form>
<?php if ($group == "sitzung"): ?>
		<form class="panel-header-form form-inline" action="<?=SELF_URL?>?group=<?=$group?>" method="POST" id="createSitzungPadForm">
		<input type="hidden" name="start_sitzung" value="true">
    <input type="hidden" name="createPadinGroup" value="1" />
		<button type="submit" class="btn btn-sm btn-success" id="createSitzungPad"><i class="glyphicon glyphicon-leaf"></i> Sitzung starten</button>
		</form>
<?php endif; ?>

</div>

   <ul class="list-group" id="pad_list">
     Eile mit Weile ...
   </ul>
</div>

</div>

<div class="modal fade" id="modal_options">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title">Pad options</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <p>Zugangsbeschränkung</p>
          <p><button class="btn btn-primary" id="access_private"><i class="glyphicon glyphicon-lock"></i> nur mit FS-Account</button>
            <button class="btn btn-default" id="access_public"><i class="glyphicon glyphicon-globe"></i> öffentlich</button>
          </p>
        </div>
        
        <div class="form-group" id="group_shortlink">
          <p>Shortlink <a href='#' id='edit_shortlink' class='btn btn-xs pull-right btn-default'>bearbeiten</a></p>
          <p><input type="text" class="form-control" readonly id="pad_shortlink" value="(nur für öffentliche Pads verfügbar)"></p>
        </div>
        
        <div class="form-group form-inline ">
          <p>Passwort</p>
        
          <input type="text" id="pad_passw" class="form-control input-lgxx" style="width:200px;"> 
            <button class="btn btn-success btn-lgxx" id="passw_store" title="Passwort übernehmen"><i class="glyphicon glyphicon-ok"></i></button>
            <button class="btn btn-default btn-lgxx" id="passw_clear" title="Passwortschutz ausschalten">aus</button>
        </div>
        
        
        <div class="form-group form-inline " id="delete_dlg" style="display: none">
          <p>Pad löschen</p>
        
          <input type="text" placeholder="Löschpasswort eingeben" id="delete_password" class="form-control input-lgxx" style="width:200px;"> 
            <button class="btn btn-danger" id="delete_yes">ja, wirklich löschen</button>
        </div>
        
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal">Schließen</button>
        <button type="button" class="btn btn-default pull-left" id="delete_pad"><i class="glyphicon glyphicon-trash"></i> Löschen</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

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
      <div class="modal-body">
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
    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
    <script src="js/padmanager.js"></script>


    <script>
    var self_url = <?= json_encode(SELF_URL) ?>, group = <?= json_encode($group) ?>;
    var padMan = new PadManager(self_url, group);
    padMan.loadPadList();
    </script>


  </body>
</html>
