<?php include_once('includes/load.php'); ?>
<?php
$req_fields = array('username','password' );
validate_fields($req_fields);
$username = remove_junk($_POST['username']);
$password = remove_junk($_POST['password']);

if(empty($errors)){
  $user = find_by_username($username);

  if(!$user){
    $session->msg("d", "Sorry Username/Password incorrect.");
    redirect('index.php',false);
  }

  // Check if account is locked
  if (isset($user['locked_until']) && strtotime($user['locked_until']) > time()) {
    $session->msg("d", "Account locked. Try again later.");
    redirect('index.php', false);
  }

  // Check password
  if(sha1($password) === $user['password']){
    // Reset failed attempts on successful login
    $db->query("UPDATE users SET failed_attempts=0, locked_until=NULL WHERE id='{$user['id']}'");
    $session->login($user['id']);
    updateLastLogIn($user['id']);
    $session->msg("s", "Welcome to Inventory Management System");
    // Redirect based on user_level
    switch ($user['user_level']) {
      case 1:
        redirect('admin.php', false);
        break;
      case 2:
        redirect('home.php', false);
        break;
      case 3:
        redirect('user.php', false);
        break;
      default:
        redirect('home.php', false);
    }
  } else {
    // Increment failed attempts
    $failed_attempts = (int)$user['failed_attempts'] + 1;
    $lockout_time = 15 * 60; // 15 minutes
    if($failed_attempts >= 5){
      $locked_until = date('Y-m-d H:i:s', time() + $lockout_time);
      $db->query("UPDATE users SET failed_attempts='{$failed_attempts}', locked_until='{$locked_until}' WHERE id='{$user['id']}'");
      $session->msg("d", "Account locked due to too many failed attempts. Try again in 15 minutes.");
    } else {
      $db->query("UPDATE users SET failed_attempts='{$failed_attempts}' WHERE id='{$user['id']}'");
      $session->msg("d", "Sorry Username/Password incorrect. Attempt $failed_attempts of 5.");
    }
    redirect('index.php',false);
  }
} else {
   $session->msg("d", $errors);
   redirect('index.php',false);
}
?>
