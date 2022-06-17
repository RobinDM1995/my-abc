<?php
defined('C5_EXECUTE') or die(_("Access Denied."));

$form = Loader::helper('form');
// echo $disable;
// exit;

if($token != 'TestToken987654321'){
  echo 'Token invalid';
  exit;
}
?>
  <?php
  if($filled == 'filled'){?>
    <div class="callout callout-info lead">
      <h4><i class="fa fa-times" style="margin-right: 7px;"></i><?php echo t('Already accepted');?></h4>
      <p style="margin-left: 25px;">
        <?php echo t('The offer for RP number: %1$s has already been accepted', $rpnumber);?><br>
        <?php echo t('By:');?><br />
        <?php echo $name . t(' on ') . $datum?>
    </div>
  <?php } ?>
  <div class="row">
    <div class="col-md-10 col-md-offset-1">
      <div class="pagebackground">
        <form id="transportForm" <?php echo 'action="' . $link . '"';?> method="post">
          <div class="row">
              <div class="col-md-6 col-sm-7">
                <div class="header">
                  <img class="logo" src="<?php echo $this->getThemePath();?>/img/ABC_Industrial_Parts600.png" alt="logo abc">
                </div>
              </div>
              <div class="col-md-6 col-sm-5">
                <div class="header">
                  <h2><?php echo '<strong>' . (t('Repair quote') . " <span class='rpnumber'>" . $rpnumber) .'</span></strong>';?></h2><br>
                  <?php
                    echo '<strong>' . $compName . '</strong><br>';
                    echo $custName . '<br>';
                  ?>
                </div>
              </div>
          </div>

          <div class="row">
              <div class="col-md-5 col-sm-7 col-md-offset-1">
                <div class="tabpad">
                  <?php echo t('Date');?>: 20/10/2020
                </div>
              </div>
              <div class="col-md-6 col-sm-5">
                <div class="btwnr">
                  <?php echo t('VAT');?>: BE0423.274.346
                </div>
              </div>
          </div>

          <div class="row">
            <div class="col-md-11 col-md-offset-1 ">
              <div class="tabpad">
                <strong><?php echo t('Your ref.: ');?> Ontvangen door klant op 14/10/2020</strong>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-10 col-md-offset-1">
              <div class="pricedemand tabpad">
                <span class="color"><?php echo t('Thanks for your price demand. You can find the repair price below. <br> Please mention our reference %1$s on all your documents', $rpnumber);?></span>
              </div>
            </div>
          </div>

          <div class="row">
              <div class="col-md-12">
                <div class="border">
                  <strong>B&R - 4PP450.1043-K05</strong><br>
                  <strong><?php echo $itemName;?></strong><br>
                  <strong>s/n <?php echo $serial?></strong>
                </div>
              </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="border">
                <strong><?php echo t('Extra information')?></strong><br>
                De herstelling wordt hier gedeeltelijk functioneel getest. Eindtest in werkelijke omstandigheden nog uit te voeren door de klant na levering.
                In onderstaande prijs voorzien we preventieve controle op componentniveau, revisie, volledige test van de ethernetpoort en reiniging.
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="border-big tabpad">
                <input type="checkbox" name="repair" <?php  if($disable == "disable") echo 'disabled';?> <?php if($repair == 1) echo 'checked';?>> <strong><?php echo t('Repair:')?></strong> (<?php echo t('chosen priority : %1$s', $priority)?>)
                <span class="prijsher"><strong>€981,75</strong></span>
                <span class="leverterm"><strong>+/-2 <?php echo t('weeks')?></strong></span>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="border trans">
                <div class="row">
                  <div class="col-md-12">
                    <i class="fa fa-truck"></i><strong>Transport</strong> (<?php echo t('not included in the above price');?>)<br>
                  </div>
                </div>
                  <div class="row">
                    <div class="col-md-12">
                      <input type="checkbox" id="trans1" name="trans" value="pickup" onclick="chbx(this);" <?php  if($disable == "disable") echo 'disabled';?> <?php if($trans == 'pickup') echo 'checked'?>> <?php echo t('Pick up organised by customer')?>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-5">
                      <input type="checkbox" id="trans2" name="trans" value="transserv" onclick="chbx(this);" <?php  if($disable == "disable") echo 'disabled';?> <?php if($trans == 'transserv') echo 'checked'?>> <?php echo t('Delivery by transport service')?>
                    </div>
                    <div class="col-md-7">
                      <div class="transcost">
                        <?php echo t('< 20kg Netherlands: €35 - Belgium: €25') . '<br>' . t('> 20kg or other countries: price on request');?>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-12 col-sm-6">
                      <input type="checkbox" id="trans3" name="trans" value="custacc" onclick="chbx(this);" <?php  if($disable == "disable") echo 'disabled';?> <?php if($trans == 'custacc') echo 'checked'?>> <?php echo t('Delivery on customers account: ')
                       . t('transporter: ')?> <input type="text" name="transporter" value="<?php echo $transporter;?>" <?php  if($disable == "disable") echo 'disabled';?> class="inputtrans"> <?php echo t('N° account: ')?><input type="text" name="accNr" <?php  if($disable == "disable") echo 'disabled';?> value="<?php echo $accnr?>" class="inputtrans">
                    </div>
                  </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="info">
                <div class="row">
                  <div class="col-md-12">
                    <?php echo t('Mentioned prices in euro excl. VAT during 1 month')?>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <u><?php echo t('Warranty:');?></u>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <?php echo t('1 year');?>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <u><?php echo t('Payment conditions:');?></u>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <?php echo t('Belgium & Netherlands: 14 days net (after credit check) <br> Other countries: prepayment');?>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <u><a href="<?php echo 'https://www.abcparts.be/nl/legal/algemene-voorwaarden'?>" target="_blank"><?php echo t('General sales conditions')?></a></u>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-2">
              <div class="row">
                <div class="col-md-12">
                  <input type="button" name="submit"  <?php  if($disable == "disable") echo 'disabled';?> value="<?php echo t('Accept')?>" id="aanvaarden" class="btn btn-abc extra-field-submit">
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <input type="button" name="submit" value="<?php echo t('Change')?>" id="aanpassing" class="btn btn-abc-custom extra-field-submit">
                </div>
              </div>
            </div>
    <div class="col-md-3">
      <div class="accept">
        <label for="name"><?php echo t('Name:')?></label><br>
        <input type="text" name="name" class="acceptinput" required><br>
        <input type="submit" value="<?php echo t('Accept')?>" class="btn btn-abc extra-field-submit acceptinput" id="acceptbtn">
      </div>
      <div class="feedback">
        <label for="feedback">Feedback:</label><br>
        <textarea name="feedback" rows="3" cols="29" class="feedbackinput"></textarea><br>
        <input type="button" name="change" value="<?php echo t('Change')?>" class="btn btn-abc-custom extra-field-submit feedbackinput" id="feedbackbtn">
      </div>
    </div>
    </div>
  </form>
</div>
</div>
</div>
