<?php
  $page_title = 'Add Restock';
  require_once('includes/load.php');
  // Check what level user has permission to view this page
  page_require_level(2);
  $all_categories = find_all('categories');
  $all_products = find_all('products');
?>
<?php
 if(isset($_POST['add_stock'])){
   $req_fields = array('product-id', 'product-quantity', 'date');
   validate_fields($req_fields);
   if(empty($errors)){

     $p_id     = remove_junk($db->escape($_POST['product-id']));
     $p_qty      = remove_junk($db->escape($_POST['product-quantity']));
     $date       = remove_junk($db->escape($_POST['date']));
     $query  = "INSERT INTO restock (";
     $query .=" product_id, quantity, date";
     $query .=") VALUES (";
     $query .=" '{$p_id}', '{$p_qty}', '{$date}'";
     $query .=")";
     if($db->query($query)){
       $session->msg('s',"Restock entry added ");
       redirect('restock.php', false); // Redirect to restock.php after success
     } else {
       $session->msg('d',' Sorry failed to add!');
       redirect('add_stock.php', false);
     }
   } else{
     $session->msg("d", $errors);
     redirect('add_stock.php',false);
   }
 }
?>
<?php include_once('layouts/header.php'); ?>
<div class="row">
  <div class="col-md-12">
    <?php echo display_msg($msg); ?>
  </div>
</div>
<div class="row">
  <div class="col-md-8">
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong>
          <span class="glyphicon glyphicon-th"></span>
          <span>Add</span>
        </strong>
      </div>
      <div class="panel-body">
        <div class="col-md-12">
          <form method="post" action="add_stock.php" class="clearfix">
           
            <div class="form-group">
              <div class="row">
                <div class="col-md-12">
                  <select class="form-control" name="product-id" required>
                    <option value="">Select Product</option>
                    <?php foreach ($all_products as $pro): ?>
                      <option value="<?php echo (int)$pro['id'] ?>">
                        <?php echo $pro['name'] ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="row">
                <div class="col-md-4">
                  <div class="input-group">
                    <span class="input-group-addon">
                      <i class="glyphicon glyphicon-shopping-cart"></i>
                    </span>
                    <input type="number" class="form-control" name="product-quantity" placeholder="Product Quantity">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="input-group">
                    <span class="input-group-addon">
                      <i class="glyphicon glyphicon-calendar"></i>
                    </span>
                    <input type="date" class="form-control" name="date" placeholder="Date">
                  </div>
                </div>
              </div>
            </div>
            <button type="submit" name="add_stock" class="btn btn-info">Add Order</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include_once('layouts/footer.php'); ?>
