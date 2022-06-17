<?php
defined('C5_EXECUTE') or die("Access Denied.");
$this->inc('elements/header.php'); ?>

      <div class="row main-content">
        <div class="col-md-12">
          <?php Loader::element('system_errors', array('format' => 'block', 'error' => $error, 'success' => $success, 'message' => $message)); ?>
          <?php print $innerContent; ?>
        </div>
      </div>

<?php  $this->inc('elements/footer.php'); ?>
