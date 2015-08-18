<?php
if (!$instance) exit;

  $padname = $_GET['pad_id'];
  $result = $instance->getText($padname);


?>
<link rel="stylesheet" href="https://static.luelistan.net/bootstrap-3.3.2-dist/css/bootstrap.min.css">
<link href='https://fonts.googleapis.com/css?family=Open+Sans:400,700,700italic,400italic' rel='stylesheet' type='text/css'>
<style>
@media print {
  .container { width: auto; } a::after { display: none; } a { text-decoration: underline!important; }
}
.container {text-align:justify; font-family: 'Open Sans', sans-serif; }
img {max-width:100%}
</style>
<script src="js/marked.js"></script>
<div class="container">
<script>
var pad = <?= json_encode($result) ?>;
document.write(marked(pad.text));
</script>
</div>


