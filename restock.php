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
              <th class="text-center" style="width: 150px;"> Verification Status </th>
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
                <?php 
                // Check if new verification system is available
                if (isset($restock_item['verification_status'])) {
                    // New verification system
                    $status = $restock_item['verification_status'] ?? 'pending';
                    $status_class = '';
                    $status_text = '';
                    
                    switch($status) {
                        case 'pending':
                            $status_class = 'label-warning';
                            $status_text = 'Pending';
                            break;
                        case 'partial':
                            $status_class = 'label-info';
                            $status_text = 'Awaiting 2nd Verification';
                            break;
                        case 'completed':
                            $status_class = 'label-success';
                            $status_text = 'Completed';
                            break;
                        case 'rejected':
                            $status_class = 'label-danger';
                            $status_text = 'Rejected';
                            break;
                        default:
                            $status_class = 'label-default';
                            $status_text = 'Unknown';
                    }
                } else {
                    // Old verification system
                    if ($restock_item['status'] == 0) {
                        $status_class = 'label-warning';
                        $status_text = 'Pending';
                        $status = 'pending';
                    } else {
                        $status_class = 'label-success';
                        $status_text = 'Verified';
                        $status = 'completed';
                    }
                }
                ?>
                <span class="label <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                
                <?php if (isset($restock_item['verifier_1_name']) && $restock_item['verifier_1_name']): ?>
                    <br><small class="text-muted">1st: <?php echo $restock_item['verifier_1_name']; ?></small>
                <?php endif; ?>
                
                <?php if (isset($restock_item['verifier_2_name']) && $restock_item['verifier_2_name']): ?>
                    <br><small class="text-muted">2nd: <?php echo $restock_item['verifier_2_name']; ?></small>
                <?php endif; ?>
                
                <?php if (function_exists('requires_dual_verification') && requires_dual_verification($restock_item['id'])): ?>
                    <br><span class="label label-danger">Dual Required</span>
                <?php endif; ?>
             </td>
             <td class="text-center">
                <div class="btn-group">
                   <a href="edit_restock.php?id=<?php echo (int)$restock_item['id']; ?>" class="btn btn-warning btn-xs" title="Edit" data-toggle="tooltip">
                                         <?php 
                     $can_edit = isset($restock_item['verification_status']) ? 
                                ($restock_item['verification_status'] != 'completed') : 
                                ($restock_item['status'] == 0);
                     ?>
                     <?php if($can_edit){ ?>
                     <span class="glyphicon glyphicon-edit"></span>
                    <?php }else{ ?>
                      <span class="glyphicon glyphicon-eye-open"></span>
                    <?php }?>
                   </a>
                    <?php 
                    $can_return = isset($restock_item['verification_status']) ? 
                                 ($restock_item['verification_status'] == 'pending') : 
                                 ($restock_item['status'] == 0);
                    ?>
                    <?php if($can_return){ ?>
                    <a href="return_restock.php?id=<?php echo (int)$restock_item['id']; ?>" class="btn btn-success btn-xs" title="Return" data-toggle="tooltip">
                      <span class="glyphicon glyphicon-repeat"></span>
                    </a>
                    <?php } ?>
                </div>
             </td>
        <td class="text-center">
          <?php 
          $can_verify = false;
          $is_completed = false;
          $is_rejected = false;
          
          if (isset($restock_item['verification_status'])) {
              // New verification system
              $can_verify = ($restock_item['verification_status'] == 'pending' || $restock_item['verification_status'] == 'partial');
              $is_completed = ($restock_item['verification_status'] == 'completed');
              $is_rejected = ($restock_item['verification_status'] == 'rejected');
          } else {
              // Old verification system
              $can_verify = ($restock_item['status'] == 0);
              $is_completed = ($restock_item['status'] == 1);
          }
          ?>
          
          <?php if($can_verify){ ?>
          <?php if (isset($restock_item['verification_status'])): ?>
            <a href="verify_restock_v2.php?id=<?php echo (int)$restock_item['id']; ?>" class="btn btn-primary btn-sm" title="Secure Verify" data-toggle="tooltip">
                       <span class="glyphicon glyphicon-shield"></span> Verify
                     </a>
          <?php else: ?>
            <a href="verify_restock.php?id=<?php echo (int)$restock_item['id']; ?>&pid=<?php echo (int)$restock_item['product_id']; ?>" class="btn btn-primary btn-lg btn-lg-custom" title="Verify" data-toggle="tooltip">
                       <span class="glyphicon glyphicon-check"></span>
                     </a>
          <?php endif; ?>
          <?php }elseif($is_completed){ ?>
            <span class="label label-success">Verified</span>
          <?php }elseif($is_rejected){ ?>
            <span class="label label-danger">Rejected</span>
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