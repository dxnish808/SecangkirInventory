<?php
  ob_start();
  require_once('includes/load.php');
  $session->logout();
  ob_end_clean();
  redirect("index.php");
?>
