<?php
require_once('includes/load.php');
page_require_level(3);

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $restock = find_by_id('restock', $id);
    $product = find_by_id('products', $restock['product_id']);

    if(!$restock){
        $session->msg('d', "Missing restock id.");
        redirect('restock.php');
    }

    if(!$product){
        $session->msg('d', "Product Not Found!");
        redirect('restock.php');
    }

    $new_qty = $product['quantity'] + $restock['quantity'];
    $sql = "UPDATE products SET quantity='{$new_qty}' WHERE id='{$restock['product_id']}'";

    if ($db->query($sql)) {
        $delete_sql = "UPDATE restock SET status = 1 WHERE id='{$id}'";
        if ($db->query($delete_sql)) {
           
            include 'send_email.php';
            $to = 'muhdnasrullah47@gmail.com';
            $subject = 'New Restock Order Verified';
            $message = '<p>A new restock order has been verified. Please review it.</p>';
            sendEmailAlert($to, $subject, $message);

            $session->msg('s', "Restock verified and transferred to products.");
            redirect('restock.php');
        } else {
            $session->msg('d', "Failed to delete restock record.");
            redirect('restock.php');
        }
    } else {
        $session->msg('d', "Failed to update or insert product.");
        redirect('restock.php');
    }
} else {
    $session->msg('d', "Invalid request.");
    redirect('restock.php');
}
?>
