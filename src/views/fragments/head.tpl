<!DOCTYPE html>
<html lang="<?php echo $language; ?>">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($title) ? $title : 'Home'; ?></title>
  <link rel="shortcut icon" href="<?php echo asset('favicon.ico', 'images'); ?>" type="image/x-icon">
  <?php echo css('stylesheet.min.css'); ?>
  <script type="text/javascript">
    var scripts = [];

    function addScript(url, cb) {
      if (typeof cb == 'undefined') var cb = null;
      scripts.push({
        type: 'url',
        value: url,
        cb: cb
      });
    }

    function addFnScript(fn, cb) {
      if (typeof cb == 'undefined') var cb = null;

      scripts.push({
        type: 'fn',
        value: fn,
        cb: cb
      });
    }
  </script>
</head>

<?php
$bodyClass = [''];
if (!empty($class)) {
  $bodyClass[] = $class;
}
?>

<body class="<?php echo implode(' ', $bodyClass); ?>">