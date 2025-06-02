<?php
  $page_title = 'Restock';
  require_once('includes/load.php');
  // Checkin What level user has permission to view this page
   page_require_level(3);
?>
<?php
$restock = find_all_unverified_restock();
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
      <div class="panel-heading clearfix">
        <strong>
          <span class="glyphicon glyphicon-th"></span>
          <span>Restock Verification</span>
        </strong>
      </div>
      <div class="panel-body">
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th class="text-center" style="width: 50px;">#</th>
              <th> Product Information </th>
              <th class="text-center" style="width: 15%;"> Quantity</th>
              <th class="text-center" style="width: 15%;"> Date </th>
              <th class="text-center" style="width: 100px;"> Actions </th>
              <th class="text-center" style="width: 100px;"> Verify </th>
           </tr>
          </thead>
         <tbody>
           <?php foreach ($restock as $restock_item): ?>
           <tr>
             <td class="text-center"><?php echo count_id(); ?></td>
             <td><?php echo remove_junk($restock_item['name']); ?></td>
             <td class="text-center"><?php echo (int)$restock_item['quantity']; ?></td>
             <td class="text-center"><?php echo $restock_item['date']; ?></td>
             <td class="text-center">
                <div class="btn-group">
   
                   <a href="edit_restock.php?id=<?php echo (int)$restock_item['id']; ?>" class="btn btn-warning btn-xs" title="Edit" data-toggle="tooltip">
                    <?php if($restock_item['status'] == 0){ ?>
                     <span class="glyphicon glyphicon-edit"></span>
                    <?php }else{ ?>
                      <span class="glyphicon glyphicon-eye-open"></span>
                    <?php }?>
                   </a>
                    <?php if($restock_item['status'] == 0){ ?>
                    <a href="return_restock.php?id=<?php echo (int)$restock_item['id']; ?>" class="btn btn-success btn-xs" title="Return" data-toggle="tooltip">
                      <span class="glyphicon glyphicon-repeat"></span>
                    </a>
                    <?php } ?>
                </div>
             </td>
             </td>
        <td class="text-center"> <!-- New column for the 'Return' button -->
          <?php if($restock_item['status'] == 0){ ?>
          <a href="verify_restock.php?id=<?php echo (int)$restock_item['id']; ?>&pid=<?php echo (int)$restock_item['product_id']; ?>" class="btn btn-primary btn-lg btn-lg-custom" title="Verify" data-toggle="tooltip">
                     <span class="glyphicon glyphicon-check"></span>
                   </a>
          <?php }else{ ?>

            -

          <?php }  ?>

        </td>
           </tr>
           <?php endforeach; ?>
         </tbody>
       </table>
      </div>
    </div>
  </div>
</div>
<?php include_once('layouts/footer.php'); ?>