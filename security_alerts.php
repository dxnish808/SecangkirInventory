<?php
$page_title = 'Security Alerts Dashboard';
require_once('includes/load.php');
page_require_level(1);

$pending_alerts = get_pending_security_alerts(20);

if (isset($_POST['resolve_alert'])) {
    $alert_id = (int)$_POST['alert_id'];
    $resolution = remove_junk($db->escape($_POST['resolution']));
    $user_id = $current_user['id'];
    
    $sql = "UPDATE security_alerts SET 
            status = '{$resolution}', 
            resolved_at = NOW(), 
            resolved_by = '{$user_id}' 
            WHERE id = '{$alert_id}'";
    
    if ($db->query($sql)) {
        $session->msg('s', "Alert has been marked as {$resolution}.");
    } else {
        $session->msg('d', "Failed to update alert status.");
    }
    redirect('security_alerts.php', false);
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
                    <span class="glyphicon glyphicon-shield"></span>
                    <span>Security Alerts Dashboard</span>
                </strong>
            </div>
            <div class="panel-body">
                <?php if (empty($pending_alerts)): ?>
                    <?php 
                    // Check if table exists to show appropriate message
                    $table_check = $db->query("SHOW TABLES LIKE 'security_alerts'");
                    if (!$table_check || $db->num_rows($table_check) == 0):
                    ?>
                        <div class="alert alert-info">
                            <span class="glyphicon glyphicon-info-sign"></span>
                            <strong>Security monitoring not yet enabled.</strong><br>
                            To enable real-time security alerts and anomaly detection, please run the database upgrade script: 
                            <code>DATABASE FILE/add_security_columns.sql</code><br>
                            This will add comprehensive security monitoring to detect suspicious verification patterns.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success">
                            <span class="glyphicon glyphicon-ok-circle"></span>
                            No pending security alerts. System is secure.
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Severity</th>
                            <th>Alert Type</th>
                            <th>Description</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_alerts as $alert): ?>
                        <tr>
                            <td>
                                <span class="label label-<?php echo ($alert['severity'] == 'high') ? 'danger' : (($alert['severity'] == 'medium') ? 'warning' : 'info'); ?>">
                                    <?php echo strtoupper($alert['severity']); ?>
                                </span>
                            </td>
                            <td><?php echo remove_junk($alert['alert_type']); ?></td>
                            <td><?php echo remove_junk($alert['description']); ?></td>
                            <td><?php echo $alert['created_at']; ?></td>
                            <td>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="alert_id" value="<?php echo $alert['id']; ?>">
                                    <input type="hidden" name="resolution" value="resolved">
                                    <button type="submit" name="resolve_alert" class="btn btn-success btn-xs">Resolve</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?> 