<?php
 ?>
  <div class="row">
    <div class="col-md-8">
      <h1><?= t('Bedankt om je herstelaanvraag in te dienen')?></h1>
    </div>
    <div class="col-md-4">
      <img src="<?= $this->getThemePath()?>/img/abcparts_logo.png" alt="Logo ABC" style="max-width:250px; float:right;">
    </div>
  </div>

<div class="row">
  <div class="col-md-12">
    <div class="box box-primary" style="border-top: 0;">
      <div class="box-header">
      </div>
      <div class="box-body">
        <h4><?= t('Je krijgt zodra een bevestigingsmail.')?></h4>
        <table border="0" cellpadding="0" cellspacing="0" style="width:50%;border-spacing:0; padding:5px; background:#F2F7FB; color:black;">
							<tbody>
								<tr>
									<td>
									<p style="text-align: left; font-size:14px; color:#74b843; margin:15px; margin-bottom: 30px;"><strong>Zo verstuur je jouw herstelling naar ABC:</strong></p>

									<ol>
										<li style="text-align: left;"><strong>Print de bevestigingsmail uit</strong>. Geen printer in de buurt?<br />
										Schrijf dan het referentienummer op een gewoon blad papier.<br />	&nbsp;</li>
										<li style="text-align: left;"><strong>Voeg de mail bij de herstelling</strong>, of indien je geen printer in de buurt had, het referentienummer dat je hebt genoteerd.<br />
										&nbsp;</li>
										<li style="text-align: left;"><strong>3.	Bezorg je herstelling volgens de gekozen optie. </strong></li>
									</ol>
									</td>
								</tr>
							</tbody>
						</table>
            <br>
        <form action="/nl/repairform" method="post">
          <input type="submit" name="back" class="btn btn-abc" value="<?= t('Een nieuwe aanvraag maken')?>">
        </form>
      </div>
    </div>
  </div>
</div>
