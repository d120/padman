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
    <style>.msg { max-width: 600px; margin: 10px auto; }</style>
    <script src="js/jquery.js"></script>
  </head>
  <body>
    <div class="container">
      <div class="top_colorbar" style="background-color: <?= HEADER_ACCENT_COLOR ?>;"></div>

      <img src="<?= HEADER_LOGO_URL ?>" class="top_logo">
      <h1 style="font-weight:normal; padding: 15px 0 30px;"><?= HEADER_H1 ?></h1>
<?php echo $content; ?>

    </div>
  </body>
</html>


