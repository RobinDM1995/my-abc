<?php
defined('C5_EXECUTE') or die(_("Access Denied."));

// error reporting nothing due to possible curl warnings
error_reporting(0);
//$ih = Loader::helper('concrete/interface');

function file_get_contents_curl($url)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}

$page = null;
$pageUrl = $_SERVER['HTTP_REFERER'];
$parts = parse_url($_SERVER['HTTP_REFERER']);
if($query['cID'] != ''){
  $nh = Loader::helper('navigation');
  $page = Page::GetByID($query['cID']);
  $pageUrl = $nh->getCollectionURL($page);
}

$html = file_get_contents_curl($pageUrl);

//parsing begins here:
$doc = new DOMDocument();
@$doc->loadHTML($html);
$nodes = $doc->getElementsByTagName('title');

//get and display what you need:
$title = $nodes->item(0)->nodeValue;

$metas = $doc->getElementsByTagName('meta');

for ($i = 0; $i < $metas->length; $i++)
{
    $meta = $metas->item($i);
    if($meta->getAttribute('name') == 'description')
      $description = $meta->getAttribute('content');
    if($meta->getAttribute('name') == 'keywords')
      $metakeywords = $meta->getAttribute('content');
}

$h1counter = 0;
$h1Values = "";
foreach ($doc->getElementsByTagName('h1') as $h1) {
    $h1counter++;
    $h1Values .= strtolower($h1->textContent) . ' ';
}

$h2counter = 0;
$h2Values = "";
foreach ($doc->getElementsByTagName('h2') as $h2) {
    $h2counter++;
    $h2Values .= strtolower($h2->textContent) . ' ';
}

$h3counter = 0;
foreach ($doc->getElementsByTagName('h3') as $h3) {
    $h3counter++;
}

$imgArr = array();
$images = $doc->getElementsByTagName("img");
foreach ($images as $image) {
  $intArr = array();
  $intArr['src'] = $image->getAttribute( 'src' );
  $intArr['alt'] = $image->getAttribute( 'alt' );
  $intArr['title'] = $image->getAttribute( 'title' );
  $imgArr[] = $intArr;
}

//keyword density start
$obj = new MultiKeywordDensity(); // New instance
$obj->domain = $pageUrl; // Define Domain

$multikeywords = $obj->multiresult();

$common_words = "i,he,she,it,and,me,my,you,the,voor,de,het,een,als,mijn,over,deze,onze,naar,le,la,du,un,une,moi,toi,votre,notre";
$common_words = strtolower($common_words);
$common_words = explode(",", $common_words);

$searchWords = array();

if(!empty($title) && !empty($description) && !empty($h1Values)) {
  $titleArr = explode(' ', strtolower($title));
  $descriptionArr = explode(' ', strtolower($description));
  $descriptionArr = array_unique($descriptionArr);
  $h1Arr = explode(' ', trim($h1Values));
  $h1Arr = array_unique($h1Arr);
  foreach($descriptionArr as $value) {
    $common = false;
    $value = strtolower(trim($value));
    if (strlen($value) > 2){
      foreach ($common_words as $common_word){
        if ($common_word == $value){
          $common = true;
        }
      }
      if ($common != true){
        if(in_array($value, $titleArr) && in_array($value, $h1Arr)) {
          $searchWords[] = $value;
        } else {
          if(!empty($h2Values)) {
            $h2Arr = explode(' ', trim($h2Values));
            $h2Arr = array_unique($h2Arr);
            if(in_array($value, $titleArr) && in_array($value, $h2Arr)) {
              $searchWords[] = $value;
            }
          }
        }
      }
    }
  }
}

$searchWords = array_unique($searchWords);
$quickSucces = "";
if(!empty($searchWords)) {
  $quickSuccess = t('This page is likely to be found on these word(s)');
  $quickSuccess .= ': ';
  $quickSuccess .= '<strong>' . implode(', ', $searchWords). '</strong>';
}

$quickErrors = "";
if(empty($title)) {
  $quickErrors = 'The page title is empty!<br />';
}
if(empty($description)) {
  $quickErrors = 'The page description is empty!<br />';
}
?>

<style>
  .page-analyse { position: relative; }
  .page-analyse .page-analyse-header { z-index: 5; padding-top: 15px; }
  .page-analyse-google { font-size: 14px; }
  .page-analyse-google .google-logo { margin-bottom: 15px; }
  .page-analyse-google .google-result { border: 1px solid #DEDEDE; background-color: #F9F9F9; padding: 15px; margin-bottom: 15px; margin-top: 5px; }
  .page-analyse-google .google-result .google-title { color: #1a0dab; text-decoration: none; font-family: arial, sans-serif; font-size: 18px; }
  .page-analyse-google .google-result .google-link { font-size: 14px; color: #006621; font-family: arial, sans-serif; }
  .page-analyse-google .google-result .google-description { color: #545454; line-height: 1.4; }
  .page-analyse-google .alert { font-size: 14px; border-radius: 0; }
  .page-analyse-google .form-group { margin-bottom: 10px; }
  .page-analyse-advanced .alert { margin-bottom: 0; margin-top: 10px; }
  .page-analyse-advanced .table { margin-bottom: 10px; }
</style>
<div class="page-analyse">
  <div class="page-analyse-header">
    <div class="row">
      <div class="col-sm-6">
        <img src="<?= $_SERVER['CONTEXT_PREFIX'] ?>/packages/abcstart/images/google-logo.png" alt="Google Logo" class="google-logo">
      </div>
      <div class="col-sm-6">
        <div class="page-analyse-buttons pull-right">
          <a href="#" class="btn btn-default btn-header-button" data-class=".page-analyse-google" style="display: none;"><?= t('Google Preview'); ?></a>
          <a href="#" class="btn btn-default btn-header-button" data-class=".page-analyse-advanced"><?= t('Advanced Analyse'); ?></a>
          <a href="#" class="btn btn-info btn-header-button" data-class=".page-analyse-information"><?= t('Information'); ?></a>
          <script>
            $(document).ready(function() {
              $('.btn-header-button').on('click', function(e) {
                e.preventDefault();
                var tab = $(this).data('class');
                $( ".btn-header-button" ).each(function() {
                  $(this).show();
                });
                $( ".page-analyse-tab" ).each(function() {
                  $(this).hide();
                });
                $(this).hide();
                $(tab).show();
              })
            })
          </script>
        </div>
      </div>
    </div>
  </div>
  <hr />
  <div class="page-analyse-content">
    <div class="page-analyse-tab page-analyse-google">
      <div class="row">
        <div class="col-sm-12">
          <div class="google-result">
            <div class="row">
              <div class="col-sm-8">
                <div class="google-title">
                  <?= $title; ?>
                </div>
                <div class="google-link">
                  <?= $pageUrl; ?>
                </div>
                <div class="google-description">
                  <?php if(strlen($description) > 165) { ?>
                    <?= substr($description, 0, strrpos(substr($description, 0, 165), ' ')); ?>...
                  <?php } else { ?>
                    <?= $description; ?>
                  <?php } ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php  ?>
      <div class="edit-page-errors" style="<?= (!empty($quickErrors) ? "" : "display: none;"); ?>">
        <div class="row">
          <div class="col-sm-12">
            <div class="alert alert-danger">
              <?= $quickErrors; ?>
            </div>
          </div>
        </div>
      </div>
      <div class="edit-page-success" style="<?= (!empty($quickSuccess) ? "" : "display: none;"); ?>">
        <div class="row">
          <div class="col-sm-12">
            <div class="alert alert-success">
              <?= $quickSuccess; ?>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-12">
          <form method="post" action="<?= $this->action('edit_page'); ?>" id="formEditPage">
            <div class="form-group">
              <label for="metaTitle" class="control-label"><?= t('Title'); ?> *</label>
              <?php $pageTitle = substr($title, 0, strrpos($title, '-')); ?>
              <?php $siteTitle = substr($title, strrpos($title, '-')); ?>
              <div class="input-group">
                <input type="text" name="metaTitle" id="metaTitle" class="form-control" required="required" value="<?= trim($pageTitle); ?>" />
                <span class="input-group-addon" id="siteTitle"><?= $siteTitle; ?></span>
              </div>
            </div>
            <div class="form-group">
              <label for="metaDescr" class="control-label"><?= t('Description'); ?> * <small>(<?= t('max. 165 characters'); ?>)</small></label>
              <textarea name="metaDescr" id="metaDescr" class="form-control" required="required"  rows="5"><?= trim($description); ?></textarea>
            </div>
            <div class="form-group">
              <input type="submit" id="metaSubmit" value="<?= t('Submit'); ?>" class="btn btn-success">
            </div>
          </form>
          <script type="text/javascript">
            $(document).ready(function() {
              function cut(n) {
                  return function textCutter(i, text) {
                      var short = text.substr(0, n);
                      if (/^\S/.test(text.substr(n)))
                          return short.replace(/\s+\S*$/, "");
                      return short;
                  };
              }

              $('#formEditPage #metaSubmit').on('click', function(e) {
                e.preventDefault();
                pageID = $('#formEditPage #cID').val();
                metaTitle = $('#formEditPage #metaTitle').val();
                metaDescr = $('#formEditPage #metaDescr').val();

                $.ajax({
                   type: 'post',
                   url: CCM_DISPATCHER_FILENAME + '/google_preview/edit_page',
                   data: {
                      cID: CCM_CID,
                      metaTitle: metaTitle,
                      metaDescr: metaDescr
                   },
                   dataType: 'json',
                   success: function(data) {
                     console.log(data);
                      if(data.length == 0) {
                        $('.page-analyse-google .google-title').empty().html(metaTitle + ' ' + $('#siteTitle').text());
                        $('.page-analyse-google .google-description').empty().html(metaDescr);
                        if(metaDescr.length > 165) {
                          $('.page-analyse-google .google-description').html(cut(165));
                          $('.page-analyse-google .google-description').html($('.page-analyse-google .google-description').html() + "...");
                        }
                        $('.page-analyse-google .edit-page-success .alert').empty().html("<?= t('The page is succesfully updated for Google.') ;?>");
                        $('.page-analyse-google .edit-page-success').show();
                        $('.page-analyse-google .edit-page-errors').hide();
                      } else {
                        var errorMsg = "";
                        jQuery.each(data, function(index, item) {
                          errorMsg += item.message + "<br />";
                        });
                        $('.page-analyse-google .edit-page-errors .alert').empty().html(errorMsg);
                        $('.page-analyse-google .edit-page-errors').show();
                        $('.page-analyse-google .edit-page-success').hide();
                      }
                   }
                });
              })
            })
          </script>
        </div>
      </div>
    </div>
    <div class="page-analyse-tab page-analyse-advanced" style="display: none;">
      <div class='row'>
        <div class='col-md-3'>
          <label class="control-label"><?= t('Title'); ?></label>
        </div>
        <div class='col-md-7'>
          <?= $title; ?>
        </div>
        <div class='col-md-2'>
          <div class="pull-right">
            <?php if(isset($title) && $title != ''){ ?>
              <?php if(strlen($title) < 75){ ?>
                <span class="label label-success"><?= t('PASSED'); ?></span>
              <?php }else{ ?>
                <span class="label label-warning"><?= t('WARNING'); ?></span>
              <?php } ?>
            <?php } else { ?>
              <span class="label label-danger"><?= t('FAILED'); ?></span>
            <?php } ?>
          </div>
        </div>
      </div>
      <div class='row'>
        <div class='col-md-12'>
          <?php if(isset($title) && $title != ''){ ?>
            <?php if(strlen($title) < 75){ ?>
              <div class="alert alert-success">
                <?= t('Great, this page has a title.'); ?>
              </div>
            <?php } else { ?>
              <div class="alert alert-warning">
                <?= t('This page has a title but contains to much characters. A title should have maximum 75 characters (including spaces).'); ?>
              </div>
            <?php } ?>
          <?php } else { ?>
            <div class="alert alert-warning">
              <?= t('Page title is empty!'); ?>
            </div>
          <?php } ?>
        </div>
      </div>
      <hr/>
      <div class='row'>
        <div class='col-md-3'>
          <label class="control-label"><?= t('Description'); ?></label>
        </div>
        <div class='col-md-7'>
          <?php if(strlen($description) > 165) { ?>
            <?= substr($description, 0, strrpos(substr($description, 0, 165), ' ')); ?>...
          <?php } else { ?>
            <?= $description; ?>
          <?php } ?>
        </div>
        <div class='col-md-2'>
          <div class="pull-right">
            <?php if(isset($description) && $description != ''){ ?>
              <?php if(strlen($description) < 165){ ?>
                <span class="label label-success"><?= t('PASSED'); ?></span>
              <?php }else{ ?>
                <span class="label label-warning"><?= t('WARNING'); ?></span>
              <?php } ?>
            <?php } else { ?>
              <span class="label label-danger"><?= t('FAILED'); ?></span>
            <?php } ?>
          </div>
        </div>
      </div>
      <div class='row'>
        <div class='col-md-12'>
          <?php if(isset($description) && $description != ''){ ?>
            <?php if(strlen($description) < 165){ ?>
              <div class="alert alert-success">
                <?= t('Excellent, the page has a description.'); ?>
              </div>
            <?php }else{ ?>
              <div class="alert alert-warning">
                <?= t('The page has a description but it has to many characters. A description should be maximum 165 characters (including spaces).'); ?>
              </div>
            <?php } ?>
          <?php }else{ ?>
            <div class="alert alert-danger">
              <?= t('The description of the page is missing. A description is very important for search engines.'); ?>
            </div>
          <?php } ?>
        </div>
      </div>
      <hr/>
      <div class='row'>
        <div class='col-md-3'>
          <label class="control-label"><?= t('Headings'); ?></label>
        </div>
      </div>
      <div class='row'>
        <div class='col-md-10'>
          <label><?= t('Headings'); ?> 1</label>
        </div>
        <div class='col-md-2'>
          <div class="pull-right">
            <?php if($h1counter != 0){ ?>
              <?php if($h1counter == 1){ ?>
                <span class="label label-success"><?= t('PASSED'); ?></span>
              <?php }else{ ?>
                <span class="label label-warning"><?= t('WARNING'); ?></span>
              <?php } ?>
            <?php } else { ?>
              <span class="label label-danger"><?= t('FAILED'); ?></span>
            <?php } ?>
          </div>
        </div>
      </div>
      <div class='row'>
        <div class='col-md-10'>
          <label><?= t('Headings'); ?> 2</label>
        </div>
        <div class='col-md-2'>
          <div class="pull-right">
            <?php if($h2counter != 0){ ?>
              <span class="label label-success"><?= t('PASSED'); ?></span>
            <?php }else{ ?>
              <span class="label label-danger"><?= t('FAILED'); ?></span>
            <?php } ?>
          </div>
        </div>
      </div>
      <div class='row'>
        <div class='col-md-10'>
          <label><?= t('Headings'); ?> 3</label>
        </div>
        <div class='col-md-2'>
          <div class="pull-right">
            <?php if($h3counter != 0){ ?>
              <span class="label label-success"><?= t('PASSED'); ?></span>
            <?php }else{ ?>
              <span class="label label-danger"><?= t('FAILED'); ?></span>
            <?php } ?>
          </div>
        </div>
      </div>
      <div class='row'>
        <div class='col-md-12'>
          <?php if($h1counter != 0 && $h2counter != 0){ ?>
            <?php if($h3counter == 0){ ?>
              <div class="alert alert-success">
                <?= t('The page has a heading 1 and heading 2. It does not have a heading 3.'); ?>
              </div>
            <?php }else if($h1counter == 1){ ?>
              <div class="alert alert-success">
                <?= t('Great, the page has a nice content structure with headings 1 to 3.'); ?>
              </div>
            <?php }else{ ?>
              <div class="alert alert-warning">
                <?= t('The page has multiple headings 1. A page can only have 1 main heading.'); ?>
              </div>
            <?php } ?>
          <?php }else{ ?>
            <div class="alert alert-danger">
              <?= t('The page structure is not good. You should work with a single main heading and multiple heading 2.'); ?>
            </div>
          <?php } ?>
        </div>
      </div>
      <hr/>
      <div class='row'>
        <div class='col-md-12'>
          <label class="control-label"><?= t('Images'); ?></label>
        </div>
      </div>
      <div class='row'>
        <div class='col-md-12'>
          <div class="well well-sm">
            <p style='font-size: 12px; margin-bottom: 0;'>
            <?= t('A search engine does not know what is on an image (yet). That is why a good alt-tag (description) on an image is important.'); ?>
            <br/>
            <?= t('The alt-tag is also important for people with a screen reader. The screen reader will tell the visitor what is visible on the image.'); ?>
            <br/>
            <?= t('A title-tag is recommended and gives extra information about the image.'); ?>
            </p>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-12">
          <table class="table table-condensed">
            <thead>
              <tr>
                <th><?= t('Image'); ?></th>
                <th><?= t('Alt'); ?></th>
                <th><?= t('Title'); ?></th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php if(!empty($imgArr)) { ?>
                <?php foreach($imgArr as $image) { ?>
                  <?php $name = basename($image['src']); ?>
                  <tr>
                    <td style="font-size: 12px;"><?= $name; ?></td>
                    <td style="font-size: 12px;"><?= $image['alt'];; ?></td>
                    <td style="font-size: 12px;"><?= $image['title']; ?></td>
                    <td>
                      <div class="pull-right">
                        <?php if($image['alt'] != ''){ ?>
                          <?php if(stripos($image['src'],$image['alt']) === false){ ?>
                            <span class="label label-success"><?= t('PASSED'); ?></span>
                          <?php }else{ ?>
                            <span class="label label-warning"><?= t('WARNING'); ?></span>
                          <?php } ?>
                        <?php }else{ ?>
                          <span class="label label-danger"><?= t('FAILED'); ?></span>
                        <?php } ?>
                      </div>
                    </td>
                  </tr>
                <?php } ?>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
      <hr />
      <div class='row'>
        <div class='col-md-12'>
          <label class="control-label"><?= t('Keywords'); ?></label>
        </div>
      </div>
      <div class='row'>
        <div class='col-md-12'>
          <div class="well well-sm">
            <p style='font-size: 12px;'>
            <?= t('If a word occurs multiple times on a page, then search engines will rank you higher on that word.'); ?>
            <br/>
            <?=  t('However, using the same word to much will result in a penalty. A text with the same word over and over will be harder to read and search engines want good content.'); ?>
            </p>
            <p style='font-size: 12px; margin-bottom: 0;'>
            <?=  t('A word-percentage between 2% and 4% is ideal to rank good in search engines.'); ?>
            <br/><strong>
            <?=  t('The main keyword should be in the meta title, meta description to rank well.'); ?>
            </strong></p>
          </div>
        </div>
      </div>
      <div class='row'>
        <div class="col-sm-12">
          <?php for($i = 0; $i < 3; $i++) { ?>
            <table class="table table-condensed">
              <thead>
                <tr>
                  <?php $numberKeywords = intval($i) + 1; ?>
                  <th style="width: 40%;"><?= t('Keyword'); ?> (<?= $numberKeywords; ?>)</th>
                  <th style="width: 20%;"><?= t('Count'); ?></th>
                  <th style="width: 20%;"><?= t('Percent'); ?></th>
                  <th style="width: 20%;"></th>
                </tr>
              </thead>
              <tbody>
                <?php if(!empty($multikeywords[$i])) { ?>
                  <?php $multicounter = 1; ?>
                  <?php foreach($multikeywords[$i] as $key => $value){ ?>
                    <?php if(($i == 0 && $multicounter < 11) || ($i > 0 && $multicounter < 6)) { ?>
                      <tr>
                        <td style="font-size: 12px;"><?= $key; ?></td>
                        <td style="font-size: 12px;"><?= $value[0]; ?></td>
                        <td style="font-size: 12px;"><?= $value[1]; ?></td>
                        <td>
                          <div class="pull-right">
                            <?php if($value[1] > 4){ ?>
                              <span class="label label-danger"><?= t('BAD'); ?></span>
                            <?php }else if($value[1] < 4 && $value[1] > 2){ ?>
                              <span class="label label-success"><?= t('GOOD'); ?></span>
                            <?php }else{ ?>
                              <span class="label label-warning"><?= t('LOW'); ?></span>
                            <?php } ?>
                          </div>
                        </td>
                      </tr>
                    <?php } ?>
                    <?php $multicounter++; ?>
                  <?php } ?>
                <?php } ?>
              </tbody>
            </table>
          <?php } ?>
        </div>
      </div>
    </div>
    <div class="page-analyse-tab page-analyse-information" style="display: none;">
      <div class="row">
        <div class="col-sm-12">
          <div class="well well-sm" style="font-size: 12px;">
            <p>Deze module analyseert de huidige pagina op tekstuele inhoud en afbeeldingen. Toch moet de opmaak van de tekst in acht genomen worden om goed te scoren op een bepaald woord in Google.</p>
            <p style='font-weight: bold;'>Google zegt: 1 pagina (en dus 1 link) is 1 stukje inhoud die apart ge&euml;valueerd wordt.</p>
            <h4>Tekst</h4>
            <p>Vooraleer een nieuwe pagina aangemaakt wordt, moet u zelf de vraag stellen: <br/><span style='font-style: italic;'>Wat is het belangrijkste woord waarop ik wil gevonden worden in Google?</span></p>
            <p>Het woord (of woordcombinatie) waar je op wilt scoren, is belangrijk omdat het woord een paar keer gebruikt moet worden op een pagina en op vrij specifieke locaties.<br/>Belangrijk is ook dat je een plaatsnaam gebruikt om lokaal goed te scoren.</p>
            <p><strong>Naam/Titel van de pagina</strong>: Uw belangrijk woord of woordcombinatie (eventueel met gemeente/streek)</p>
            <p><strong>Omschrijving van de pagina</strong>: Een slagzin om mensen te lokken naar de pagina. Herhaal het woord en de gemeente/streek/regio in deze zin.</p>
            <h4>Titels</h4>
            <p>Een web-pagina kan gezien worden als een artikel in een tijdschrift. Deze bestaat altijd uit een hoofdtitel en een paar ondertitels.</p>
            <p><strong>Hoofdtitel</strong>: bevat het woord waarop je wilt gevonden worden</p>
            <p><strong>Ondertitels</strong>: indien meerdere ondertitels, raden wij aan om op de laatste ondertitel ook nog eens het woord te herhalen waarop u wilt gevonden worden. Ook de streek/regio herhaal je eens in de ondertitels.</p>
            <p><strong>Tekst: Uw tekst moet uniek zijn!</strong> Kopiëren van een andere website mag zeker niet gebeuren want unieke inhoud en meerwaarde is belangrijk. Wij raden aan om het woord waarop u wilt scoren toch 1 keer te herhalen in de tekst. Herhaal het woord niet TE veel want dan zou Google denken dat je te hard probeert om te scoren op dat woord.</p>
            <h4>Afbeeldingen</h4>
            <p>Afbeeldingen kunnen ook een pagina sterker maken om gemakkelijk gevonden te worden. Hiervoor hou je best een paar puntjes in gedachten.</p>
            <p><strong>Naam van de afbeelding</strong>: De bestandsnaam van de afbeelding zegt wat er op afbeelding staat. (bijv: webdesign-op-maat.jpg zegt meer dan 22.jpg)</p>
            <p><strong>Omschrijving (alt)</strong>: Je kan een omschrijving of alt op een afbeelding plaatsen. Zet in de omschrijving wat er op de afbeelding staat in 1 korte zin.</p>
            <p>&nbsp;</p>
            <h4>Extra Zoekmachine tips</h4>
            <p><strong>Hoe lang een website online staat, is ook een factor.</strong><br/>Een nieuwe website zal niet meteen bovenaan de zoekresultaten te zien zijn. Daarom raden wij aan de eerste 2 maand om te adverteren via Google. <br/>Contacteer ons voor meer informatie.</p>
            <p><strong>Links naar uw website.</strong><br/>
            In het algemeen is de regel, hoe meer links naar uw website, hoe beter. Natuurlijk links van rare/onbetrouwbare websites kunnen de vindbaarheid van uw website schaden.<br/>Wij hebben een gratis document om veilig links naar uw website te krijgen. Contacteer ons voor meer informatie.</p>
            <p><strong>Let op voor onbetrouwbare personen/bedrijven</strong><br/>
            Let op voor personen die zeggen zij uw website op plaats 1 van Google kunnen plaatsen. Vaak zijn dit zeer onbetrouwbare partners en zullen ze schade aanrichten aan uw nieuwe website.</p>
            <p>Onze experten staan klaar om uw vragen te beantwoorden...<br/></p>
            Aarzel niet om ze te stellen. <br/>Mail: info@mijnwebsitebouwen.be<br/>Tel: 09 222 01 27</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
