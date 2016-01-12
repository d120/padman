
<a href="<?php echo SELF_URL; ?>" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> Zurück</a>

<h3>Benutzereinstellungen</h3>

<form action="javascript:" id="userConfig" class="form-horizontal">

  <div class="form-group">
    <label for="tAlias" class="control-label col-sm-2">Alias:</label>
    <div class="col-sm-4">
      <input type="text" id="tAlias" name="alias" class="form-control"
             value="<?php echo htmlspecialchars($userinfo["alias"]); ?>">
    </div>
  </div>


  <div class="form-group">
    <label class="control-label col-sm-2">Author ID:</label>
    <div class="col-sm-4">
      <input type="text" readonly class="form-control"
             value="<?php echo htmlspecialchars($authorID); ?>">
    </div>
  </div>


  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
      <input type="submit" name="set_config" value="Speichern" class="btn btn-primary">
    </div>
  </div>


</form>

<h3>Bearbeitete Pads</h3>
<table class="table">
<?php foreach($edited_pads as $pad): ?>
<tr><td><?= $pad[0] ?></td><td><a href="<?php echo SELF_URL; ?>?group=<?=$pad[0]?>&show=<?=$pad[1]?>"><?= $pad[1] ?></a></td></tr>
<?php endforeach; ?>
</table>



<h3>Sessions</h3>
<table class="table">
  <?php foreach($sessions as $session): if($session->validUntil < time() && !isset($_GET["all_sessions"]))continue; ?>
<tr><td><?= $session->groupID ?></td><td><?= $session->authorID ?></td><td><?= date("r",$session->validUntil)?>  <?= $session->validUntil?></tr>
<?php endforeach; ?>
</table>




<script src="js/jquery.js"></script>

<script>
$("#userConfig").submit(function() {
  $.post(location.href, { set_config: "1", alias: $("#userConfig [name=alias]").val() }, function(e) {
    $("<p>Daten wurden gespeichert</p>").prependTo("#userConfig");
  });
});
</script>

