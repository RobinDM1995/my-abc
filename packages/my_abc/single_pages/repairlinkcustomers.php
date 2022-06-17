<?php
// print_r($json = json_encode($formData));
// print_r(json_decode($json));
// print_r($formData);
$jsonData = json_encode($formData);
// echo '<pre>';
// print_r($jsonData);
// exit;

?>
<h1>Link herstelformulier opstellen</h1>
<form class="formcontrol" action="/en/repairlinkcustomers/sendLink" method="post">
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title"><i class="fa fa-user" style="margin-right:5px;"></i>Klant info</h3>
    </div>
    <div class="box-body">
        <input type="hidden" name="language" value="<?= $formData['lang']?>">
        <input type="hidden" name="hash" value="<?= $formData['hash']?>">
        <input type="hidden" name="jsonData" value='<?= $jsonData?>'>

        <div class="row">
          <div class="col-md-12">
            <p><strong>Bedrijfsnaam:</strong> <?= $formData['compName']?></p>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <p><strong>Voornaam:</strong> <?= $formData['firstname']?></p>
          </div>
          <div class="col-md-6">
            <p><strong>Familienaam:</strong> <?= $formData['lastname']?></p>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <p><strong>E-mail adres:</strong> <?= $formData['email']?></p>
          </div>
          <div class="col-md-6">
            <p><strong>Telefoon nummer:</strong> <?= $formData['phone']?></p>
          </div>
        </div>
      </div>
    </div> <!-- BOX PRIMARY -->

    <div class="box box-default">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-file" style="margin-right:5px;"></i>Product info</h3>
      </div>
      <div class="box-body">

      <div class="row">
        <div class="col-md-6">
          <p><strong>Merknaam:</strong> <?= $formData['brand']?></p>
        </div>
        <div class="col-md-6">
          <p><strong>Product code:</strong> <?= $formData['code']?></p>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <p><strong>Prioriteit:</strong> <?= $formData['priority']?></p>
        </div>
        <div class="col-md-6">
          <p><strong>Referentie klant:</strong> <?= $formData['ref']?></p>
        </div>
      </div>

      <div class="row">
        <div class="col-md-12">
          <p><strong>Foutomschrijving:</strong> <?= $formData['desc']?></p>
        </div>
      </div>

      </div>
    </div> <!-- BOX DEFAULT -->
    <input type="submit" class="btn btn-abc" name="sendlink" value="Verstuur link">
    <input type="submit" class="btn btn-abc-grey" name="testlink" value="Test link" formaction="/en/repairlinkcustomers/testLink" formtarget="_blank">
  </form>
