<?php
  $page_title = 'Change Password';
  require_once('includes/load.php');
  // Checkin What level user has permission to view this page
  page_require_level(3);
?>
<?php $user = current_user(); ?>
<?php
  if(isset($_POST['update'])){

    $req_fields = array('new-password','old-password','id' );
    validate_fields($req_fields);

    // Strong password policy check
    $password_raw = $_POST['new-password'];
    $password_errors = [];
    if(strlen($password_raw) < 8) {
      $password_errors[] = "Password must be at least 8 characters.";
    }
    if(!preg_match('/[A-Z]/', $password_raw)) {
      $password_errors[] = "Password must contain at least one uppercase letter.";
    }
    if(!preg_match('/[a-z]/', $password_raw)) {
      $password_errors[] = "Password must contain at least one lowercase letter.";
    }
    if(!preg_match('/[0-9]/', $password_raw)) {
      $password_errors[] = "Password must contain at least one number.";
    }
    if(!preg_match('/[\W_]/', $password_raw)) {
      $password_errors[] = "Password must contain at least one special character.";
    }
    if(!empty($password_errors)) {
      $session->msg("d", $password_errors);
      redirect('change_password.php', false);
    }

    if(empty($errors)){

             if(sha1($_POST['old-password']) !== current_user()['password'] ){
               $session->msg('d', "Your old password not match");
               redirect('change_password.php',false);
             }

            $id = (int)$_POST['id'];
            $new = remove_junk($db->escape(sha1($_POST['new-password'])));
            $sql = "UPDATE users SET password ='{$new}' WHERE id='{$db->escape($id)}'";
            $result = $db->query($sql);
                if($result && $db->affected_rows() === 1):
                  $session->msg('s',"Successfully update.");
                  redirect('edit_account.php', false); 
                else:
                  $session->msg('d',' Sorry failed to updated!');
                  redirect('change_password.php', false);
                endif;
    } else {
      $session->msg("d", $errors);
      redirect('change_password.php',false);
    }
  }
?>
<?php include_once('layouts/header.php'); ?>
<div class="login-page">
    <div class="text-center">
       <h3>Change your password</h3>
     </div>
     <?php echo display_msg($msg); ?>
      <form method="post" action="change_password.php" class="clearfix">
        <div class="form-group">
              <label for="newPassword" class="control-label">New password</label>
              <input type="password" class="form-control" name="new-password" placeholder="New password">
        </div>
        <div class="form-group">
              <label for="oldPassword" class="control-label">Old password</label>
              <input type="password" class="form-control" name="old-password" placeholder="Old password">
        </div>
        <div class="form-group clearfix">
               <input type="hidden" name="id" value="<?php echo (int)$user['id'];?>">
                <button type="submit" name="update" class="btn btn-info">Change</button>
        </div>
    </form>
</div>
<?php include_once('layouts/footer.php'); ?>
