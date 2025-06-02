<?php
$page_title = 'Verified Restock Report';
require_once('includes/load.php');
// Check what level user has permission to view this page
page_require_level(3);
?>
<?php include_once('layouts/header.php'); ?>
<div class="row">
  <div class="col-md-6">
    <?php echo display_msg($msg); ?>
  </div>
</div>
<div class="row">
  <div class="col-md-6">
    <div class="panel panel-default">
      <div class="panel-heading clearfix">
        <strong>
          <span class="glyphicon glyphicon-th"></span>
          <span>Verified Restock Report</span>
        </strong>
      </div>
      <div class="panel-body">
        <form class="clearfix" method="post" action="restock_report_process.php">
          <div class="form-group">
            <label class="form-label">Date Range</label>
            <div class="input-group">
              <input type="text" class="datepicker form-control" name="start-date" placeholder="From" required>
              <span class="input-group-addon"><i class="glyphicon glyphicon-menu-right"></i></span>
              <input type="text" class="datepicker form-control" name="end-date" placeholder="To" required>
            </div>
          </div>
          <div class="form-group">
            <button type="submit" name="submit" class="btn btn-info">Generate Report</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include_once('layouts/footer.php'); ?>
