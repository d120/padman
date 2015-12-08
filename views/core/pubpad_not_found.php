<?php load_view("error_layout", array("content" => <<<CONTENT

<h2>Fehler</h2>

<p>Das Pad <?=htmlentities($pad)?> konnte nicht geladen werden.</p>

<p>Prüfe bitte nochmals den Link, vielleicht hast du dich ja vertippt...</p>

<p>Ansonsten kann es auch sein, dass das Pad umbenannt, in eine andere Gruppe verschoben oder gelöscht wurde.</p>

CONTENT
)); ?>



