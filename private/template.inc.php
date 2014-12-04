<?php if (!defined("SELF_URL")) die("Please don't call directly..."); ?>
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
		 <form class="panel-header-form form-inline" action="<?=SELF_URL?>?group=<?=$group?>" method="POST">
		<div class="form-group-sm">
		     <input type="text" class="form-control" placeholder="neues Pad in <?=$group?>" name="pad_name">
		   <button type="submit" class="btn btn-sm <?= ($group == "sitzung" ? "btn-default" : "btn-success") ?>" name="createPadinGroup">Pad erstellen</button>
		</div>
		 </form>
<?php if ($group == "sitzung"): ?>
		<form class="panel-header-form form-inline" action="<?=SELF_URL?>?group=<?=$group?>" method="POST">
		<input type="hidden" name="start_sitzung" value="true">
		<button type="submit" class="btn btn-sm btn-success" name="createPadinGroup" id="createSitzungPad">Sitzung starten</button>
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
        
        <div class="form-group">
          <p>Shortlink</p>
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
        <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
        <button type="button" class="btn btn-default pull-left" id="delete_pad"><i class="glyphicon glyphicon-trash"></i> Löschen</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" id="modal_sitzungconfirm">
  <div class="modal-body">
    Es ist nicht Mittwoch. Wirklich ein Sitzungspad erstellen?
  </div>
  <div class="modal-footer">
    <button type="button" data-dismiss="modal" class="btn btn-primary" id="confirm">Ja, wirklich erstellen</button>
    <button type="button" data-dismiss="modal" class="btn">Abbrechen</button>
  </div>
</div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://code.jquery.com/jquery.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>

<script>
    var self_url = <?= json_encode(SELF_URL) ?>, group = <?= json_encode($group) ?>;

    $(document).on('click', ".open_popup", function(e) {
      window.open($(e.target).closest("a").attr('href'), "", "width=auto,height=auto,toolbar=no,status=no,resizable=yes");
      return false;
    });
      
    var currentEditPadID;
    $(document).on('click', ".pad_opts", function(e) {
      //alert(1)
      var $dlg = $("#modal_options");
      var $line = $(e.target).closest("[data-padID]");
      currentEditPadID = $line.attr("data-padID");
      var is_public = $line.attr("data-public");
      
      setAccessPublicToggle(is_public == "true");
      if (is_public == "true")
        $("#pad_shortlink").val($line.attr("data-shortlnk"));
      else
        $("#pad_shortlink").val("(nur für öffentliche Pads verfügbar)");
      $("#pad_passw").val($line.attr("data-passw"));
      
      var shortName = currentEditPadID.substr(currentEditPadID.indexOf("$")+1);
      $dlg.find(".modal-title").html("Einstellungen zum Pad <b><u>"+shortName+"</u></b>");
      $dlg.find("#delete_dlg p").text("Pad "+shortName+" wirklich endgültig löschen?");
      
      $("#delete_dlg").hide();
      $dlg.modal("show");
    });
    
    function setAccessPublicToggle(value) {
      $("#access_private").toggleClass("btn-primary", !value);
      $("#access_private").toggleClass("btn-default", value);
      
      $("#access_public").toggleClass("btn-primary", value);
      $("#access_public").toggleClass("btn-default", !value);
    }
    
    $("#passw_store").click(function() {
    	if (!$("#pad_passw").val()) {
    	  var random = ("00000"+Math.floor(Math.random()*100000));
    	  random = random.substr(random.length-5);
        $("#pad_passw").val(random);
      }
      savePassword();
    });
    
    $("#passw_clear").click(function() {
      $("#pad_passw").val("");
      savePassword();
    })
    
    function savePassword() {
      $.post(self_url, { "pad_id" : currentEditPadID, "set_passw" : $("#pad_passw").val() },
      function(data) {
        loadPadList();
      }, "json");
    }
    
    $("#access_public").click(function() {
      setAccessPublicToggle(true);
      setPadPublic("true");
    });
    
    $("#access_private").click(function() {
      setAccessPublicToggle(false);
      setPadPublic("false");
    });
    
    function setPadPublic(value) {
      $.post(self_url, { "pad_id" : currentEditPadID, "set_public" : value },
      function(data) {
        if (value=="true")
          $("#pad_shortlink").val(data.shortlnk);
        else
          $("#pad_shortlink").val("(nur für öffentliche Pads verfügbar)");
        loadPadList();
      }, "json");
    }
    
    $("#delete_pad").click(function() {
      $("#delete_dlg").slideDown();$("#delete_password").val("");
    })
    $("#delete_yes").click(function() {
      $.post(self_url, { "pad_id" : currentEditPadID, "delete_this_pad" : $("#delete_password").val() },
      function(data) {
        loadPadList();
        $("#modal_options").modal("hide");
      }, "json");
    })
    
    
    function loadPadList() {
      $("#pad_list").html("<div class='loader'></div>");
      $.get(self_url + "?group=" + group + "&list_pads=1", function(result) {
        $("#pad_list").html(result);
      }, "html");
    }

    loadPadList();

    $("#createSitzungPad").click(function() {
      var $form = $(this).closest('form'); 
      e.preventDefault();
      $('#modal_sitzungsconfirm').modal({ backdrop: 'static', keyboard: false })
          .one('click', '#confirm', function() {
              $form.trigger('submit');
          });
      });
      return false;
    });

</script>



  </body>
</html>
