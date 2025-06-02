<?php
  $page_title = 'Edit Restock';
  require_once('includes/load.php');
  page_require_level(1);

  // Validate restock ID
  $restock_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  if ($restock_id <= 0) {
    error_log("Invalid restock id: " . $_GET['id']);
    $session->msg("d","Invalid restock id.");
    redirect('restock.php');
  }
  $restock = find_all_restock_product($restock_id);
  $restock = $restock[0];
  $all_products = find_all('products');

  if(!$restock){
    $session->msg("d","Missing restock id.");
    redirect('restock.php');
  }
?>
<?php
  if(isset($_POST['update_restock'])){
    $req_fields = array('product_id','quantity', 'date' );
    validate_fields($req_fields);

    // Validate numeric fields
    $pid = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
    $date = isset($_POST['date']) ? $_POST['date'] : '';

    if($pid <= 0 || $quantity < 0 || empty($date)){
      error_log("Invalid input on edit_restock: pid=$pid, qty=$quantity, date=$date");
      $session->msg("d", "Invalid input values.");
      redirect('edit_restock.php?id='.$restock_id, false);
    }

    if(empty($errors)){
      $pid  = $db->escape($pid);
      $quantity     = $db->escape($quantity);
      $date         = $db->escape($date);

      $sql  = "UPDATE restock SET";
      $sql .= " product_id='{$pid}', quantity='{$quantity}', date='{$date}'";
      $sql .= " WHERE id ='{$restock['id']}'";
      $result = $db->query($sql);
      if($result && $db->affected_rows() === 1){
        $session->msg('s',"Restock updated.");
        redirect('edit_restock.php?id='.$restock['id'], false);
      } else {
        $session->msg('d',' No data updated!');
        redirect('edit_restock.php?id='.$restock['id'], false);
      }
    } else {
      $session->msg("d", $errors);
      redirect('edit_restock.php?id='.(int)$restock['id'],false);
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

  <div class="col-md-12">
  <div class="panel panel-default">
    <div class="panel-heading clearfix">
      <strong>
        <span class="glyphicon glyphicon-th"></span>
        <?php if($restock['status'] == 0){ ?>
        <span>Edit Restock</span>
        <?php }else{ ?>
              View Restock
        <?php } ?>
     </strong>
     <div class="pull-right">
       <a href="restock.php" class="btn btn-info btn-sm">Show all restocks</a>
     </div>
    </div>
    <div class="panel-body">
       <table class="table table-bordered">
         <thead>
          <th> Product Name </th>
          <th> Quantity </th>
          <th> Date </th>
           <?php if($restock['status'] == 0){ ?>
          <th> Action </th>
           <?php } ?>
         </thead>
           <tbody  id="product_info">
              <tr>
              <form method="post" action="edit_restock.php?id=<?php echo $restock['id']; ?>">
                <td id="r_name">
                  <select class="form-control" name="product_id" required>
                    <option value="">Select Product</option>
                    <?php foreach ($all_products as $pro): ?>
                      <option value="<?php echo (int)$pro['id'] ?>" <?php echo ($pro['id'] == $restock['product_id']) ? 'selected' : '' ?>>
                        <?php echo $pro['name'] ?></option>
                    <?php endforeach; ?>
                  </select>
                </td>
             
                <td id="r_quantity">
                  <input type="number" class="form-control" name="quantity" value="<?php echo (int)$restock['quantity']; ?>">
                </td>
        
                <td id="r_date">
                  <input type="date" class="form-control datepicker" name="date" value="<?php echo remove_junk($restock['date']); ?>">
                </td>
                 <?php if($restock['status'] == 0){ ?>
                <td>
                  <div class="btn-group">
                    <form method="post" action="delete_restock.php?id=<?php echo (int)$restock['id']; ?>" style="display: inline;">
                      <button type="submit" name="delete_restock" class="btn btn-danger btn-xs" title="Delete">
                        <i class="glyphicon glyphicon-trash"></i>
                      </button>
                    </form>
                    <button type="submit" name="update_restock" class="btn btn-primary btn-xs" title="Update">
                      <i class="glyphicon glyphicon-check"></i>
                    </button>
                  </div>
                </td>
                 <?php } ?>
              </form>
              </tr>
           </tbody>
       </table>
    </div>
  </div>
  </div>

</div>

<?php include_once('layouts/footer.php'); ?>
