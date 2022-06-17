<?php
  defined('C5_EXECUTE') or die(_("Access Denied."));
    if($c->getCollectionID() == HOME_CID){
      header('Location: ' . BASE_URL . DIR_REL . '/offerspdf');
    }
?>
