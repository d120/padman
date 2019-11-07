<div class="modal fade" id="modal_export">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title">Export</h4>
      </div>
      <div class="modal-body">
<?php
  if ($shortlnk)
    echo "  <a href='mailto:?subject=Etherpad&body=Hallo,%0a%0ahier der Link zum Pad:%0a".SHORTLNK_PREFIX."$shortnam%0aPasswort:%20$password%0a%0aViele%20Grüße,%0a%0a' title='Share' class='btn btn-primary btn-block'>
      <span class='glyphicon glyphicon-envelope'></span> Link per Mail senden</a>";
  echo "<a class='btn btn-success btn-block' href='?pad_id=$padID&do=export_wiki' onclick='return export_popup(this.href);' title='Wiki Export'><span class='glyphicon glyphicon-export'></span> Wiki-Export </a>";
  echo "<a class='btn btn-info btn-block' href='?pad_id=$padID&do=export_mdhtml' onclick='return export_popup(this.href);' title='Markdown Export'><span class='glyphicon glyphicon-eye-open'></span> Markdown-Vorschau</a>";
  echo "<a class='btn btn-info btn-block' href='javascript:' onclick='return call_shell_cmd(\"bookstack_export\");' title='Markdown Export'><span class='glyphicon glyphicon-eye-open'></span> Bookstack-Export</a>";
?>
</div>

    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal " id="modal_output">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title">Output</h4>
      </div>
			<div class="modal-body" style="">
				<div id="terminal" style="height:400px"></div>
			</div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<link rel=stylesheet href="js/xterm.css">
