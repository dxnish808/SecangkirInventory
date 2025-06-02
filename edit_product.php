<?php
  $page_title = 'Edit product';
  require_once('includes/load.php');
  // Checkin What level user has permission to view this page
  page_require_level(1);

  // Validate product ID from GET
  $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  if ($product_id <= 0) {
    error_log("Invalid product id: " . $_GET['id']);
    $session->msg("d","Invalid product id.");
    redirect('product.php');
  }
  $product = find_by_id('products', $product_id);
  $all_categories = find_all('categories');
  $all_photo = find_all('media');
  if(!$product){
    $session->msg("d","Missing product id.");
    redirect('product.php');
  }
?>
<?php
 if(isset($_POST['product'])){
    $req_fields = array('product-title','product-categorie','product-quantity','buying-price');
    validate_fields($req_fields);

    // Validate numeric fields
    $p_cat = isset($_POST['product-categorie']) ? (int)$_POST['product-categorie'] : 0;
    $p_qty = isset($_POST['product-quantity']) ? (int)$_POST['product-quantity'] : 0;
    $p_buy = isset($_POST['buying-price']) ? (float)$_POST['buying-price'] : 0;

    if($p_cat <= 0 || $p_qty < 0 || $p_buy < 0){
      error_log("Invalid input on edit_product: cat=$p_cat, qty=$p_qty, buy=$p_buy");
      $session->msg("d", "Invalid input values.");
      redirect('edit_product.php?id='.$product_id, false);
    }

   if(empty($errors)){
       $p_name  = remove_junk($db->escape($_POST['product-title']));
       $query   = "UPDATE products SET";
       $query  .=" name ='{$p_name}', quantity ='{$p_qty}',";
       $query  .=" buy_price ='{$p_buy}',categorie_id ='{$p_cat}' ";
       $query  .=" WHERE id ='{$product['id']}'";
       $result = $db->query($query);
               if($result && $db->affected_rows() === 1){
                 $session->msg('s',"Product updated ");
                 redirect('product.php', false);
               } else {
                 $session->msg('d',' Sorry failed to updated!');
                 redirect('edit_product.php?id='.$product['id'], false);
               }
   } else{
       $session->msg("d", $errors);
       redirect('edit_product.php?id='.$product['id'], false);
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
      <div class="panel panel-default">
        <div class="panel-heading">
          <strong>
            <span class="glyphicon glyphicon-th"></span>
            <span>Edit Product</span>
         </strong>
        </div>
        <div class="panel-body">
         <div class="col-md-7">
           <form method="post" action="edit_product.php?id=<?php echo (int)$product['id'] ?>">
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon">
                   <i class="glyphicon glyphicon-th-large"></i>
                  </span>
                  <input type="text" class="form-control" name="product-title" value="<?php echo remove_junk($product['name']);?>">
               </div>
              </div>
              <div class="form-group">
                <div class="row">
                  <div class="col-md-6">
                    <select class="form-control" name="product-categorie">
                    <option value=""> Select a categorie</option>
                   <?php  foreach ($all_categories as $cat): ?>
                     <option value="<?php echo (int)$cat['id']; ?>" <?php if($product['categorie_id'] === $cat['id']): echo "selected"; endif; ?> >
                       <?php echo remove_junk($cat['name']); ?></option>
                   <?php endforeach; ?>
                 </select>
                  </div>
                </div>
              </div>

              <div class="form-group">
               <div class="row">
                 <div class="col-md-4">
                  <div class="form-group">
                    <label for="qty">Quantity</label>
                    <div class="input-group">
                      <span class="input-group-addon">
                       <i class="glyphicon glyphicon-shopping-cart"></i>
                      </span>
                      <input type="number" class="form-control" name="product-quantity" value="<?php echo remove_junk($product['quantity']); ?>">
                   </div>
                  </div>
                 </div>
                 <div class="col-md-4">
                  <div class="form-group">
                    <label for="qty">Buying price</label>
                    <div class="input-group">
                      <span class="input-group-addon">
                        <i class="glyphicon glyphicon-usd"></i>
                      </span>
                      <input type="number" class="form-control" name="buying-price" value="<?php echo remove_junk($product['buy_price']);?>">
                      <span class="input-group-addon">.00</span>
                   </div>
                  </div>
                 </div>
               </div>
              </div>
              <button type="submit" name="product" class="btn btn-danger">Update</button>
          </form>
         </div>
        </div>
      </div>
  </div>

<?php include_once('layouts/footer.php'); ?>
