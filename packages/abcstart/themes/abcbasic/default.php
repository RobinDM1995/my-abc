<?php defined('C5_EXECUTE') or die("Access Denied.");?>
<?php
  //include header
  $this->inc('elements/header.php');
?>

  <!-- <main class="inhoud"> -->
    <!-- <div class="container"> -->
      <div class="row main-content">
        <div class="col-md-12">
          <?php
            //main content
            $a = new Area('Main');
            $a->setAreaGridMaximumColumns(12);
            $a->display($c);
          ?>
        </div>
      </div>
    <!-- </div> -->
  <!-- </main> -->

<?php
  //include footer
  $this->inc('elements/footer.php');
?>
