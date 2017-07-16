<?php
  require_once('utils.php');
  $utils = new Utils('modified');
  print_r(json_encode($utils->bookData));
?>