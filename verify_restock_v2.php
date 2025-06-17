<?php
require_once('includes/load.php');
page_require_level(3);

if (isset($_POST['verify_restock'])) {
    $restock_id = (int)$_POST['restock_id'];
    $user_id = $current_user['id'];
    $action = $_POST['action']; // 'first_verify', 'second_verify', or 'reject'
    
    if ($action === 'reject') {
        $reason = remove_junk($db->escape($_POST['rejection_reason']));
        if (empty($reason)) {
            $session->msg('d', "Rejection reason is required.");
            redirect('restock.php');
        }
        
        $result = reject_restock_verification($restock_id, $user_id, $reason);
        
        if ($result['success']) {
            $session->msg('s', $result['message']);
        } else {
            $session->msg('d', $result['message']);
        }
        redirect('restock.php');
    }
    
    if ($action === 'first_verify') {
        $result = perform_first_verification($restock_id, $user_id);
        
        if ($result['success']) {
            $session->msg('s', $result['message']);
            if (isset($result['requires_dual']) && $result['requires_dual']) {
                // High-value/bulk order requires dual verification
                $session->msg('i', "This order requires dual verification due to high value or bulk quantity.");
            }
        } else {
            $session->msg('d', $result['message']);
        }
        redirect('restock.php');
    }
    
    if ($action === 'second_verify') {
        $result = perform_second_verification($restock_id, $user_id);
        
        if ($result['success']) {
            $session->msg('s', $result['message']);
        } else {
            $session->msg('d', $result['message']);
        }
        redirect('restock.php');
    }
}

// If GET request with ID, show verification form
if (isset($_GET['id'])) {
    $restock_id = (int)$_GET['id'];
    $user_id = $current_user['id'];
    
    // Get restock details
    $restock_data = find_all_unverified_restock($restock_id);
    if (empty($restock_data)) {
        $session->msg('d', "Restock order not found or already processed.");
        redirect('restock.php');
    }
    
    $restock = $restock_data[0];
    $verification_status = get_verification_status($restock_id);
    
    // Check if user can verify this restock
    if (!can_user_verify_restock($user_id, $restock_id)) {
        $session->msg('d', "You cannot verify this restock order. Either you already verified it or it's completed.");
        redirect('restock.php');
    }
    
    // Check if dual verification is required
    $requires_dual = requires_dual_verification($restock_id);
    
    include_once('layouts/header.php');
?>

<div class="row">
    <div class="col-md-12">
        <?php echo display_msg($msg); ?>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <strong>
                    <span class="glyphicon glyphicon-check"></span>
                    <span>Secure Restock Verification</span>
                </strong>
                <div class="pull-right">
                    <a href="restock.php" class="btn btn-info btn-sm">Back to Restock List</a>
                </div>
            </div>
            <div class="panel-body">
                <!-- Restock Information -->
                <div class="well">
                    <h4>Restock Order Details</h4>
                    <table class="table table-striped">
                        <tr>
                            <td><strong>Restock ID:</strong></td>
                            <td><?php echo $restock['id']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Product:</strong></td>
                            <td><?php echo remove_junk($restock['name']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Quantity:</strong></td>
                            <td><?php echo (int)$restock['quantity']; ?> units</td>
                        </tr>
                        <tr>
                            <td><strong>Date:</strong></td>
                            <td><?php echo $restock['date']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Current Status:</strong></td>
                            <td>
                                <span class="label label-<?php echo ($restock['verification_status'] == 'pending') ? 'warning' : 'info'; ?>">
                                    <?php echo ucfirst($restock['verification_status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php if ($requires_dual): ?>
                        <tr>
                            <td><strong>Verification Required:</strong></td>
                            <td><span class="label label-danger">DUAL VERIFICATION REQUIRED</span></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>

                <!-- Verification Status -->
                <?php if ($verification_status): ?>
                <div class="well">
                    <h4>Verification History</h4>
                    <table class="table table-striped">
                        <?php if ($verification_status['verified_by_1']): ?>
                        <tr>
                            <td><strong>First Verification:</strong></td>
                            <td>
                                <?php echo $verification_status['verifier_1_name']; ?> 
                                <small class="text-muted">(<?php echo $verification_status['verification_1_date']; ?>)</small>
                            </td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($verification_status['verified_by_2']): ?>
                        <tr>
                            <td><strong>Second Verification:</strong></td>
                            <td>
                                <?php echo $verification_status['verifier_2_name']; ?> 
                                <small class="text-muted">(<?php echo $verification_status['verification_2_date']; ?>)</small>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
                <?php endif; ?>

                <!-- Verification Actions -->
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <h4>Verification Actions</h4>
                    </div>
                    <div class="panel-body">
                        <?php if ($restock['verification_status'] == 'pending'): ?>
                            <!-- First Verification -->
                            <form method="post" action="verify_restock_v2.php" class="form-inline" style="margin-bottom: 10px;">
                                <input type="hidden" name="restock_id" value="<?php echo $restock['id']; ?>">
                                <input type="hidden" name="action" value="first_verify">
                                <button type="submit" name="verify_restock" class="btn btn-success btn-lg">
                                    <span class="glyphicon glyphicon-ok"></span>
                                    Perform First Verification
                                </button>
                            </form>
                            
                        <?php elseif ($restock['verification_status'] == 'partial'): ?>
                            <!-- Second Verification -->
                            <form method="post" action="verify_restock_v2.php" class="form-inline" style="margin-bottom: 10px;">
                                <input type="hidden" name="restock_id" value="<?php echo $restock['id']; ?>">
                                <input type="hidden" name="action" value="second_verify">
                                <button type="submit" name="verify_restock" class="btn btn-primary btn-lg">
                                    <span class="glyphicon glyphicon-ok-circle"></span>
                                    Complete Second Verification
                                </button>
                            </form>
                        <?php endif; ?>

                        <!-- Rejection Form -->
                        <div class="panel panel-danger" style="margin-top: 20px;">
                            <div class="panel-heading">
                                <h5>Reject Verification</h5>
                            </div>
                            <div class="panel-body">
                                <form method="post" action="verify_restock_v2.php">
                                    <input type="hidden" name="restock_id" value="<?php echo $restock['id']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <div class="form-group">
                                        <label for="rejection_reason">Reason for Rejection:</label>
                                        <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Please provide a detailed reason for rejecting this restock verification..." required></textarea>
                                    </div>
                                    <button type="submit" name="verify_restock" class="btn btn-danger">
                                        <span class="glyphicon glyphicon-remove"></span>
                                        Reject Verification
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Information Panel -->
    <div class="col-md-4">
        <div class="panel panel-warning">
            <div class="panel-heading">
                <h4><span class="glyphicon glyphicon-shield"></span> Security Information</h4>
            </div>
            <div class="panel-body">
                <h5>Verification Rules:</h5>
                <ul class="list-unstyled">
                    <li><span class="glyphicon glyphicon-info-sign text-info"></span> Orders > $500 or 50+ units require dual verification</li>
                    <li><span class="glyphicon glyphicon-user text-warning"></span> Same user cannot perform both verifications</li>
                    <li><span class="glyphicon glyphicon-time text-muted"></span> All actions are logged and monitored</li>
                    <li><span class="glyphicon glyphicon-eye-open text-success"></span> Anomaly detection is active</li>
                </ul>
                
                <hr>
                
                <h5>Your Session:</h5>
                <ul class="list-unstyled">
                    <li><strong>User:</strong> <?php echo $current_user['name']; ?></li>
                    <li><strong>Level:</strong> <?php echo $current_user['user_level']; ?></li>
                    <li><strong>IP:</strong> <?php echo $_SERVER['REMOTE_ADDR']; ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
    include_once('layouts/footer.php');
} else {
    $session->msg('d', "Invalid request.");
    redirect('restock.php');
}
?> 