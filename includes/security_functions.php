<?php
/*--------------------------------------------------------------*/
/* Security Functions for Restock Verification System
/* Implements dual verification, audit trail, and anomaly detection
/*--------------------------------------------------------------*/

/*--------------------------------------------------------------*/
/* Log audit trail for restock actions
/*--------------------------------------------------------------*/
function log_restock_audit($restock_id, $user_id, $action, $old_values = null, $new_values = null) {
    global $db;
    
    // Check if restock_audit_log table exists
    $table_check = $db->query("SHOW TABLES LIKE 'restock_audit_log'");
    
    if (!$table_check || $db->num_rows($table_check) == 0) {
        // Table doesn't exist, skip logging
        return true;
    }
    
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $old_values_json = $old_values ? json_encode($old_values) : null;
    $new_values_json = $new_values ? json_encode($new_values) : null;
    
    $sql = "INSERT INTO restock_audit_log (restock_id, user_id, action, old_values, new_values, ip_address, user_agent) 
            VALUES ('{$restock_id}', '{$user_id}', '{$action}', '{$old_values_json}', '{$new_values_json}', '{$ip_address}', '{$user_agent}')";
    
    return $db->query($sql);
}

/*--------------------------------------------------------------*/
/* Log verification attempt for anomaly detection
/*--------------------------------------------------------------*/
function log_verification_attempt($user_id, $restock_id, $attempt_type, $success = false) {
    global $db;
    
    // Check if verification_attempts table exists
    $table_check = $db->query("SHOW TABLES LIKE 'verification_attempts'");
    
    if (!$table_check || $db->num_rows($table_check) == 0) {
        // Table doesn't exist, skip logging
        return true;
    }
    
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $success_int = $success ? 1 : 0;
    
    $sql = "INSERT INTO verification_attempts (user_id, restock_id, attempt_type, ip_address, success) 
            VALUES ('{$user_id}', '{$restock_id}', '{$attempt_type}', '{$ip_address}', '{$success_int}')";
    
    return $db->query($sql);
}

/*--------------------------------------------------------------*/
/* Check if user can perform verification (prevent same user dual verification)
/*--------------------------------------------------------------*/
function can_user_verify_restock($user_id, $restock_id) {
    global $db;
    
    $sql = "SELECT verified_by_1, verified_by_2, verification_status FROM restock WHERE id = '{$restock_id}'";
    $result = $db->query($sql);
    
    if ($result && $db->num_rows($result) > 0) {
        $restock = $db->fetch_assoc($result);
        
        // If user already performed first verification, they cannot do second
        if ($restock['verified_by_1'] == $user_id) {
            return false;
        }
        
        // If already completed, no more verification needed
        if ($restock['verification_status'] == 'completed') {
            return false;
        }
        
        return true;
    }
    
    return false;
}

/*--------------------------------------------------------------*/
/* Check for anomalies in verification patterns
/*--------------------------------------------------------------*/
function detect_verification_anomalies($user_id, $restock_id) {
    global $db;
    $alerts = [];
    
    // Check if verification_attempts table exists
    $table_check = $db->query("SHOW TABLES LIKE 'verification_attempts'");
    
    if ($table_check && $db->num_rows($table_check) > 0) {
        // Check for rapid verification attempts (more than 5 in 60 minutes)
        $sql = "SELECT COUNT(*) as attempt_count FROM verification_attempts 
                WHERE user_id = '{$user_id}' 
                AND timestamp >= DATE_SUB(NOW(), INTERVAL 60 MINUTE)";
        $result = $db->query($sql);
        
        if ($result && $db->num_rows($result) > 0) {
            $row = $db->fetch_assoc($result);
            if ($row['attempt_count'] > 5) {
                $alerts[] = [
                    'type' => 'rapid_verification_attempts',
                    'severity' => 'high',
                    'description' => "User {$user_id} made {$row['attempt_count']} verification attempts in the last hour"
                ];
            }
        }
    }
    
    // Check for unusual time patterns (verifications outside business hours)
    $current_hour = date('H');
    if ($current_hour < 8 || $current_hour > 18) {
        $alerts[] = [
            'type' => 'unusual_time_verification',
            'severity' => 'medium',
            'description' => "Verification attempt at unusual hour: {$current_hour}:00"
        ];
    }
    
    // Check for high-value restock verification
    $sql = "SELECT r.quantity, p.buy_price 
            FROM restock r 
            JOIN products p ON r.product_id = p.id 
            WHERE r.id = '{$restock_id}'";
    $result = $db->query($sql);
    
    if ($result && $db->num_rows($result) > 0) {
        $row = $db->fetch_assoc($result);
        $total_value = $row['quantity'] * $row['buy_price'];
        
        if ($total_value > 1000) {
            $alerts[] = [
                'type' => 'high_value_restock',
                'severity' => 'high',
                'description' => "High-value restock verification: $" . number_format($total_value, 2)
            ];
        }
        
        if ($row['quantity'] > 100) {
            $alerts[] = [
                'type' => 'bulk_quantity_restock',
                'severity' => 'medium',
                'description' => "Bulk quantity restock verification: {$row['quantity']} units"
            ];
        }
    }
    
    return $alerts;
}

/*--------------------------------------------------------------*/
/* Create security alert
/*--------------------------------------------------------------*/
function create_security_alert($alert_type, $user_id, $restock_id, $description, $severity = 'medium') {
    global $db;
    
    // Check if security_alerts table exists
    $table_check = $db->query("SHOW TABLES LIKE 'security_alerts'");
    
    if (!$table_check || $db->num_rows($table_check) == 0) {
        // Table doesn't exist, skip alert creation
        return true;
    }
    
    $sql = "INSERT INTO security_alerts (alert_type, user_id, restock_id, description, severity) 
            VALUES ('{$alert_type}', '{$user_id}', '{$restock_id}', '{$description}', '{$severity}')";
    
    return $db->query($sql);
}

/*--------------------------------------------------------------*/
/* Get pending security alerts
/*--------------------------------------------------------------*/
function get_pending_security_alerts($limit = 10) {
    global $db;
    
    // Check if security_alerts table exists
    $table_check = $db->query("SHOW TABLES LIKE 'security_alerts'");
    
    if (!$table_check || $db->num_rows($table_check) == 0) {
        // Table doesn't exist, return empty array
        return [];
    }
    
    $sql = "SELECT sa.*, u.name as user_name, r.id as restock_id 
            FROM security_alerts sa 
            LEFT JOIN users u ON sa.user_id = u.id 
            LEFT JOIN restock r ON sa.restock_id = r.id 
            WHERE sa.status = 'new' 
            ORDER BY sa.created_at DESC 
            LIMIT {$limit}";
    
    return find_by_sql($sql);
}

/*--------------------------------------------------------------*/
/* Check if restock requires dual verification
/*--------------------------------------------------------------*/
function requires_dual_verification($restock_id) {
    global $db;
    
    // Get restock details
    $sql = "SELECT r.quantity, p.buy_price 
            FROM restock r 
            JOIN products p ON r.product_id = p.id 
            WHERE r.id = '{$restock_id}'";
    $result = $db->query($sql);
    
    if ($result && $db->num_rows($result) > 0) {
        $row = $db->fetch_assoc($result);
        $total_value = $row['quantity'] * $row['buy_price'];
        
        // Require dual verification for high-value or bulk orders
        if ($total_value > 500 || $row['quantity'] > 50) {
            return true;
        }
    }
    
    return false;
}

/*--------------------------------------------------------------*/
/* Get verification status for restock
/*--------------------------------------------------------------*/
function get_verification_status($restock_id) {
    global $db;
    
    $sql = "SELECT verification_status, verified_by_1, verified_by_2, 
                   verification_1_date, verification_2_date,
                   u1.name as verifier_1_name, u2.name as verifier_2_name
            FROM restock r
            LEFT JOIN users u1 ON r.verified_by_1 = u1.id
            LEFT JOIN users u2 ON r.verified_by_2 = u2.id
            WHERE r.id = '{$restock_id}'";
    
    $result = $db->query($sql);
    return ($result && $db->num_rows($result) > 0) ? $db->fetch_assoc($result) : null;
}

/*--------------------------------------------------------------*/
/* Perform first verification
/*--------------------------------------------------------------*/
function perform_first_verification($restock_id, $user_id) {
    global $db;
    
    // Check if user can verify
    if (!can_user_verify_restock($user_id, $restock_id)) {
        return ['success' => false, 'message' => 'You cannot verify this restock order.'];
    }
    
    // Log attempt
    log_verification_attempt($user_id, $restock_id, 'verify', false);
    
    // Check for anomalies
    $anomalies = detect_verification_anomalies($user_id, $restock_id);
    foreach ($anomalies as $anomaly) {
        create_security_alert($anomaly['type'], $user_id, $restock_id, $anomaly['description'], $anomaly['severity']);
    }
    
    // Update restock with first verification
    $sql = "UPDATE restock SET 
            verified_by_1 = '{$user_id}', 
            verification_1_date = NOW(), 
            verification_status = 'partial' 
            WHERE id = '{$restock_id}' AND verification_status = 'pending'";
    
    if ($db->query($sql)) {
        // Log successful attempt
        log_verification_attempt($user_id, $restock_id, 'verify', true);
        
        // Log audit trail
        log_restock_audit($restock_id, $user_id, 'first_verification', 
                         ['status' => 'pending'], 
                         ['status' => 'partial', 'verified_by_1' => $user_id]);
        
        // Check if dual verification is required
        if (requires_dual_verification($restock_id)) {
            return ['success' => true, 'message' => 'First verification completed. Awaiting second verification.', 'requires_dual' => true];
        } else {
            // Single verification is sufficient, complete the process
            return complete_verification($restock_id, $user_id);
        }
    }
    
    return ['success' => false, 'message' => 'Failed to perform first verification.'];
}

/*--------------------------------------------------------------*/
/* Perform second verification
/*--------------------------------------------------------------*/
function perform_second_verification($restock_id, $user_id) {
    global $db;
    
    // Check if user can verify
    if (!can_user_verify_restock($user_id, $restock_id)) {
        return ['success' => false, 'message' => 'You cannot verify this restock order.'];
    }
    
    // Check if first verification exists
    $sql = "SELECT verified_by_1, verification_status FROM restock WHERE id = '{$restock_id}'";
    $result = $db->query($sql);
    
    if (!$result || $db->num_rows($result) == 0) {
        return ['success' => false, 'message' => 'Restock order not found.'];
    }
    
    $restock = $db->fetch_assoc($result);
    
    if ($restock['verification_status'] != 'partial') {
        return ['success' => false, 'message' => 'First verification not completed.'];
    }
    
    // Log attempt
    log_verification_attempt($user_id, $restock_id, 'verify', false);
    
    // Check for anomalies
    $anomalies = detect_verification_anomalies($user_id, $restock_id);
    foreach ($anomalies as $anomaly) {
        create_security_alert($anomaly['type'], $user_id, $restock_id, $anomaly['description'], $anomaly['severity']);
    }
    
    // Update restock with second verification
    $sql = "UPDATE restock SET 
            verified_by_2 = '{$user_id}', 
            verification_2_date = NOW(), 
            verification_status = 'completed' 
            WHERE id = '{$restock_id}' AND verification_status = 'partial'";
    
    if ($db->query($sql)) {
        // Log successful attempt
        log_verification_attempt($user_id, $restock_id, 'verify', true);
        
        // Log audit trail
        log_restock_audit($restock_id, $user_id, 'second_verification', 
                         ['status' => 'partial'], 
                         ['status' => 'completed', 'verified_by_2' => $user_id]);
        
        // Complete the verification process
        return complete_verification($restock_id, $user_id);
    }
    
    return ['success' => false, 'message' => 'Failed to perform second verification.'];
}

/*--------------------------------------------------------------*/
/* Complete verification and update inventory
/*--------------------------------------------------------------*/
function complete_verification($restock_id, $user_id) {
    global $db, $session;
    
    $restock = find_by_id('restock', $restock_id);
    $product = find_by_id('products', $restock['product_id']);
    
    if (!$restock || !$product) {
        return ['success' => false, 'message' => 'Restock or product not found.'];
    }
    
    // Update product quantity
    $new_qty = $product['quantity'] + $restock['quantity'];
    $sql = "UPDATE products SET quantity = '{$new_qty}' WHERE id = '{$restock['product_id']}'";
    
    if ($db->query($sql)) {
        // Update restock status to verified (legacy compatibility)
        $update_sql = "UPDATE restock SET status = 1 WHERE id = '{$restock_id}'";
        $db->query($update_sql);
        
        // Log audit trail
        log_restock_audit($restock_id, $user_id, 'inventory_updated', 
                         ['product_quantity' => $product['quantity']], 
                         ['product_quantity' => $new_qty]);
        
        // Send email notification
        include_once 'send_email.php';
        $to = 'muhdnasrullah47@gmail.com';
        $subject = 'Restock Order Completed - Dual Verification';
        $message = '<p>A restock order has been completed with dual verification.</p>';
        $message .= '<p>Restock ID: ' . $restock_id . '</p>';
        $message .= '<p>Product: ' . $product['name'] . '</p>';
        $message .= '<p>Quantity: ' . $restock['quantity'] . '</p>';
        sendEmailAlert($to, $subject, $message);
        
        return ['success' => true, 'message' => 'Restock verification completed and inventory updated.'];
    }
    
    return ['success' => false, 'message' => 'Failed to update inventory.'];
}

/*--------------------------------------------------------------*/
/* Reject restock verification
/*--------------------------------------------------------------*/
function reject_restock_verification($restock_id, $user_id, $reason) {
    global $db;
    
    // Log attempt
    log_verification_attempt($user_id, $restock_id, 'reject', false);
    
    $reason_escaped = $db->escape($reason);
    
    $sql = "UPDATE restock SET 
            verification_status = 'rejected', 
            rejection_reason = '{$reason_escaped}' 
            WHERE id = '{$restock_id}'";
    
    if ($db->query($sql)) {
        // Log successful attempt
        log_verification_attempt($user_id, $restock_id, 'reject', true);
        
        // Log audit trail
        log_restock_audit($restock_id, $user_id, 'verification_rejected', 
                         null, 
                         ['status' => 'rejected', 'reason' => $reason]);
        
        // Create security alert for rejected verification
        create_security_alert('verification_rejected', $user_id, $restock_id, 
                            "Restock verification rejected: {$reason}", 'medium');
        
        return ['success' => true, 'message' => 'Restock verification rejected.'];
    }
    
    return ['success' => false, 'message' => 'Failed to reject verification.'];
}

?> 