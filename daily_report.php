<?php
  $page_title = 'Daily Restock Report';
  require_once('includes/load.php');
  // Check what level user has permission to view this page
  page_require_level(3);
?>

<?php
 $year  = date('Y');
 $month = date('m');
 $restock = dailyRestock($year, $month);
?>
<?php include_once('layouts/header.php'); ?>
<div class="row">
  <div class="col-md-6">
    <?php echo display_msg($msg); ?>
  </div>
</div>
  <div class="row">
    <div class="col-md-12">
      <div class="panel panel-default">
        <div class="panel-heading clearfix">
          <strong>
            <span class="glyphicon glyphicon-th"></span>
            <span>Daily Restock Report</span>
          </strong>
        </div>
        <div class="panel-body">
          <table class="table table-bordered table-striped">
            <thead>
              <tr>
                <th class="text-center" style="width: 50px;">#</th>
                <th> Product name </th>
                <th class="text-center" style="width: 15%;"> Quantity Restocked</th>
                <th class="text-center" style="width: 15%;"> Date </th>
             </tr>
            </thead>
           <tbody>
             <?php foreach ($restock as $r):?>
             <tr>
               <td class="text-center"><?php echo count_id();?></td>
               <td><?php echo remove_junk($r['name']); ?></td>
               <td class="text-center"><?php echo (int)$r['quantity']; ?></td>
               <td class="text-center"><?php echo $r['date']; ?></td>
             </tr>
             <?php endforeach;?>
           </tbody>
         </table>
        </div>
      </div>
    </div>
  </div>

<?php include_once('layouts/footer.php'); ?>
