
<h3><a href="<?php echo SELF_URL; ?>" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> Zurück</a> Benutzereinstellungen</h3>

<form action="<?php echo SELF_URL; ?>?do=user_config" method="post" class="form-horizontal">

<div class="form-group">
<label for="tAlias" class="control-label col-sm-2">Alias:</label> <div class="col-sm-10"><input type="text" id="tAlias" name="alias" value="<?php echo htmlspecialchars($userinfo["alias"]); ?>" class="form-control">
</div></div>
<p><input type="submit" name="set_config" value="Speichern" class="btn btn-primary"></p>

</form>

