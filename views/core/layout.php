<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= HEADER_TITLE ?></title>

    <script>
    var padman_data = { "activegroup" : "<?= $current_group ?>", "groups" : <?= json_encode($groups) ?> };
    var SHORTLNK_PREFIX=<?=json_encode(SHORTLNK_PREFIX)?>, SELF_URL=<?=json_encode(SELF_URL)?>;
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
<!--<div class="alert alert-warning" role="alert">Der PadMan hat eine neue interne Struktur und ein paar neue Funktionen, aber vielleicht auch ein paar neue Bugs. Bitte schau daher bei jeder Aktion nach, ob es geklappt hat. Probleme bitte bei mweller@d120.de oder <a href="https://github.com/d120/padman/issues">als Issue</a> melden. Vielen Dank. <small class="pull-right">Diese Meldung verschwindet die Tage automatisch&trade;.</small></div>
-->
<div class="container">
<?php load_view("extra_header", array()); ?>
<div class="top_colorbar" style="background-color: <?= HEADER_ACCENT_COLOR ?>;"></div>

  <img src="<?= HEADER_LOGO_URL ?>" class="top_logo">
  <h1 style="font-weight:normal; padding: 15px 0 30px;"><?= HEADER_H1 ?></h1>
<div style="height:65px">
<nav class="navbar navbar-default" role="navigation">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#grouplist-navbar">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
    </div>

    <div class="collapse navbar-collapse" id="grouplist-navbar">
      <ul class="nav navbar-nav" id="main_nav">


<?php
function listItem($item, $name) { global $current_group;
	$url = SELF_URL."?group=".urlencode($item["group_alias"]);
	if ($current_group === strtolower($name)) {
		echo "  <li data-id=\"$item[group_alias]\" class=\"active\"><a href=\"$url\">".$name."</a></li>\n";
	} else {
		echo "  <li data-id=\"$item[group_alias]\"><a href=\"$url\">".$name."</a></li>\n";
	}
}
$indent = 1;
$lastMenu = [];
foreach($groups as $d){
  $alias = explode("/", $d["menu_title"]);
  for(; $indent > count($alias) || ($indent > 1 && $lastMenu[$indent-2] != $alias[$indent-2]); $indent--) {
    echo "</ul></li>\n";
  }
  for(; $indent < count($alias); $indent++) {
    if($indent==1)echo "<li class=dropdown><a href='#' class=dropdown-toggle data-toggle=dropdown>".$alias[$indent-1]." <span class=caret></span></a><ul class=dropdown-menu>\n";
    else echo "<li class='dropdown-submenu dropdown-header'><b>".$alias[$indent-1]."</b><ul class='dropdown-menu'>";
  }
  listItem($d, $alias[count($alias)-1]);
  $lastMenu = $alias;
}
for(; $indent > 1; $indent--) {
  echo "</ul></li>\n";
}

?>
    <li><a href="<?= ADD_GROUP_LINK ?>"><i class="glyphicon glyphicon-plus"></i></a></li>
      </ul>
      <ul class="nav navbar-nav navbar-right">
      	<?php load_view("login_info", $login); ?>
      </ul>
      <?php if (ALLOW_SEARCH): ?>
      <form class="navbar-form navbar-right" role="search" action="<?= SELF_URL?>" method="get">
        <div class="form-group">
          <input type="text" class="form-control" placeholder="Search" name="q" style="width: 150px;" id="searchBox" autocomplete="off">
        </div>
      </form>
      <?php endif; ?>
    </div><!-- /.navbar-collapse -->
</nav>
</div>

<div id="quickSearch" style="display:none"></div>

<?= isset($infoBox) ? $infoBox : '' ?>

<div class="panel panel-default">
  <div class="panel-heading">
	<span id="taglist">...</span>
		 <form class="panel-header-form form-inline group_form" action="javascript:invalid" method="POST">

<?php if ($allow_pad_create): ?>
		<div class="form-group-sm">
		     <input type="text" class="form-control create_pad_name" placeholder="" name="pad_name">
		   <button type="submit" class="btn btn-sm btn-success" name="createPadinGroup">Pad erstellen</button>
		</div>
		 </form>

		<form class="panel-header-form form-inline group_form" action="javascript:invalid" method="POST" id="createSitzungPadForm" style="display:none">
		<input type="hidden" name="start_sitzung" value="true">
    <input type="hidden" name="createPadinGroup" value="1" />
		<button type="submit" class="btn btn-sm btn-info" id="createSitzungPad"><i class="glyphicon glyphicon-leaf"></i> Sitzung starten</button>
		</form>

<?php endif; ?>
</div>

   <ul class="list-group" id="pad_list">
     Eile mit Weile ...
   </ul>
</div>

</div>

<?php load_view("modal_options"); ?>

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
    <script src="js/padmanager.js?version=2"></script>


    <script>
    var padMan = new PadManager();
    padMan.loadGroup(<?= json_encode($current_group["group_alias"]) ?>);
    </script>

<div class="footer container">
Etherpad-Lite Manager by Max Weller et al  &middot;  <a href="https://github.com/d120/padman">This program is free software</a>
&middot; <a href="https://github.com/d120/padman/issues">Issues</a>
<br><br> &middot;
<br><br> &middot;
<br><br> &middot;
<br><br> &middot;

</div>

  </body>
</html>
