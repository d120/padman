<?php
    echo '
    <tr class="'.$className.'" data-padID="'.$id.'" data-public="'.$public.'" data-passw="'.$passw.'" data-shortlnk="'.$shortlnk.'"> 
      <td class="pad_icon icon"><!--button type="button" class="btn btn-link btn-xs"-->
        '.$icon_html.'
      <!--/button--></td>
      <td class="name"><a href="'.SELF_URL.'?group='.$group.'&show='.$shortname.'">'.$shortname.'</a></td><td>';
    if ($passw) echo ' <code>'.$passw.'</code>';
    echo ' <span class="pull-right"> ';
    if ($public=="true") echo '<span class="label label-success ">Öffentlich</span> ';
    echo '<span class="label label-default ">'.date("d.m.y H:i",$last_edited).'</span> ';
    echo '</span></td><td><button class="btn btn-xs btn-default pad_opts" title="Einstellungen"><i class="glyphicon glyphicon-cog"></i></button>
    	 <button class="btn btn-xs btn-default pad_rename" title="Umbenennen"><i class="glyphicon glyphicon-pencil"></i></button>
      <a href="'.SELF_URL.'?group='.$group.'&show='.$shortname.'" target="_blank" class="btn btn-xs btn-default open_popup" title="In neuem Fenster öffnen"><i class="glyphicon glyphicon-new-window"></i></a>
      </td></tr>';
?>