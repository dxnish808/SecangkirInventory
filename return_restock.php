<?php
$page_title = 'Return Restock';
require_once('includes/load.php');
// Checkin What level user has permission to view this page
page_require_level(3);

// Fetch the restock item data
$id = (int)$_GET['id'];

$restock_item = find_all_restock_product($id);
$restock_item = $restock_item[0];
$all_products = find_all('products');

if (!$restock_item) {
  $session->msg("d", "Missing restock id.");
  redirect('restock.php');
}

if (isset($_POST['return_restock'])) {
  $req_fields = array('quantity', 'reason');
  validate_fields($req_fields);

  if (empty($errors)) {
    $quantity = remove_junk($db->escape($_POST['quantity']));
    $reason = remove_junk($db->escape($_POST['reason']));

    // Insert into returns table
    $query = "INSERT INTO returns (restock_id, product_name, quantity, date, reason) VALUES ('{$id}', '{$restock_item['name']}', '{$quantity}', NOW(), '{$reason}')";

    if ($db->query($query)) {
      $session->msg('s', "Return logged.");
      redirect('restock.php', false);
    } else {
      $session->msg('d', 'Failed to log return.');
      redirect('restock.php', false);
    }
  } else {
    $session->msg("d", $errors);
    redirect('return_restock.php?id=' . $id, false);
  }
}
?>

<?php include_once('layouts/header.php'); ?>
<div class="row">
  <div class="col-md-6">
    <?php echo display_msg($msg); ?>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong>
          <span class="glyphicon glyphicon-th"></span>
          <span>Return Restock</span>
       </strong>
      </div>
      <div class="panel-body">
        <form method="post" action="return_restock.php?id=<?php echo (int)$_GET['id']; ?>">
          <div class="form-group">
            <label for="product_name">Product Name</label>
            <input type="text" class="form-control" name="product_name" value="<?php echo remove_junk($restock_item['name']); ?>" readonly>
          </div>
          <div class="form-group">
            <label for="quantity">Quantity</label>
            <input type="number" class="form-control" name="quantity" min="1" max="<?php echo (int)$restock_item['quantity']; ?>" value="<?php echo (int)$restock_item['quantity']; ?>" placeholder="Quantity">
          </div>
          <div class="form-group">
            <label for="reason">Reason</label>
            <textarea class="form-control" name="reason" placeholder="Reason for return"></textarea>
          </div>
          <button type="submit" name="return_restock" class="btn btn-primary">Submit</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include_once('layouts/footer.php'); ?>
