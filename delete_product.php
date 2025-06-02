<?php
  require_once('includes/load.php');
  // Checkin What level user has permission to view this page
  page_require_level(1);

  // Validate product ID
  $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  if($product_id <= 0){
    error_log("Invalid delete_product id: " . $_GET['id']);
    $session->msg("d","Invalid Product id.");
    redirect('product.php');
  }
  $product = find_by_id('products', $product_id);
  if(!$product){
    $session->msg("d","Missing Product id.");
    redirect('product.php');
  }

  $delete_id = delete_by_id('products', $product_id);
  if($delete_id){
      $session->msg("s","Products deleted.");
      redirect('product.php');
  } else {
      $session->msg("d","Products deletion failed.");
      redirect('product.php');
  }
?>
