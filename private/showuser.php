<?php

if (isset($_POST["set_config"])) {
  if (!$userinfo) sql("INSERT INTO padman_user SET user = ?", [ $author_cn ]);
  sql("UPDATE padman_user SET alias = ? WHERE user = ?", [ $_POST["alias"], $author_cn ]);
  echo "<div class='alert alert-success'>Einstellungen gespeichert</div>";
}

load_view("error_layout", [
  "content" => get_view("user", [ "userinfo" => $userinfo ])
]);


