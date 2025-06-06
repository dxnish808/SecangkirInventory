<?php
  $page_title = 'Edit User';
  require_once('includes/load.php');
  // Checkin What level user has permission to view this page
   page_require_level(1);
?>
<?php
  $e_user = find_by_id('users',(int)$_GET['id']);
  $groups  = find_all('user_groups');
  if(!$e_user){
    $session->msg("d","Missing user id.");
    redirect('users.php');
  }
?>

<?php
//Update User basic info
  if(isset($_POST['update'])) {
    $req_fields = array('name','username','level');
    validate_fields($req_fields);
    if(empty($errors)){
             $id = (int)$e_user['id'];
           $name = remove_junk($db->escape($_POST['name']));
       $username = remove_junk($db->escape($_POST['username']));
          $level = (int)$db->escape($_POST['level']);
       $status   = remove_junk($db->escape($_POST['status']));
            $sql = "UPDATE users SET name ='{$name}', username ='{$username}',user_level='{$level}',status='{$status}' WHERE id='{$db->escape($id)}'";
         $result = $db->query($sql);
          if($result && $db->affected_rows() === 1){
            $session->msg('s',"Acount Updated ");
            redirect('edit_user.php?id='.(int)$e_user['id'], false);
          } else {
            $session->msg('d',' Sorry failed to updated!');
            redirect('edit_user.php?id='.(int)$e_user['id'], false);
          }
    } else {
      $session->msg("d", $errors);
      redirect('edit_user.php?id='.(int)$e_user['id'],false);
    }
  }
?>
<?php
// Update user password
if(isset($_POST['update-pass'])) {
  $req_fields = array('old-password','password');
  validate_fields($req_fields);

  // Strong password policy check
  $password_raw = $_POST['password'];
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
    redirect('edit_user.php?id='.(int)$e_user['id'], false);
  }

  // Old password verification
  $submitted_old = sha1($_POST['old-password']);
  $db_password = $e_user['password'];
  if($submitted_old !== $db_password){
    $session->msg('d', "Old password does not match.");
    redirect('edit_user.php?id='.(int)$e_user['id'], false);
  }

  if(empty($errors)){
    $id = (int)$e_user['id'];
    $password = remove_junk($db->escape($password_raw));
    $h_pass   = sha1($password);
    $sql = "UPDATE users SET password='{$h_pass}' WHERE id='{$db->escape($id)}'";
    $result = $db->query($sql);
    if($result && $db->affected_rows() === 1){
      $session->msg('s',"User password has been updated ");
      redirect('edit_user.php?id='.(int)$e_user['id'], false);
    } else {
      $session->msg('d',' Sorry failed to update user password!');
      redirect('edit_user.php?id='.(int)$e_user['id'], false);
    }
  } else {
    $session->msg("d", $errors);
    redirect('edit_user.php?id='.(int)$e_user['id'],false);
  }
}
?>
<?php include_once('layouts/header.php'); ?>
 <div class="row">
   <div class="col-md-12"> <?php echo display_msg($msg); ?> </div>
  <div class="col-md-6">
     <div class="panel panel-default">
       <div class="panel-heading">
        <strong>
          <span class="glyphicon glyphicon-th"></span>
          Update <?php echo remove_junk(ucwords($e_user['name'])); ?> Account
        </strong>
       </div>
       <div class="panel-body">
          <form method="post" action="edit_user.php?id=<?php echo (int)$e_user['id'];?>" class="clearfix">
            <div class="form-group">
                  <label for="name" class="control-label">Name</label>
                  <input type="name" class="form-control" name="name" value="<?php echo remove_junk(ucwords($e_user['name'])); ?>">
            </div>
            <div class="form-group">
                  <label for="username" class="control-label">Username</label>
                  <input type="text" class="form-control" name="username" value="<?php echo remove_junk(ucwords($e_user['username'])); ?>">
            </div>
            <div class="form-group">
              <label for="level">User Role</label>
                <select class="form-control" name="level">
                  <?php foreach ($groups as $group ):?>
                   <option <?php if($group['group_level'] === $e_user['user_level']) echo 'selected="selected"';?> value="<?php echo $group['group_level'];?>"><?php echo ucwords($group['group_name']);?></option>
                <?php endforeach;?>
                </select>
            </div>

            <div class="form-group clearfix">
                    <button type="submit" name="update" class="btn btn-info">Update</button>
            </div>
        </form>
       </div>
     </div>
  </div>
  <!-- Change password form -->
  <div class="col-md-6">
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong>
          <span class="glyphicon glyphicon-th"></span>
          Change <?php echo remove_junk(ucwords($e_user['name'])); ?> password
        </strong>
      </div>
      <div class="panel-body">
        <form action="edit_user.php?id=<?php echo (int)$e_user['id'];?>" method="post" class="clearfix">
          <div class="form-group">
                <label for="old-password" class="control-label">Old Password</label>
                <input type="password" class="form-control" name="old-password" placeholder="Type old password" required>
          </div>
          <div class="form-group">
                <label for="password" class="control-label">New Password</label>
                <input type="password" class="form-control" name="password" placeholder="Type user new password" required>
          </div>
          <div class="form-group clearfix">
                  <button type="submit" name="update-pass" class="btn btn-danger pull-right">Change</button>
          </div>
        </form>
      </div>
    </div>
  </div>

 </div>
<?php include_once('layouts/footer.php'); ?>
