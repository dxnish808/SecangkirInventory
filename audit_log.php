<?php
$page_title = 'Audit Log';
require_once('includes/load.php');
page_require_level(1);

// Get audit log entries
$audit_logs = [];
$limit = 50;

// Check if restock_audit_log table exists
$table_check = $db->query("SHOW TABLES LIKE 'restock_audit_log'");

if ($table_check && $db->num_rows($table_check) > 0) {
    $sql = "SELECT al.*, u.name as user_name, r.id as restock_id 
            FROM restock_audit_log al 
            LEFT JOIN users u ON al.user_id = u.id 
            LEFT JOIN restock r ON al.restock_id = r.id 
            ORDER BY al.timestamp DESC 
            LIMIT {$limit}";
    $audit_logs = find_by_sql($sql);
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
                    <span class="glyphicon glyphicon-list-alt"></span>
                    <span>Restock Verification Audit Log</span>
                </strong>
                <div class="pull-right">
                    <a href="security_alerts.php" class="btn btn-warning btn-sm">Security Alerts</a>
                </div>
            </div>
            <div class="panel-body">
                <?php if (empty($audit_logs)): ?>
                    <?php 
                    // Check if table exists to show appropriate message
                    $table_check = $db->query("SHOW TABLES LIKE 'restock_audit_log'");
                    if (!$table_check || $db->num_rows($table_check) == 0):
                    ?>
                        <div class="alert alert-info">
                            <span class="glyphicon glyphicon-info-sign"></span>
                            <strong>Audit logging not yet enabled.</strong><br>
                            To enable comprehensive audit logging, please run the database upgrade script: 
                            <code>DATABASE FILE/add_security_columns.sql</code><br>
                            This will add audit trail functionality to track all verification activities.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success">
                            <span class="glyphicon glyphicon-ok-circle"></span>
                            No audit log entries found. All verification activities will be logged here.
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Restock ID</th>
                            <th>IP Address</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($audit_logs as $log): ?>
                        <tr>
                            <td><?php echo $log['timestamp']; ?></td>
                            <td><?php echo remove_junk($log['user_name']); ?></td>
                            <td>
                                <span class="label label-info"><?php echo $log['action']; ?></span>
                            </td>
                            <td>
                                <?php if ($log['restock_id']): ?>
                                    #<?php echo $log['restock_id']; ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><code><?php echo $log['ip_address']; ?></code></td>
                            <td>
                                <?php if ($log['old_values'] || $log['new_values']): ?>
                                    <button class="btn btn-xs btn-info" data-toggle="collapse" data-target="#details-<?php echo $log['id']; ?>">
                                        View Details
                                    </button>
                                    <div id="details-<?php echo $log['id']; ?>" class="collapse">
                                        <hr>
                                        <?php if ($log['old_values']): ?>
                                            <strong>Before:</strong> <code><?php echo $log['old_values']; ?></code><br>
                                        <?php endif; ?>
                                        <?php if ($log['new_values']): ?>
                                            <strong>After:</strong> <code><?php echo $log['new_values']; ?></code>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
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