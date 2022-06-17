<div class="row">
  <div class="col-md-6">
    <h1 style="margin:0"><?= t('Herstelaanvraag');?></h1>
    <h4 style="margin-bottom: 40px"><?= t('Dit neemt maar een paar minuten in beslag.');?></h4>
  </div>
  <div class="col-md-6">
    <img src="<?= $this->getThemePath()?>/img/abcparts_logo.png" alt="Logo ABC" style="max-width:250px; float:right;">
  </div>
</div>

<div>
  <?php
  //main content
  $a = new Area('Main');
  // $afr = new Area('Mainfr');
  // $aen = new Area('Mainen');

  // switch($lang){
  //   case 'nl':
  //   default:
    $a->display($c);
  //   break;
  //
  //   case 'fr':
  //   $afr->display($c);
  //   break;
  //
  //   case 'en':
  //   $aen->display($c);
  //   break;
  // }
  ?>
</div>
