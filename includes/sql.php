<?php
  require_once('includes/load.php');

/*--------------------------------------------------------------*/
/* Function for find all database table rows by table name
/*--------------------------------------------------------------*/
function find_all($table) {
   global $db;
   if(tableExists($table))
   {
     return find_by_sql("SELECT * FROM ".$db->escape($table));
   }
}
/*--------------------------------------------------------------*/
/* Function for Perform queries
/*--------------------------------------------------------------*/
function find_by_sql($sql) {
  global $db;
  $result = $db->query($sql);
  $result_set = [];
  if ($result) {
      while ($row = $result->fetch_assoc()) {
          $result_set[] = $row;
      }
  }
  return $result_set;
}

/*--------------------------------------------------------------*/
/*  Function for Find data from table by id
/*--------------------------------------------------------------*/
function find_by_id($table,$id)
{
  global $db;
  $id = (int)$id;
    if(tableExists($table)){
          $sql = $db->query("SELECT * FROM {$db->escape($table)} WHERE id='{$db->escape($id)}' LIMIT 1");
          if($result = $db->fetch_assoc($sql))
            return $result;
          else
            return null;
     }
}
/*--------------------------------------------------------------*/
/* Function for Delete data from table by id
/*--------------------------------------------------------------*/
function delete_by_id($table,$id)
{
  global $db;
  if(tableExists($table))
   {
    $sql = "DELETE FROM ".$db->escape($table);
    $sql .= " WHERE id=". $db->escape($id);
    $sql .= " LIMIT 1";
    $db->query($sql);
    return ($db->affected_rows() === 1) ? true : false;
   }
}
/*--------------------------------------------------------------*/
/* Function for Count id  By table name
/*--------------------------------------------------------------*/

function count_by_id($table){
  global $db;
  if(tableExists($table))
  {
    $sql    = "SELECT COUNT(id) AS total FROM ".$db->escape($table);
    $result = $db->query($sql);
     return($db->fetch_assoc($result));
  }
}
/*--------------------------------------------------------------*/
/* Determine if database table exists
/*--------------------------------------------------------------*/
function tableExists($table){
  global $db;
  $table_exit = $db->query('SHOW TABLES FROM '.DB_NAME.' LIKE "'.$db->escape($table).'"');
      if($table_exit) {
        if($db->num_rows($table_exit) > 0)
              return true;
         else
              return false;
      }
  }
 /*--------------------------------------------------------------*/
 /* Login with the data provided in $_POST,
 /* coming from the login form.
/*--------------------------------------------------------------*/
  function authenticate($username='', $password='') {
    global $db;
    $username = $db->escape($username);
    $password = $db->escape($password);
    $sql  = sprintf("SELECT id,username,password,user_level FROM users WHERE username ='%s' LIMIT 1", $username);
    $result = $db->query($sql);
    if($db->num_rows($result)){
      $user = $db->fetch_assoc($result);
      $password_request = sha1($password);
      if($password_request === $user['password'] ){
        return $user['id'];
      }
    }
   return false;
  }
  /*--------------------------------------------------------------*/
  /* Login with the data provided in $_POST,
  /* coming from the login_v2.php form.
  /* If you used this method then remove authenticate function.
 /*--------------------------------------------------------------*/
   function authenticate_v2($username='', $password='') {
     global $db;
     $username = $db->escape($username);
     $password = $db->escape($password);
     $sql  = sprintf("SELECT id,username,password,user_level FROM users WHERE username ='%s' LIMIT 1", $username);
     $result = $db->query($sql);
     if($db->num_rows($result)){
       $user = $db->fetch_assoc($result);
       $password_request = sha1($password);
       if($password_request === $user['password'] ){
         return $user;
       }
     }
    return false;
   }


  /*--------------------------------------------------------------*/
  /* Find current log in user by session id
  /*--------------------------------------------------------------*/
  function current_user(){
      static $current_user;
      global $db;
      if(!$current_user){
         if(isset($_SESSION['user_id'])):
             $user_id = intval($_SESSION['user_id']);
             $current_user = find_by_id('users',$user_id);
        endif;
      }
    return $current_user;
  }
  /*--------------------------------------------------------------*/
  /* Find all user by
  /* Joining users table and user gropus table
  /*--------------------------------------------------------------*/
  function find_all_user(){
      global $db;
      $results = array();
      $sql = "SELECT u.id,u.name,u.username,u.user_level,u.status,u.last_login,";
      $sql .="g.group_name ";
      $sql .="FROM users u ";
      $sql .="LEFT JOIN user_groups g ";
      $sql .="ON g.group_level=u.user_level ORDER BY u.name ASC";
      $result = find_by_sql($sql);
      return $result;
  }
  /*--------------------------------------------------------------*/
  /* Function to update the last log in of a user
  /*--------------------------------------------------------------*/

 function updateLastLogIn($user_id)
	{
		global $db;
    $date = make_date();
    $sql = "UPDATE users SET last_login='{$date}' WHERE id ='{$user_id}' LIMIT 1";
    $result = $db->query($sql);
    return ($result && $db->affected_rows() === 1 ? true : false);
	}

  /*--------------------------------------------------------------*/
  /* Find all Group name
  /*--------------------------------------------------------------*/
  function find_by_groupName($val)
  {
    global $db;
    $sql = "SELECT group_name FROM user_groups WHERE group_name = '{$db->escape($val)}' LIMIT 1 ";
    $result = $db->query($sql);
    return($db->num_rows($result) === 0 ? true : false);
  }
  /*--------------------------------------------------------------*/
  /* Find group level
  /*--------------------------------------------------------------*/
  function find_by_groupLevel($level)
  {
    global $db;
    $sql = "SELECT group_level FROM user_groups WHERE group_level = '{$db->escape($level)}' LIMIT 1 ";
    $result = $db->query($sql);
    return($db->num_rows($result) === 0 ? true : false);
  }
  /*--------------------------------------------------------------*/
  /* Function for cheaking which user level has access to page
  /*--------------------------------------------------------------*/
   function page_require_level($require_level){
     global $session;
     $current_user = current_user();
     $login_level = find_by_groupLevel($current_user['user_level']);
     //if user not login
     if (!$session->isUserLoggedIn(true)):
            $session->msg('d','Please login...');
            redirect('index.php', false);
      //cheackin log in User level and Require level is Less than or equal to
     elseif($current_user['user_level'] <= (int)$require_level):
              return true;
      else:
            $session->msg("d", "Sorry! you dont have permission to view the page.");
            redirect('home.php', false);
        endif;

     }
   /*--------------------------------------------------------------*/
   /* Function for Finding all product name
   /* JOIN with categorie  and media database table
   /*--------------------------------------------------------------*/
   function join_product_table(){
    global $db;
    $sql  = "SELECT p.id, p.name, p.quantity, p.buy_price, p.date, c.name AS categorie";
    $sql .= " FROM products p";
    $sql .= " LEFT JOIN categories c ON c.id = p.categorie_id";
    $sql .= " ORDER BY p.id ASC";
    return find_by_sql($sql);
}

  /*--------------------------------------------------------------*/
  /* Function for Finding all product name
  /* Request coming from ajax.php for auto suggest
  /*--------------------------------------------------------------*/

   function find_product_by_title($product_name){
     global $db;
     $p_name = remove_junk($db->escape($product_name));
     $sql = "SELECT name FROM products WHERE name like '%$p_name%' LIMIT 5";
     $result = find_by_sql($sql);
     return $result;
   }

  /*--------------------------------------------------------------*/
  /* Function for Finding all product info by product title
  /* Request coming from ajax.php
  /*--------------------------------------------------------------*/
  function find_all_product_info_by_title($title){
    global $db;
    $sql  = "SELECT * FROM products ";
    $sql .= " WHERE name ='{$title}'";
    $sql .=" LIMIT 1";
    return find_by_sql($sql);
  }

  /*--------------------------------------------------------------*/
  /* Function for Update product quantity
  /*--------------------------------------------------------------*/
  function update_product_qty($qty,$p_id){
    global $db;
    $qty = (int) $qty;
    $id  = (int)$p_id;
    $sql = "UPDATE products SET quantity=quantity -'{$qty}' WHERE id = '{$id}'";
    $result = $db->query($sql);
    return($db->affected_rows() === 1 ? true : false);

  }
  /*--------------------------------------------------------------*/
  /* Function for Display Recent product Added
  /*--------------------------------------------------------------*/
  function find_recent_product_added($limit){
    global $db;
    $sql   = " SELECT p.id, p.name, p.buy_price, c.name AS categorie";
    $sql  .= " FROM products p";
    $sql  .= " LEFT JOIN categories c ON c.id = p.categorie_id";
    $sql  .= " ORDER BY p.id DESC LIMIT ".$db->escape((int)$limit);
    return find_by_sql($sql);
}


 /*--------------------------------------------------------------*/
 /* Function for find all restock
 /*--------------------------------------------------------------*/
 function find_all_unverified_restock($rid = null) {
  global $db;
  $sql = "SELECT p.name, r.product_id, r.id, r.quantity, r.date, r.status ";
  $sql .= " FROM restock as r INNER JOIN products as p ON r.product_id = p.id";
  $sql .= " WHERE r.status = 0"; // Fetch only unverified restock items
  if(!empty($rid)){
      $sql .= " AND r.id = '$rid'";
  }
  $sql .= " ORDER BY r.date DESC";
  return find_by_sql($sql);
}




function find_all_restock_product($rid = NULL) {
  global $db;
  $sql  = "SELECT p.name, r.product_id, r.id, r.quantity, r.date, r.status ";
  $sql .= " FROM restock as r INNER JOIN products as p ON r.product_id = p.id";
  if(!empty($rid)){
      $sql .= " WHERE r.id = '$rid'";
  }
  $sql .= " ORDER BY r.date DESC";

  return find_by_sql($sql);
}

/*--------------------------------------------------------------*/
/* Function for Generate restock report by two dates
/*--------------------------------------------------------------*/
function find_restock_by_dates($start_date, $end_date) {
  global $db;
  $start_date = date("Y-m-d", strtotime($start_date));
  $end_date = date("Y-m-d", strtotime($end_date));
  $sql  = "SELECT r.date, p.name,";
  $sql .= " COUNT(r.id) AS total_records,";
  $sql .= " SUM(r.quantity) AS total_quantity";
  $sql .= " FROM restock r";
  $sql .= " JOIN products p ON r.product_id = p.id";
  $sql .= " WHERE r.date BETWEEN '{$start_date}' AND '{$end_date}'";
  $sql .= " AND r.status = 1"; // Assuming status 1 indicates verified restock
  $sql .= " GROUP BY DATE(r.date), p.name";
  $sql .= " ORDER BY DATE(r.date) DESC";

  return $db->query($sql);
}



/*--------------------------------------------------------------*/
/* Function for Generate Daily sales report
/*--------------------------------------------------------------*/
function dailyRestock($year, $month){
  global $db;
  $sql  = "SELECT r.quantity,";
  $sql .= " DATE_FORMAT(r.date, '%Y-%m-%e') AS date, p.name";
  $sql .= " FROM restock r";
  $sql .= " LEFT JOIN products p ON r.product_id = p.id";
  $sql .= " WHERE DATE_FORMAT(r.date, '%Y-%m' ) = '{$year}-{$month}'";
  $sql .= " AND r.status = 1";  // Only verified restocks
  $sql .= " ORDER BY DATE_FORMAT(r.date, '%e' ) ASC";
  return find_by_sql($sql);
}

/*--------------------------------------------------------------*/
/* Function for Generate Monthly sales report
/*--------------------------------------------------------------*/
function monthlyRestock($year){
  global $db;
  $sql  = "SELECT r.quantity,";
  $sql .= " DATE_FORMAT(r.date, '%Y-%m-%e') AS date, p.name";
  $sql .= " FROM restock r";
  $sql .= " LEFT JOIN products p ON r.product_id = p.id";
  $sql .= " WHERE DATE_FORMAT(r.date, '%Y' ) = '{$year}'";
  $sql .= " AND r.status = 1";  // Only verified restocks
  $sql .= " ORDER BY DATE_FORMAT(r.date, '%c' ) ASC";
  return find_by_sql($sql);
}

/*--------------------------------------------------------------*/
function find_returns_by_dates($start_date, $end_date) {
  global $db;
  $start_date = date("Y-m-d", strtotime($start_date));
  $end_date = date("Y-m-d", strtotime($end_date));

  $sql  = "SELECT r.date, p.name, r.quantity, r.reason";
  $sql .= " FROM returns r";
  $sql .= " JOIN products p ON r.product_id = p.id";
  $sql .= " WHERE r.date BETWEEN '{$start_date}' AND '{$end_date}'";
  $sql .= " ORDER BY DATE(r.date) DESC";

  return $db->query($sql);
}

/*-------------------------------------------------------------------*/
function count_unverified_restock() {
  global $db;
  $sql = "SELECT COUNT(id) AS total FROM restock WHERE status = 0";
  $result = $db->query($sql);
  return ($result && $db->num_rows($result) > 0) ? $db->fetch_assoc($result) : ['total' => 0];
}
/*---------------------------------------------------------------------*/
function find_recent_returns($limit) {
  global $db;
  $sql  = "SELECT r.date, r.product_name, r.quantity, r.reason ";
  $sql .= "FROM returns r ";
  $sql .= "ORDER BY r.date DESC LIMIT {$db->escape((int)$limit)}";
  $result = find_by_sql($sql);
  if ($result === false) {
    die("Database query failed: " . $db->error);
  }
  return $result;
}


?>



