<?php
defined('C5_EXECUTE') or die(_("Access Denied."));

$form = Loader::helper('form');


// if($_GET['year']){
//
// }
// echo $adres;
// exit;
?>
<?php if($compName && $review == 'review'){?>
<div class="row">
  <div class="col-md-10 col-md-offset-1">
    <div class="pagebackgroundag">
      <div class="row">
        <div class="col-md-9 col-sm-8 col-xs-6">
          <!-- <button type="button" class="btn" onclick="window:history.go(-1)"><i class="fa fa-arrow-left"></i></button> -->
        </div>
        <div class="col-md-3 col-sm-4 col-xs-6">
          <div class="alignright">
            <p>Kruisem, <?php echo $datum;?></p>
          </div>
        </div>
      </div>
      <br>
      <div class="row">
        <div class="col-md-10 col-md-offset-1">
          <h2><?php echo t('Overeenkomst interventies ter plaatse')?></h2>
          <span class="term"><?php echo t('Geldig van %1$s tot 31/12/', $datum) . $year;?></span>
        </div>
      </div>

        <div class="row">
          <div class="col-md-4 col-md-offset-1 col-sm-6">
            <p><strong><?php echo t('Tussen:');?></strong></p>
            <p>
              ABC Industrial Parts bv <br>
              Kazerneweg 29 <br>
              9770 Kruisem <br>
              <?php echo t('Btw: ')?> BE0888.467.144
            </p>
          </div>
          <div class="col-md-4 col-md-offset-1 col-sm-6">
            <p><strong><?php echo t('En:');?></strong></p>
            <p>
              <?php echo $compName;?><br>
              <?php echo $adres;?><br>
              <?php echo $zipcode . ' ' . $city;?><br>
              <?php echo t('Btw: ') . $vat;?>
            </p>
            <p><?php echo t('Contactpersoon'). ': ' .$contact?></p>
          </div>
        </div>

      <div class="row">
        <div class="col-md-10 col-md-offset-1">
          <h3>1. <?php echo t('Algemene interventievoorwaarden');?></h3>
          <p><?php echo t('In overleg met de klant is het mogelijk om ter plaatse te komen tijdens de kantooruren, dit aan het vooropgestelde dagtarief.');?></p><br>
          <p><?php echo t('Onafhankelijk van het resultaat wordt onderstaande steeds gefactureerd per interventie');?></p>
          <ul class="list">
            <li><?php echo t('Vaste startkost');?>*</li>
            <li><?php echo t('Extra technische voorbereiding / opzoekingswerk');?></li>
            <li><?php echo t('De gepresteerde uren ter plaatse');?>.</li>
            <li><?php echo t('Gebruikte onderdelen');?></li>
            <li><?php echo t('De uren nodig voor de verplaatsing van en naar ABC Industrial Parts bv');?>.</li>
            <li><?php echo t('Een kilometervergoeding heen en weer');?>.</li>
            <li><?php echo t('Eventuele extra transportkosten, bv tunnelkosten');?></li>
            <li><?php echo t('Eventuele overnachtingskosten');?></li>
          </ul>
          <p>* <?php echo t('Dit betreft het verzamelen van het nodige werkmateriaal, het inladen van de wagen, administratieve afhandeling van het dossier')?> ...</p>

          <h3>2. <?php echo t('Uitzonderlijke interventievoorwaarden');?></h3>
          <p><?php echo t('In overleg met de klant is het mogelijk om ter plaatse te komen buiten de kantooruren, dit aan het uitzonderlijke nacht-weekendtarief.');?></p><br>
          <ul class="list">
            <li><?php echo t('Bovenstaande algemene interventievoorwaarden blijven van toepassing.');?></li>
            <li><?php echo t('Prestaties buiten de kantooruren zijn enkel mogelijk na overleg met ABC.');?></li>
            <li><?php echo t('Onder interventies buiten de kantooruren verstaan we interventies die plaatsvinden tussen 19 uur en 7 uur op weekdagen en interventies die uitgevoerd worden tijdens het weekend of op feestdagen.');?></li>
            </ul>
        </div>
      </div>
      <!-- <table class="table"></table> -->
      <div class="row">
        <div class="col-md-10 col-md-offset-1">
          <h3>3. <?php echo t('Tarieven');?></h3>
          <div class="col-md-6 col-xs-8 border-green">
            <p><strong><?php echo t('Dagtarief / uur');?></strong></p>
          </div>
          <div class="col-md-6 col-xs-4 border-green">
            <p>€ <?php echo $dagtarief;?> / h</p>
          </div>
          <div class="col-md-6 col-xs-8 bg-success border-green">
            <p><strong><?php echo t('Nacht-weekendtarief / uur');?></strong></p>
          </div>
          <div class="col-md-6 col-xs-4 bg-success border-green">
            <p>€ <?php echo $nachttarief;?> / h</p>
          </div>
          <div class="col-md-6 col-xs-8 border-green">
            <p><strong><?php echo t('Vaste startkost');?></strong></p>
          </div>
          <div class="col-md-6 col-xs-4 border-green">
            <p>€ <?php echo $startfee;?></p>
          </div>
          <div class="col-md-6 col-xs-8 bg-success border-greenL">
            <p><strong><?php echo t('Kilometervergoeding');?></strong></p>
          </div>
          <div class="col-md-6 col-xs-4 bg-success border-greenL">
            <p>€ <?php echo $kilometervergoeding;?> / km</p>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-10 col-md-offset-1">
          <h3>4. <?php echo t('Kantooruren');?></h3>

          <div class="col-md-6 col-xs-4 border-green">
            <p><strong><?php echo t('Maandag');?></strong></p>
          </div>
          <div class="col-md-6 col-xs-8 border-green">
            <p><?php echo t('8 uur - 18 uur (doorlopend)');?></p>
          </div>
          <div class="col-md-6 col-xs-4 bg-success border-green">
            <p><strong><?php echo t('Dinsdag');?></strong></p>
          </div>
          <div class="col-md-6 col-xs-8 bg-success border-green">
            <p><?php echo t('8 uur - 18 uur (doorlopend)');?></p>
          </div>
          <div class="col-md-6 col-xs-4 border-green">
            <p><strong><?php echo t('Woensdag');?></strong></p>
          </div>
          <div class="col-md-6 col-xs-8 border-green">
            <p><?php echo t('8 uur - 18 uur (doorlopend)');?></p>
          </div>
          <div class="col-md-6 col-xs-4 bg-success border-green">
            <p><strong><?php echo t('Donderdag');?></strong></p>
          </div>
          <div class="col-md-6 col-xs-8 bg-success border-green">
            <p><?php echo t('8 uur - 18 uur (doorlopend)');?></p>
          </div>
          <div class="col-md-6 col-xs-4 border-greenL">
            <p><strong><?php echo t('Vrijdag');?></strong></p>
          </div>
          <div class="col-md-6 col-xs-8 border-greenL">
            <p><?php echo t('8 uur - 17.30 uur (doorlopend)');?></p>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-10 col-md-offset-1">
          <h3>5. <?php echo t('Uitstel van interventie ter plaatse');?></h3>
          <p><?php echo t('ABC Industrial Parts bv is in de mogelijkheid om interventies ter plaatse uit te stellen/te verplaatsen in geval van overmacht.');?></p>

          <h3>6. <?php echo t('Voorafbetaling van de interventie ter plaatse');?></h3>
          <p><?php echo t('Betaling van de interventie gebeurt steeds na de interventie en na ontvangst van de factuur, dit volgens de vastgelegde betalingsvoorwaarden.')?></p>
          <p><?php echo t('ABC Industrial Parts bv behoudt zich het recht voor om een voorafbetaling te vragen voor de interventie ter plaatse in volgende gevallen:');?></p><br>
          <ul class="list">
            <li><?php echo t('Het betreft een interventie bij een nieuwe klant');?>.</li>
            <li><?php echo t('De kredietverzekering adviseert ABC Industrial Parts bv een voorafbetaling te vragen');?>.</li>
            <li><?php echo t('De klant heeft in het verleden reeds nagelaten om betalingen binnen de afgesproken betalingstermijn te betalen');?>.</li>
            <li><?php echo t('De klant heeft op het moment van de interventie nog openstaande facturen bij ABC Industrial Parts bv');?>.</li>
          </ul>
          <br>
          <p><?php echo t('In geval van voorafbetaling ontvangt u een pro-formafactuur. Onderstaande wordt aangerekend:');?></p>
          <ul class="list">
            <li><?php echo t('Vaste startkost');?></li>
            <li><?php echo t('Kilometervergoeding');?></li>
            <li><?php echo t('Standaardforfait van 8 uren');?></li>
          </ul>
          <p><?php echo t('Pas na ontvangst van een geldig betalingsbewijs of de effectieve betaling op rekening van ABC Industrial Parts bv wordt de interventie uitgevoerd.');?></p><br><br>
          <p><?php echo t('Na uitvoering van de interventie ter plaatse worden de effectieve kosten en uren berekend:');?></p>
          <ul>
            <li><?php echo t('Indien blijkt dat de klant te veel heeft betaald, zal ABC Industrial Parts bv het verschil terugbetalen op dezelfde rekening');?></li>
            <li><?php echo t('indien de pro-formafactuur de gepresteerde uren en kosten niet dekt, wordt er na de interventie een tweede factuur verzonden met het bedrag dat nog dient betaald te worden.');?></li>
          </ul>
        </div>
      </div>

      <div class="row">
        <div class="col-md-10 col-md-offset-1">
          <div class="color">
            <p><?php echo t('Bij akkoord van deze overeenkomst gaat u automatisch akkoord met de <a href="https://www.abcparts.be/nl/legal/algemene-voorwaarden" target="_blank">algemene voorwaarden</a> van ABC Industrial Parts bv.');?></p>
          </div>
        </div>
      </div>

      <form  action="<?php echo $linkSign?>" method="post">
        <div class="row">
          <div class="col-md-10 col-md-offset-1">
            <input type="hidden" name="compName" value="<?php echo $compName?>">
              <input type="hidden" name="adres" value="<?php echo $adres?>">
            <input type="hidden" name="city" value="<?php echo $zipcode . ' ' . $city?>">
            <input type="hidden" name="vat" value="<?php echo $vat?>">
            <input type="hidden" name="contact" value="<?php echo $contact?>">
            <input type="hidden" name="email" value="<?php echo $email?>">
            <p class="color"><?php echo t('Akkoord voor deze overeenkomst wordt beschouwd als akkoord van de volledige firma.')?></p>
            <div class="buttons">

              <!-- KLEUREN AANPASSEN -->
              <input type="submit" class="btn btn-abc-custom" value="<?php echo t('Akkoord')?>">
              <input type="submit" class="btn btn-abc decl" value="<?php echo t('Annuleren')?>" formaction="javascript:history.go(-1)">
            </div>
        </div>
        </div>
      </form>


    </div> <!--Pagebackground-->
  </div>
</div>
<?php }elseif($signed == 'signed'){  ?>
  <div class="row">
    <div class="col-md-10 col-md-offset-1">
      <h2><?php echo t('Bedankt');?>!</h2><br><br>
      <p><?php echo t('Bedankt om akkoord te gaan met onze interventievoorwaarden.');?></p><br>
      <p><?php echo t('U ontvangt dadelijk een bevestiging via het e-mailadres ') . '<strong>'. $email .'</strong>'?>.</p>

      <form action="<?php echo $linkConfirm?>" method="post" target="_blank">
        <input type="submit" class="btn btnNext" value="<?php echo t('OVEREENKOMST OPENEN')?>">
      </form>

    </div>
  </div>

  <?php }else{ ?>
  <div class="row">
    <div class="col-md-8">
      <h1 style="margin-bottom: 5px;"><?php echo t('Overeenkomst interventies ter plaatse ') . $year?></h1>
      <h4><?php echo t('Gelieve uw bedrijfsgegevens in te vullen')?></h4>
    </div>
    <div class="col-md-4">
      <img src="<?= $this->getThemePath()?>/img/abcparts_logo.png" alt="Logo ABC" style="max-width:250px; float:right;">
    </div>
  </div>
  <br>
  <form action="<?php echo $linkReview?>" method="post">
    <div class="row">
      <div class="col-md-12">
        <div class="box  box-primary" style="border:0;">
          <div class="box-header">
          </div>
          <div class="box-body">
            <div class="row">
              <div class="col-md-6">
                <?php
                  print $form->text('compName', array('required' => 'required', 'placeholder' => t('Bedrijfsnaam')));
                ?>
              </div>
              <div class="col-md-6">
                <?php
                  print $form->text('contact', array('required' => 'required', 'placeholder' => t('Contactpersoon')));
                ?>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <?php
                  print $form->text('street', array('required' => 'required', 'placeholder' => t('Straat + nummer')));
                ?>
              </div>
              <div class="col-md-6">
                <?php
                  print $form->text('email', array('required' => 'required', 'placeholder' => t('E-mail')));
                ?>
              </div>

            </div>
            <div class="row">
              <div class="col-md-2">
                <?php
                  print $form->text('zipcode', array('required' => 'required', 'placeholder' => t('Postcode')));
                ?>
              </div>
              <div class="col-md-4">
                <?php
                  print $form->text('city', array('required' => 'required', 'placeholder' => t('Stad')));
                ?>
              </div>
              <div class="col-md-6">
                <?php
                  print $form->text('vat', array('required' => 'required', 'placeholder' => t('Btw')));
                ?>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <?php print $form->submit('submit', t('Volgende'), array('class' => 'btn btn-abc btnNext'));?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
<?php } ?>
