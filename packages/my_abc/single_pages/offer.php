<?php
defined('C5_EXECUTE') or die(_("Access Denied."));

$form = Loader::helper('form');
// echo '<pre>';
// print_r($exchangebudget);
// exit;
?>
<script type="text/javascript">
window.onload = function(){
  var orderID = <?= json_encode($orderID)?>;
  var typeOffer = document.getElementById('orderid-367');

  var repairPrice = <?= $repairPrice?>;
  var priority = "<?= $priority?>";
  var deliveryPeriod = "<?= $deliveryPeriod?>";
  var options = <?= json_encode($optionArr);?>;
  var optionDiv = document.getElementsByClassName('optiondiv');

  var transOpt = <?= json_encode($transdata);?>;
  var transDiv = document.getElementsByClassName('transdiv');

  var exchangebudget = document.getElementById('exchangebudget');
  var availablebudget = <?= $exchangebudget;?>;
  // console.log(exchangebudget.textContent);
  exchangebudget.innerHTML = 'You have €' + availablebudget['available'] + ' available';
  //We need orderID to determine which options should be shown
  typeOffer.value = orderID;

  //make a radio button for each option
  // if(orderID.includes('SO')){
  //   for(var i = 0; i < options.length; i++){
  //     var optionVal = options[i]['option'];
  //     var optionText = options[i]['option'] + ' €' + options[i]['price'] + ' delivery period ' + options[i]['deltime'] + ' weeks';
  //     var optionPara = "'"+options[i]['option']+"'," + options[i]['price'];
  //
  //     optionDiv[0].innerHTML += '<label for="salesOpt'+i+'" style="padding-left:25px;"><input type="radio" name="salesRadio" id="salesOpt'+i+'" value="'+optionVal+'" style="margin-left:-20px;" onclick="getOptionValue('+optionPara+')">'+optionText+'</label><br>';
  //   }
  // }
  if(orderID.includes('RP')){
    optionDiv[0].innerHTML += '<label for="repairOpt" style="padding-left:25px;"><input type="radio" name="repairRadio" id="repairOpt" value="" style="margin-left:-20px;" onclick="getOptionValue(\'Repair\', '+repairPrice+')">Repair: (chosen priority: '+priority +' €'+ repairPrice +' '+ deliveryPeriod +')</label><br>'
  }
  // //make a radio btn for each transport option
  for(var i = 0; i < transOpt.length; i++){
    var transVal = transOpt[i]['title'];
    // var transPara;
    transDiv[0].innerHTML += '<label for="transopt-'+i+'" style="padding-left:25px;"><input type="radio" name="transRadio" id="transopt-' + i +'" value="'+transOpt[i]['title']+'" required style="margin-left: -20px;" onclick="getTransValue(\''+ transOpt[i]['value'] +'\', 1234)">' + transOpt[i]['title']+ '</label><br>';
  }
}

function getOptionValue(value, price){
  //If an option radio button is checked insert value into hidden formidable field
  var hiddenSale = document.getElementById('hiddenoption-335');
  var hiddenPrice = document.getElementById('hiddenprice-371');

  hiddenSale.value = value;
  hiddenPrice.value = price;
}

function getTransValue(value, price){
  // console.log(value);
  // console.log(price);
  var hiddenTrans = document.getElementById('transopt-337');
  var hiddenTransPrice = document.getElementById('hiddentransprice-372');

  hiddenTrans.value = value;
  hiddenTransPrice.value = price;
}
</script>

  <?php
  $a = new Area('Main');
  $a->display($c);
  /*
  if($filled == 'filled'){?>
    <div class="callout callout-info lead">
      <h4><i class="fa fa-times" style="margin-right: 7px;"></i><?php echo t('Already accepted');?></h4>
      <p style="margin-left: 25px;">
        <?php echo t('The offer: %1$s has already been accepted', $orderID);?><br>
        <?php echo t('By:');?><br />
        <?php echo $name . t(' on ') . $datum?>
    </div>
  <?php } ?>
  <div class="row">
    <div class="col-md-10 col-md-offset-1">
      <div class="pagebackground">
        <form id="transportForm" <?php echo 'action="' . $linkCon . '"';?> method="post">
          <div class="row">
              <div class="col-md-6 col-sm-7">
                <div class="header">
                  <img class="logo" src="<?php echo $this->getThemePath();?>/img/ABC_Industrial_Parts600.png" alt="logo abc">
                </div>
              </div>
              <div class="col-md-6 col-sm-5">
                <div class="header">
                  <h2><strong><?php echo $offerTitle;?> <span class="rpnumber"><?php echo $orderID;?></span></strong></h2>
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
                  <?php echo t('Date');?>: 20/10/2020 <?php //variabel maken als ik de gegevens heb?>
                </div>
              </div>
              <div class="col-md-6 col-sm-5">
                <div class="btwnr">
                  <?php echo t('VAT') . ': ' . $vat;?>
                </div>
              </div>
          </div>

          <div class="row">
            <div class="col-md-11 col-md-offset-1 ">
              <div class="tabpad">
                <strong><?php echo t('Your ref.: ');?> Ontvangen door klant op 14/10/2020</strong> <?php //variabel maken als ik de gegevens heb?>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-10 col-md-offset-1">
              <div class="pricedemand tabpad">
                <span class="color"><?php echo t($priceDemand, $orderID)?></span>
              </div>
            </div>
          </div>

          <?php
            if(substr($orderID, 0, 2) == 'RP'){
          ?>
          <div class="row">
              <div class="col-md-12">
                <div class="itemInfo border">
                  <div class="col-md-10 col-sm-9 col-xs-8">
                    <strong><?php echo $brand . ' - ' . $itemcode?></strong><br>
                    <strong><?php echo $itemName;?></strong><br>
                    <strong>s/n <?php echo $serial?></strong>
                  </div>
                  <div class="col-md-2 col-sm-3 col-xs-4">
                    <img class="itemimg"<?php echo '<img src="data:image/jpeg;base64,' . $itemImg . '"'?>>
                  </div>
                </div>
              </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="border">
                <strong><?php echo t('Extra information')?></strong><br> <?php echo $analysis?>

              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="border-big tabpad">
                <input type="checkbox" name="repair" <?php  echo $disabled;?> <?php if($repair == 1) echo 'checked';?>> <strong><?php echo t('Repair:')?></strong> (<?php echo t('chosen priority : %1$s', $priority)?>)
                <span class="prijsher"><strong>€981,75</strong></span> <?php //variabel maken als ik de gegevens heb?>
                <span class="leverterm"><strong> <?php echo $deliveryPeriod?></strong></span>
              </div>
            </div>
          </div>
          <?php
            }else{
          ?>

          <div class="row">
            <div class="col-md-12">
              <div class="borderitem">
                <div class="row">
                  <div class="col-md-12">
                    <div class="paditem">
                      <strong><span class="padtran"><?php echo $brand . ' - ' . $itemcode?> - Touch Panel : TP1200 Comfort</span></strong> <?php //variabel maken als ik de gegevens heb?>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-3 col-sm-3 col-xs-5">
                    <span class="padtran"><?php echo t('Option')?></span>
                  </div>
                  <div class="col-md-2 col-sm-2 col-xs-3">
                    <?php echo t('Quantity')?>
                  </div>
                  <div class="col-md-2 col-sm-2 col-xs-3">
                    <?php echo t('Price/Unit')?>
                  </div>
                  <div class="col-md-4 col-sm-3 col-xs-3">
                    <div class="hidden-xs">
                      <?php echo t('Delivery time')?>
                    </div>
                  </div>
                </div>
                <?php for($i = 0; $i < count($optionArr); $i++){?>
                  <?php if($optionArr[$i]['price'] == $bestOffer){
                    ?>
                    <div class="row lowest">
                    <?php
                  }else{
                    ?>
                    <div class="row">
                      <?php
                  }?>
                    <div class="col-md-3 col-sm-3 col-xs-5">
                      <input type="checkbox" name="saletype[]" <?php echo 'value="' . strtolower($optionArr[$i]['option']). ',' . $optionArr[$i]['price'] .'"'; if(strpos($saletype, strtolower($optionArr[$i]['option'])) !== false) echo "checked";?>
                      <?php  echo $disabled;?> >
                      <strong><?php echo t($optionArr[$i]['option']);?></strong>
                    </div>
                    <div class="col-md-2 col-sm-2 col-xs-3">
                      <?php echo $optionArr[$i]['quantity'];?>
                    </div>
                    <div class="col-md-2 col-sm-2 col-xs-3">
                      <strong>€ <?php echo number_format($optionArr[$i]['price'], 2, '.', '');?></strong>
                    </div>
                    <div class="col-md-4 col-sm-3 col-xs-12">
                      <div class="visible-xs padtran">
                        <?php echo t('Delivery time: ') . $optionArr[$i]['deltime'] . t('week')?>
                      </div>
                      <div class="hidden-xs">
                        <?php echo $optionArr[$i]['deltime'] . t('week')?>
                      </div>
                    </div>
                  </div>
                <?php }?>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">

            </div>
            <div class="col-md-6">
              <div class="border-big">
                  <?php echo t('Our best proposal:')?> <span class="pad"><strong><?php echo t('Total') . ' € ' . number_format($bestOffer,2,'.','')?> </strong></span>
              </div>
            </div>
          </div>
          <?php
            }
          ?>

          <div class="row">
            <div class="col-md-12">
              <div class="border trans">
                <div class="row">
                  <div class="col-md-12">
                    <?php echo $transportTitle;?>
                  </div>
                </div>
                <?php foreach($transdata as $data){
                  ?>
                  <div class="row">
                    <div class="col-md-6">
                      <!--checkbox + title-->
                      <input type="checkbox" name="trans" <?php echo 'value="' . $data['value'] .'"'; if($trans == $data['value']) echo 'checked';?> <?php echo $disabled?> class="check">
                      <?php echo $data['title'];?>
                    </div>
                    <div class="col-md-6">
                      <!--desc1-->
                      <?php echo $data['desc1'];?>
                    </div>
                  </div>
                  <?php if($data['desc2'] != ''){
                    ?>
                    <div class="row">
                      <div class="col-md-6 col-md-offset-6">
                        <!--desc2-->
                        <?php echo $data['desc2'];?>
                      </div>
                    </div>
                    <?php
                  }?>
                  <?php
                }?>
              </div>
            </div>
          </div>

          <?php if(substr($orderID, 0, 2) == 'SO'){?>
            <div class="row">
              <div class="col-md-6">

              </div>
              <div class="col-md-6">
                <div class="border-big-so">
                    <?php echo t('Your chosen options')?> <span class="pad"><strong><?php echo t('Total:') . ' €' .$totaalprijs?></strong></span>
                </div>
              </div>
            </div>
          <?php } ?>

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
              <div class="pdf">
                <div class="row">
                  <div class="col-md-12">
                    <a <?php echo 'href="' . $linkPdf . '"';?>><i class="fa fa-file-pdf-o"></i></a>
                  </div>
                </div>
              </div>
            </div>
    <div class="col-md-3">
      <div class="accept">
        <label for="name"><?php echo t('Name:')?></label><br>
        <input type="text" name="name" class="acceptinput" required><br>
        <input type="submit" name="confirm" value="<?php echo t('Accept')?>" class="btn btn-abc extra-field-submit acceptinput" id="acceptbtn">
      </div>
    </form>
    <form <?php echo 'action="' . $linkFeed . '"';?> method="post">
      <div class="feedback">
        <label for="feedback">Feedback:</label><br>
        <textarea name="feedback" rows="3" cols="29" class="feedbackinput"></textarea><br>
        <input type="submit" name="change" value="<?php echo t('Change')?>" class="btn btn-abc-custom extra-field-submit feedbackinput" id="feedbackbtn">
      </div>
    </form>
    </div>
    </div>
  <div class="row">
    <div class="col-md-12">
      <img class="footer" src="<?php echo $this->getThemePath();?>/img/footer_800.jpg" alt="footer abc">
    </div>
  </div>
</div>
</div>
</div>
*/?>
