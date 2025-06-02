<?php
  $page_title = 'Admin Home Page';
  require_once('includes/load.php');
  page_require_level(1);

  $c_categorie = count_by_id('categories');
  $c_product   = count_by_id('products');
  $c_unverified_restock = count_unverified_restock();
  $c_user      = count_by_id('users');
  $recent_products = find_recent_product_added('5');
  $recent_returns = find_recent_returns('5');
?>
<?php include_once('layouts/header.php'); ?>

<div class="row">
  <div class="col-md-6">
    <?php echo display_msg($msg); ?>
  </div>
</div>

<div class="row">
  <a href="users.php" style="color:black;">
    <div class="col-md-3">
      <div class="panel panel-box clearfix">
        <div class="panel-icon pull-left bg-secondary1">
          <i class="glyphicon glyphicon-user"></i>
        </div>
        <div class="panel-value pull-right">
          <h2 class="margin-top"> <?php echo $c_user['total']; ?> </h2>
          <p class="text-muted">Users</p>
        </div>
      </div>
    </div>
  </a>

  <a href="categorie.php" style="color:black;">
    <div class="col-md-3">
      <div class="panel panel-box clearfix">
        <div class="panel-icon pull-left bg-red">
          <i class="glyphicon glyphicon-th-large"></i>
        </div>
        <div class="panel-value pull-right">
          <h2 class="margin-top"> <?php echo $c_categorie['total']; ?> </h2>
          <p class="text-muted">Categories</p>
        </div>
      </div>
    </div>
  </a>

  <a href="product.php" style="color:black;">
    <div class="col-md-3">
      <div class="panel panel-box clearfix">
        <div class="panel-icon pull-left bg-blue2">
          <i class="glyphicon glyphicon-shopping-cart"></i>
        </div>
        <div class="panel-value pull-right">
          <h2 class="margin-top"> <?php echo $c_product['total']; ?> </h2>
          <p class="text-muted">Products</p>
        </div>
      </div>
    </div>
  </a>

  <a href="restock.php" style="color:black;">
    <div class="col-md-3">
      <div class="panel panel-box clearfix">
        <div class="panel-icon pull-left bg-green">
          <i class="glyphicon glyphicon-send"></i>
        </div>
        <div class="panel-value pull-right">
          <h2 class="margin-top"> <?php echo $c_unverified_restock['total']; ?></h2>
          <p class="text-muted">Unverified Restocks</p>
        </div>
      </div>
    </div>
  </a>

  <div class="col-md-4">
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong>
          <span class="glyphicon glyphicon-th"></span>
          <span>Recently Added Products</span>
        </strong>
      </div>
      <div class="panel-body">
      <div class="list-group">
    <?php foreach ($recent_products as $recent_product): ?>
        <a class="list-group-item clearfix" href="edit_product.php?id=<?php echo (int)$recent_product['id']; ?>">
            <h4 class="list-group-item-heading">
                <?php echo remove_junk(first_character($recent_product['name'])); ?>
                <span class="label label-warning pull-right">
                    $<?php echo (int)$recent_product['buy_price']; ?>
                </span>
            </h4>
            <span class="list-group-item-text pull-right">
                <?php echo remove_junk(first_character($recent_product['categorie'])); ?>
            </span>
        </a>
    <?php endforeach; ?>
</div>

      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong>
          <span class="glyphicon glyphicon-th"></span>
          <span>Returns</span>
        </strong>
      </div>
      <div class="panel-body">
        <div class="list-group">
          <?php foreach ($recent_returns as $return): ?>
            <a class="list-group-item clearfix" href="#">
              <h4 class="list-group-item-heading">
                <?php echo remove_junk(first_character($return['product_name'])); ?>
                <span class="label label-danger pull-right">
                  Qty: <?php echo (int)$return['quantity']; ?>
                </span>
              </h4>
              <span class="list-group-item-text pull-right">
                Reason: <?php echo remove_junk(first_character($return['reason'])); ?>
              </span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include_once('layouts/footer.php'); ?>
