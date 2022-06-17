<?php defined('C5_EXECUTE') or die("Access Denied."); ?>
<?php $cPage = Page::getCurrentPage(); ?>
<?php $cUser = new User(); ?>

<!DOCTYPE html>
<html lang="<?php echo Localization::activeLanguage()?>">
  <head>
        <link rel="stylesheet" media="screen" type="text/css" href="<?php echo $this->getThemePath() ?>/css/bootstrap-responsive.min.css" />
				<link rel="stylesheet" media="screen" type="text/css" href="<?php echo $this->getThemePath() ?>/css/bootstrap.min.css" />
				<link rel="stylesheet" media="print" type="text/css"  href="<?php echo $this->getThemePath() ?>/css/admin.css" />
        <link rel="stylesheet" media="screen" type="text/css" href="<?php echo $this->getThemePath() ?>/css/main.css" />
        <link rel="stylesheet" media="print" type="text/css" href="<?php echo $this->getThemePath() ?>/css/print.css" />
				<link rel="stylesheet" type="text/css" href="<?php echo $this->getThemePath()?>/css/jquery.ui.css">

        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <!-- <link type="text/css" rel="stylesheet" href="<?php //echo $this->getThemePath()?>/materialize/css/materialize.min.css"/> -->

        <!-- <script src="https://unpkg.com/@popperjs/core@2/dist/umd/popper.js"></script> -->
        <!-- <script src="<?php //echo $this->getThemePath()?> /js/bootstrap.js"></script> -->
        <!-- <script src="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script> -->

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <?php Loader::element('header_required');?>
    <?php echo $html->css($view->getStylesheet('materialize.min.less'))?>
    <?php echo $html->css($view->getStylesheet('main.less'))?>

    <?php if($cUser->isLoggedIn()) { ?>
      <?php $gAdministrators = Group::getByName("Administrators"); ?>
      <?php $gModerators = Group::getByName("Moderators"); ?>
      <?php if($cUser->inGroup($gAdministrators) || $cUser->inGroup($gModerators)) { ?>
        <?php echo $html->css($view->getStylesheet('_loggedin.less'))?>
      <?php } ?>
    <?php } ?>

    <?php if($cPage->isEditMode()) { ?>
      <?php echo $html->css($view->getStylesheet('_edit.less'))?>
    <?php } ?>
    <!-- <script src="<?php //echo $this->getThemePath()?>/js/offers.js"></script> -->
    <!-- <script src="<?php //echo $this->getThemePath()?>/js/repairform.js"></script> -->

    <script>
        if (navigator.userAgent.match(/IEMobile\/10\.0/)) {
            var msViewportStyle = document.createElement('style')
            msViewportStyle.appendChild(
                document.createTextNode(
                    '@-ms-viewport{width:auto!important}'
                )
            )
            document.querySelector('head').appendChild(msViewportStyle)
        }
    </script>
    <!-- [if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif] -->
  </head>
  <body class="hold-transition skin-blue-light sidebar-mini">
  <div class="wrapper">
    <div class="main">
      <div class="container c-padding"><br><br><br><br>
