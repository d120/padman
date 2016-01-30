<?php
$group = htmlentities($group);
load_view("error_layout", array("content" => <<<CONTENT

<div class="msg alert alert-danger">
<h4>Fehler</h4>

<p> Die Gruppe '$group' existiert nicht.</p>

<p>Prüfe bitte nochmals den Link, vielleicht hast du dich ja vertippt...</p>
</div>

CONTENT
));

?>
