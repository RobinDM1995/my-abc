<?php defined('C5_EXECUTE') or die("Access Denied."); ?>
<?php
  //include header
  $this->inc('elements/header.php');
?>

  <main class="home-content">
    <div class="container-fluid">
      <div class="row">
        <div>
          <?php
            //main content
            $a = new Area('Full Width');
            $a->setAreaGridMaximumColumns(12);
            $a->display($c);
          ?>
        </div>
      </div>
    </div>
    <div class="section">
      <div class="container">
        <div class="row">
          <div class="col-sm-12">
            <?php
              //main content
              $a = new Area('Main');
              $a->setAreaGridMaximumColumns(12);
              $a->display($c);
            ?>
          </div>
        </div>
      </div>
    </div>
    <div class="section">
      <div class="container-fluid">
        <div class="row">
          <div>
            <?php
              //main content
              $a = new Area('Full Width 2');
              $a->setAreaGridMaximumColumns(12);
              $a->display($c);
            ?>
          </div>
        </div>
      </div>
    </div>
  </main>
<?php
  //include footer
  $this->inc('elements/footer.php');
?>
