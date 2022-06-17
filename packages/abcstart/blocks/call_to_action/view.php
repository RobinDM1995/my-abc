<?php  defined("C5_EXECUTE") or die("Access Denied."); ?>
<?php
$url = "";
$target = "";
if(!empty($linktype) && $linktype != 0) {
  if($linktype == 1) {
    if(!empty($internallink)) {
      $lPage = Page::getByID($internallink);
      $url = $lPage->getCollectionLink();
      $target = "_self";
    }
  } elseif ($linktype == 2) {
    if(!empty($url)) {
      $url = $linkurl;
      $target = "_blank";
    }
  }
}

?>


<?php if(!empty($url)) { ?>
  <div class="call-to-action <?php echo (!empty($align) && trim($align) != '' ? $align : ''); ?>">
    <a href="<?php echo $url; ?>" target="<?php echo $target; ?>" class="btn-primary-color
                                                                        <?php echo $class; ?>
                                                                        <?php echo (!empty($icon && trim($icon) != '') ? ($iconlocation == 2 ? 'btn-icon-right' : 'btn-icon-left') : ''); ?>">

      <?php if(!empty($icon) && trim($icon) != '') { ?>
        <i class="fa <?php echo $icon; ?> <?php echo ($iconlocation == 2 ? 'right' : 'left'); ?>"></i>
      <?php } ?>

      <?php if(!empty($name) && trim($name) != '') { ?>
        <div class="call-to-action-text">
          <?php echo $name; ?>
        </div>
      <?php } ?>
    </a>
  </div>
<?php } else { ?>
  <div class="ccm-edit-mode-disabled-item" style="<?php echo isset($width) ? "width: $width;" : '' ?><?php echo isset($height) ? "height: $height;" : '' ?>">
      <div style="padding: 25px 0px 25px 0px"><?php echo t('Button: er is geen link gevonden...')?></div>
  </div>
<?php } ?>
