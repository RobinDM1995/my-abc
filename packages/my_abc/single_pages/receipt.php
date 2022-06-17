<?php
  // echo '<pre>';
  // print_r($custData);
  // exit;
?>
<page>

</page>
<div class="receipt">
  <div class="row">
    <div class="col-md-4">
      <img style="width:100%; margin-top:10px;" src="<?= $this->getThemePath()?>/img/abcparts_logo.png" alt="logo_abc">
    </div>
    <div class="col-md-8">
      <h2 class="text-right abcgreen"><?= t('Acknowledgement of receipt');?></h2>
    </div>
  </div>
  <div class="whitespaceb"></div>
  <div class="row">
    <div class="col-md-12">
      <p><?= t('The undersigned ') . '<strong>' .$custData['contact'] . '</strong>';?></p>
      <p><?= t('Domiciled in ') . '<strong>' . $custData['streetNr'] . ' ' . $custData['zipcode'] . ' - ' . $custData['city'] . '</strong>';?></p>
      <p><?= t('Acting in name and for account of')?></p>
    </div>
  </div>
  <div class="whitespace"></div>
  <div class="row">
    <div class="col-md-2">
    </div>
    <div class="col-md-10">
      <p><strong><?= $custData['compName'] . '<br>' . $custData['compStreetNr'] . '<br>' . $custData['compZipcode'] . ' - ' . $custData['compCity']?></strong></p>
    </div>
  </div>
<div class="whitespace"></div>
  <div class="row">
    <div class="col-md-12">
      <p><?= t('Declares')?>:</p>
      <p>&bull; <?= t('To have received and picked up the following goods (being the subject of invoice N° <strong>%1$s</strong> d.d. <strong>%2$s</strong>) on <strong>%3$s</strong>', $custData['invoiceNr'], $custData['invoiceDate'], date('d/m/Y'))?></p>
      <p><strong><?= $custData['desc']?></strong></p>
      <p>&bull; <?= t('Destined to a location outside the community i.e. ') . '<strong>' . $custData['country'] . '</strong>'?></p>
    </div>
  </div>
<div class="whitespace"></div>
  <div class="row">
    <div class="col-md-6">
      <p><?= t('Drawn up in ') . '<strong>'. $custData['country'] . '</strong>'?></p>
      <p><?= t('On ') . '<strong>' . date('d/m/Y') . '</strong>'?></p>
    </div>
    <div class="col-md-6">
      <p><?= t('Signature,')?></p>
      <img src="<?= $custData['sign']?>" alt="signature">
      <p><strong><?= t('Signed on:') . '</strong> ' . date('d/m/Y H:i:s')?></p>
      <p><strong><?= t('IP-address:') . '</strong> ' . $userIP?></p>
    </div>
  </div>
</div> <!--receipt-->
