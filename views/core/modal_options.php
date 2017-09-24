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
        
          <input type="text" id="pad_passw" class="form-control input-lgxx" style="width:200px;" pattern="[A-Za-z0-9_.-]*"> 
            <button class="btn btn-success btn-lgxx" id="passw_store" title="Passwort übernehmen"><i class="glyphicon glyphicon-ok"></i></button>
            <button class="btn btn-default btn-lgxx" id="passw_clear" title="Passwortschutz ausschalten">aus</button>
        </div>
        
        <div class="form-group  ">
          <p>Schlagworte</p>
        
          <input type="text" id="pad_tags" class="form-control" style="">
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
