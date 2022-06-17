<?php
namespace Concrete\Package\MyABC\Controller\SinglePage;
use Concrete\Core\Page\Controller\PageController;

use Localization;
use Core;
use Loader;


  class PDF extends \FPDF{
    public function Header(){
    }

    public function Footer(){
      $this->SetY(-15);
      $this->SetFont('Arial', '', 8);
      $this->SetTextColor(0, 0, 0);
      $this->Cell(150, 4, t('Overeenkomst interventies ter plaatse 2021'), 0,0,'L');
      $this->Cell(20, 4, t('Pagina ') . $this->PageNo() . t(' van {nb}'), 0, 1, 'L');
    }
  }

  class agreementfs extends pageController{
    public function view($variables = null){
      $variablesArr = explode('--', $variables);
      $lang = $variablesArr[0];

      $this->setLang($lang);

      if($lang){
        $linkReview = 'https://my.abcparts.be/agreementfs/review/' . $lang;
        $linkManual = 'https://my.abcparts.be/agreementfs/manual/'. $lang;
      }else{
        $linkReview = 'https://my.abcparts.be/agreementfs/review/nl';
        $linkManual = 'https://my.abcparts.be/agreementfs/manual/nl';
      }

      $this->set('linkReview', $linkReview);
      $this->set('linkManual', $linkManual);
    }

    public function review($variables = null){
      $variablesArr = explode('--', $variables);
      $lang = $variablesArr[0];

      $this->setLang($lang);

      if($lang){
        $linkSign = 'https://my.abcparts.be/agreementfs/signed/' . $lang;
        $linkHome = 'https://my.abcparts.be/agreementfs/' . $lang;
      }else{
        $linkSign = 'https://my.abcparts.be/agreementfs/signed/nl';
        $linkHome = 'https://my.abcparts.be/agreementfs/nl';
      }

      $this->set('linkSign', $linkSign);
      $this->set('linkHome', $linkHome);
      $this->set('lang', $lang);
      $this->set('dagtarief', 105);
      $this->set('nachttarief', 194);
      $this->set('startfee', 150);
      $this->set('kilometervergoeding', number_format(0.80, 2, '.', ''));
      $this->set('datum', date('d/m/Y'));

      $this->set('compName', $_POST['compName']);
      $this->set('adres', $_POST['street']);
      $this->set('zipcode', $_POST['zipcode']);
      $this->set('city', $_POST['city']);
      $this->set('vat', $_POST['vat']);

      $this->set('email', $_POST['email']);
      $this->set('contact', $_POST['contact']);

      $this->set('review', 'review');
    }

    public function signed($variables = null){
      $variablesArr = explode('--', $variables);
      $lang = $variablesArr[0];
      $db = \Database::connection();

      $this->setLang($lang);
      $this->set('compName', $_POST['compName']);
      $this->set('adres', $_POST['adres']);
      $this->set('city', $_POST['city']);
      $this->set('vat', $_POST['vat']);
      $this->set('contact', $_POST['contact']);
      $this->set('email', $_POST['email']);

      $data = array();
      $data['compName'] = $_POST['compName'];
      $data['contact'] = $_POST['contact'];
      $data['email'] = $_POST['email'];
      $data['adres'] = $_POST['adres'];
      $data['zipcode'] = $_POST['zipcode'];
      $data['city'] = $_POST['city'];
      $data['vat'] = $_POST['vat'];
      $data['lang'] = $lang;

      $mailService = Core::make('mail');
      $datum = date('d/m/Y');

      $this->set('signed', 'signed');
      $linkConfirm = 'https://my.abcparts.be/application/files/agreements/agreementfs-' . str_replace(array('/', ' ', '-', '+', ','), '', $_POST['compName']) . '-' . str_replace('/', '', $datum) . '.pdf';
      $this->set('linkConfirm', $linkConfirm);

      $bodyHeader = '
      <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
      <html xmlns="http://www.w3.org/1999/xhtml">
      <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
      <title>'. t('Your repair request has been registered') .'</title>


      <!--[if gte mso 9]>
      <style type="text/css">
      #pageContainer {
        background-color:transparent !important;
      }
      </style>
      <![endif]-->
      <style type="text/css">
        body{
          margin:0;
          padding:0;
          background-color:#e6e6e6;
          color:#777777;
          font-family:Arial, Helvetica, sans-serif;
          font-size:12px;
          -webkit-text-size-adjust:none;
          -ms-text-size-adjust:none;
        }
        h1,h2,h3{
          color:#555555;
          margin-bottom:15px !important;
        }
        h4{
          color:#777777;
          margin-bottom:0px !important;
        }
        a,a:link,a:visited{
          color:#74b843;
          text-decoration:underline;
        }
        a:hover,a:active{
          text-decoration:none;
          color:#28855d !important;
        }
        .phone a{
          text-decoration:none;
        }
        p{
          margin:0 0 14px 0;
          padding:0;
        }
        img{
          border:0;
        }
        table td{
          border-collapse:collapse;
        }
        td.border_b{
          border-bottom:1px #eeeeee solid;
        }
        td.border_r{
          border-right:1px #eeeeee solid;
        }
        td.border_b_r{
          border-bottom:1px #eeeeee solid;
          border-right:1px #eeeeee solid;
        }
        #pricingTable{
          border-collapse:separate !important;
        }
        .highlighted{
          color:#74b843;
        }
        .ReadMsgBody{
          width:100%;
        }
        .ExternalClass{
          width:100%;
        }
        .yshortcuts{
          color:#777777;
        }
        .yshortcuts a span{
          color:#777777;
          border-bottom:none !important;
          background:none !important;
          text-decoration:none !important;
        }
        .adres{
          font-size: 13px;
          color: #777777;
        }
        div{
          font-size: 13px;
        }
        .pad{
          padding-left: 10px;
        }

    </style></head>

    <body background="images/bg.jpg">

    <table id="pageContainer" width="100%" align="center" background="images/bg.jpg" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse; text-align:left; background-repeat:repeat; background-color:#e6e6e6;">
      <tr>
          <td style="padding-top:30px; padding-bottom:30px;">

          <!-- Start of logo, phone and banner container -->
          <table bgcolor="#ffffff" width="600" align="center" cellpadding="0" cellspacing="0" style="border-collapse:collapse; text-align:left; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:12px; line-height:15pt; color:#777777;">
              <tr>
                  <td width="270" valign="middle" style="padding-top:20px; padding-left:30px; padding-bottom:15px; font-family:Arial, Helvetica, sans-serif; font-size:24px; line-height:15pt; color:#000; border-top:1px solid #dddddd;"><a href="https://www.abcparts.be" target="_blank">
                      <img src="https://my.abcparts.be/application/img/ABC_Industrial_Parts800.png" alt="ABC Industrial Parts" border="0" width="200" style="margin: 0; padding: 0; width:200px" mc:edit="logo"></a>
                  </td>
                  <td mc:edit="support" width="270" valign="middle" style="padding-top:25px; padding-right:30px; padding-bottom:25px; font-family:Arial, Helvetica, sans-serif; font-size:12px; line-height:15pt; color:#777777; text-align:right; border-top:1px solid #dddddd;"><br>

  <h4 class="phone" style="font-family:\'Segoe UI\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size:24px; line-height:100%; font-weight:300; color:#777777; margin-top:0; margin-bottom:0 !important; padding:0;"><span class="mc-toc-title"><span>+32 9 380 43 50<br><a href="https://www.abcparts.be" target="_blank">www.abcparts.be</a></span></span></h4>
  </td>
              </tr>
          </table>
        <!-- End of logo, phone and banner container -->

              <!-- Start of content with image on the left -->
              <table bgcolor="#ffffff" width="600" align="center" cellpadding="0" cellspacing="0" style="border-collapse:collapse; text-align:left; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:12px; line-height:15pt; color:#777777;">
                  <tr>
                      <td width="600" colspan="2" style="padding-top:20px; padding-right:30px; padding-left:30px; font-family:Arial, Helvetica, sans-serif; font-size:12px; line-height:15pt; color:#777777;">
                          <h2 mc:edit="title1" style="font-family:\'Segoe UI\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size:26px; line-height:27pt; color:#555555; font-weight:300; margin-top:0; margin-bottom:15px !important; padding:0;"><span style="color:#8bb23b">'. $mailTitle .'</span></h2>
            </td>
                  </tr>
                  <tr>

                      <td mc:edit="inhoud1" width="600" valign="top" style="padding-right:30px; padding-left:30px; font-family:Arial, Helvetica, sans-serif; font-size:12px; line-height:15pt; color:#777777;"><div style="text-align: left;">
      ';

      $bodyFooter = '
        </div>
                </td>
                  </tr>
                  <tr>
                    <td width="600" colspan="2" height="31" style="padding-top:5px; font-size:2px; line-height:0px;"><img src="https://gallery.mailchimp.com/e3bc240bdf4c4a226170902c0/images/1248ec40-b635-4f50-9f84-3bb797a235a2.png" alt="" border="0" style="margin: 0; padding: 0;" mc:edit="divider1"></td>
                  </tr>
        </table>
        <!-- End of content with image on the left -->



        <!-- Start of footer -->
        <table width="640" align="center" cellpadding="0" cellspacing="0" style="border-collapse:collapse; text-align:left; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:12px; line-height:15pt; color:#777777;">
        <tr>
          <td mc:edit="footer_copyright" width="600" colspan="7" style="padding-left:20px; padding-right:20px; font-family:Arial, Helvetica, sans-serif; font-size:11px; line-height:13pt; color:#777777; text-align:center;"><!-- TrustBox widget - Review Collector --> <div class="trustpilot-widget" data-locale="%localetrustpilot%" data-template-id="56278e9abfbbba0bdcd568bc" data-businessunit-id="5d14e4694d01c600010eee1a" data-style-height="52px" data-style-width="100%"> <a href="https://nl.trustpilot.com/review/abcparts.be" target="_blank" rel="noopener"><img src="https://www.abcparts.be/application/files/6615/6172/4284/trustpilot.png" width="300" height="150px" style="border:none"></a> </div> <!-- End TrustBox widget -->Copyright &copy; ' . date('Y') . ' <a href="https://www.abcparts.be" style="text-decoration:underline; color:#8bb23b;" target="_blank"><span style="color:#8bb23b">ABC Industrial Parts</span></a>, All rights reserved.</td>
        </tr>
        </table>
        <!-- End of footer -->

        </td></tr></table>



        </body>
        </html>';

      $bodyContent = '<h3>'. t('We hebben uw bevestiging goed ontvangen.') .'</h3><br>';
      $bodyContent .= '<p>' . t('De overeenkomst werd bevestigd door ') . '<strong>' . ucfirst($_POST['contact']) . '</strong>' .t(' op ') . '<strong>' . date('d/m/Y') . '</strong></p>';
      $bodyContent .= '<p>' . t('In de bijlage kan u uw overeenkomst terugvinden.').

      $this->pdf($data);
      $compName = str_replace(array('/', '-', ' ', '+', '<', '>'), '', $_POST['compName']);
      $file = $this->submit($compName);
      $fID = $file->getFileID();

      if($fID){
        $attachment = \Concrete\Core\File\File::getByID($fID);
      }

      $mailService->setBodyHTML($bodyHeader . $bodyContent . $bodyFooter);
      $mailService->setSubject(t('Bevestiging interventievoorwaarden ') . date('Y') . '!');
      $mailService->from('offer@abcparts.be', 'ABC parts Offer');
      $mailService->to($_POST['email']);
      $mailService->cc('info@abcparts.be');
      $mailService->addAttachment($attachment);

      $mailService->sendMail();
    }

    public function setLang($lang){
      switch($lang){
        case 'nl':
        default:
        $locale = 'nl_BE';
        $localeTrustpilot = 'nl-BE';
        $langCode = 'nl';
        break;

        case 'fr':
        $locale = 'fr_FR';
        $localeTrustpilot = 'fr-FR';
        $langCode = 'fr';
        break;

        case 'en':
        $locale = 'en_GB';
        $localeTrustpilot = 'en-GB';
        $langCode = 'en';
        break;
      }
      Localization::changeLocale($locale);
    }

    public function submit($compName){
      $datum = date('d/m/Y');
      $file = '/var/web/vd16778/public_html/application/files/agreements/agreementfs-' . $compName. '-' .str_replace('/', '', $datum).'.pdf';
      $filename = 'agreementfs-' . $compName . '-' . str_replace('/', '', $datum) .'.pdf';

      $importer = new \Concrete\Core\File\Importer();
      $result = $importer->import($file, $filename);

      return $result;
    }

    public function pdf($data){
      $db = \Database::connection();

      $this->setLang($data['lang']);
      $datum = date('d/m/Y');
      $h = 6;

        $compName = $data['compName'];
        $address = $data['adres'];
        $city = $data['city'];
        $vat = $data['vat'];
        $contact = $data['contact'];

      $pdf = new PDF();
      $pdf->SetAutoPageBreak(1, 31);
      $pdf->SetMargins(20, 30, 20);
      $pdf->AliasNbPages();
      $pdf->AddPage();
      $pdf->setSubject(t('Overeenkomst interventies 2021'));

      $pdf->SetFont('Arial', '', 10);
      $pdf->Cell(170, 4, 'Kruisem, ' . date('d/m/Y'), 0,1,'R');
      $pdf->SetFont('Arial', 'B', 14);
      $pdf->SetTextColor(139,178,59);
      $pdf->Cell(190, 10, '', 0, 1, 'L');
      $pdf->Cell(0, $h, t('Overeenkomst interventies ter plaatse'), 0, 1, 'L');
      $pdf->SetFont('Arial', '', 8);
      $pdf->SetTextColor(0,0,0);
      $pdf->Cell(0, $h, t('Geldig van %1$s tot 31/12/2021', $datum), 0, 1, 'L');
      $pdf->Cell(0, $h, '',0,1,'L');

        $pdf->Cell(0, $h, $custData, 0,1,'L');

      $pdf->SetFont('Arial', 'B', 10);
      $pdf->SetTextColor(0,0,0);
      $pdf->Cell(85, $h, iconv('UTF-8', 'CP1256', t('Tussen:')), 0, 0, 'L');
      $pdf->Cell(0, $h, iconv('UTF-8', 'CP1256', t('En:')),0,1,'L');
      $pdf->SetFont('Arial', '', 10);
      $pdf->Cell(0, 3, '',0,1,'L');
      $pdf->Cell(85, $h, 'ABC Industrial Parts bv',0,0,'L');
      $pdf->Cell(0, $h, iconv('UTF-8', 'CP1256', $compName),0,1,'L');
      $pdf->Cell(85, $h, 'Kazerneweg 29',0,0,'L');
      $pdf->Cell(0,$h, iconv('UTF-8', 'CP1256', $address),0,1,'L');
      $pdf->Cell(85, $h, '9770 Kruisem',0,0,'L');
      $pdf->Cell(0,$h, iconv('UTF-8', 'CP1256', $city),0,1,'L');
      $pdf->Cell(85, $h, t('Btw: ') . 'BE0888.467.144',0,0,'L');
      $pdf->Cell(0,$h, iconv('UTF-8', 'CP1256', t('Btw: ') . $vat),0,1,'L');
      $pdf->Cell(85, $h, '',0, 0, 'L');
      $pdf->Cell(0, $h, iconv('UTF-8', 'CP1256', t('Contactpersoon: ') .$contact),0,1,'L');
      $pdf->Cell(0, 10, '', 0, 1, 'L');

      //ALGEMENE INTERVENTIEVOORWAARDEN
      $pdf->SetFont('Arial', 'B', 12);
      $pdf->SetTextColor(139, 178, 59);
      $pdf->Cell(150, 8, '1. ' . iconv('UTF-8', 'CP1256', t('Algemene interventievoorwaarden')),0,1,'L');
      $pdf->Cell(0, 8, '',0,1,'L');

      $pdf->SetFont('Arial', '', 10);
      $pdf->SetTextColor(0,0,0);
      $pdf->MultiCell(0, $h, iconv('UTF-8', 'CP1256', t('In overleg met de klant is het mogelijk om ter plaatse te komen tijdens de kantooruren, dit aan het vooropgestelde dagtarief.')),0,'L');
      $pdf->Cell(0, 10, '',0,1,'L');
      $pdf->Cell(0, $h, iconv('UTF-8', 'CP1256', t('Onafhankelijk van het resultaat wordt onderstaande steeds gefactureerd per interventie')),0,1,'L');
      $pdf->Cell(0, 4, '',0,1,'L');

      $pdf->Cell(10, $h, '');
      $pdf->Cell(7, $h, chr(149));
      $pdf->Cell(0, $h, iconv('UTF-8', 'CP1256', t('Vaste startkost')) . '*',0,1,'L');
      $pdf->Cell(10, $h, '');
      $pdf->Cell(7, $h, chr(149));
      $pdf->Cell(0, $h, iconv('UTF-8', 'CP1256', t('Extra technische voorbereiding / opzoekingswerk')),0,1,'L');
      $pdf->Cell(10, $h, '');
      $pdf->Cell(7, $h, chr(149));
      $pdf->Cell(0, $h, iconv('UTF-8', 'CP1256', t('De gepresteerde uren ter plaatse')),0,1,'L');
      $pdf->Cell(10, $h, '');
      $pdf->Cell(7, $h, chr(149));
      $pdf->Cell(0, $h, iconv('UTF-8', 'CP1256', t('Gebruikte onderdelen')),0,1,'L');
      $pdf->Cell(10, $h, '');
      $pdf->Cell(7, $h, chr(149));
      $pdf->Cell(0, $h, iconv('UTF-8', 'CP1256', t('De uren nodig voor de verplaatsing van en naar ABC Industrial Parts bv')),0,1,'L');
      $pdf->Cell(10, $h, '');
      $pdf->Cell(7, $h, chr(149));
      $pdf->Cell(0, $h, iconv('UTF-8', 'CP1256', t('Een kilometervergoeding heen en weer van € 0,80 / km')),0,1,'L');
      $pdf->Cell(10, $h, '');
      $pdf->Cell(7, $h, chr(149));
      $pdf->Cell(0, $h, iconv('UTF-8', 'CP1256', t('Eventuele extra transportkosten, bv tunnelkosten')),0,1,'L');
      $pdf->Cell(10, $h, '');
      $pdf->Cell(7, $h, chr(149));
      $pdf->Cell(0, $h, iconv('UTF-8', 'CP1256', t('Eventuele overnachtingskosten')),0,1,'L');
      $pdf->Cell(0, 6, '',0,1,'L');

      $pdf->MultiCell(0, $h,'*' . iconv('UTF-8', 'CP1256', t('Dit betreft het verzamelen van het nodige werkmateriaal, het inladen van de wagen, administratieve afhandeling van het dossier')) . '...',0,'L');
      $pdf->Cell(0, 10, '',0,1,'L');

      //UITZONDERLIJKE INTERVENTIEVOORWAARDEN
      $pdf->SetFont('Arial', 'B', 12);
      $pdf->SetTextColor(139, 178, 59);
      $pdf->Cell(150, $h, '2. ' . iconv('UTF-8', 'CP1256', t('Uitzonderlijke interventievoorwaarden')),0,1,'L');

      $pdf->SetFont('Arial', '', 10);
      $pdf->SetTextColor(0,0,0);
      $pdf->Cell(0, 8, '',0,1,'L');
      $pdf->MultiCell(0, $h, iconv('UTF-8', 'CP1256', t('In overleg met de klant is het mogelijk om ter plaatse te komen buiten de kantooruren, dit aan het uitzonderlijke nacht-weekendtarief.')),0,'L');
      $pdf->Cell(0, 10, '',0,1,'L');

      $pdf->Cell(10, $h, '');
      $pdf->Cell(7, $h, chr(149));
      $pdf->Cell(0, $h, iconv('UTF-8', 'CP1256', t('Bovenstaande algemene interventievoorwaarden blijven van toepassing')),0,1,'L');
      $pdf->Cell(10, $h, '');
      $pdf->Cell(7, $h, chr(149));
      $pdf->MultiCell(0, $h, iconv('UTF-8', 'CP1256', t('Prestaties buiten de kantooruren zijn enkel mogelijk na overleg met ABC')),0,'L');
      $pdf->Cell(10, $h, '');
      $pdf->Cell(7, $h, chr(149));
      $pdf->MultiCell(0, $h, iconv('UTF-8', 'CP1256', t('Onder interventies buiten de kantooruren verstaan we interventies die plaatsvinden tussen 19 uur en 7 uur op weekdagen en interventies die uitgevoerd worden tijdens het weekend of op feestdagen.')),0,'L');
      $pdf->Cell(0, 10, '',0,1,'L');

      // TARIEVEN
      $pdf->SetFont('Arial', 'B', 12);
      $pdf->SetTextColor(139, 178, 59);
      $pdf->Cell(150, $h, '3. ' . iconv('UTF-8', 'CP1256', t('Tarieven')),0,1,'L');

      $pdf->SetFillColor(139,178,59);
      $pdf->SetDrawColor(150, 178, 59);
      $pdf->Cell(190, 8, '', 0,1, 'L');
      $pdf->SetFont('Arial', 'B', 10);
      $pdf->SetTextColor(0,0,0);
      $pdf->Cell(85, $h, iconv('UTF-8', 'CP1256', t('Dagtarief / uur')), 'T', 0, 'L');
      $pdf->SetFont('Arial', '', 10);
      $pdf->Cell(0, $h, chr(128) . ' 105 / h','T',1,'L');
      $pdf->SetFont('Arial', 'B', 10);
      $pdf->Cell(85, $h, iconv('UTF-8', 'CP1256', t('Nacht-weekendtarief / uur')), 'T', 0, 'L', 1);
      $pdf->SetFont('Arial', '', 10);
      $pdf->Cell(0, $h, chr(128) . ' 194 / h','T',1,'L', 1);
      $pdf->SetFont('Arial', 'B', 10);
      $pdf->Cell(85, $h, iconv('UTF-8', 'CP1256', t('Start fee')), 'T', 0, 'L');
      $pdf->SetFont('Arial', '', 10);
      $pdf->Cell(0, $h, chr(128) . ' 150','T',1,'L');
      $pdf->SetFont('Arial', 'B', 10);
      $pdf->Cell(85, $h, iconv('UTF-8', 'CP1256', t('Kilometervergoeding')), 'T B', 0, 'L', 1);
      $pdf->SetFont('Arial', '', 10);
      $pdf->Cell(0, $h, chr(128) . ' 0,80 / km','T B',1,'L', 1);

      $pdf->Cell(0, 8, '',0,1,'L');

      //KANTOORUREN
      $pdf->SetFont('Arial', 'B', 12);
      $pdf->SetTextColor(139, 178, 59);
      $pdf->Cell(150, $h, '4. ' . iconv('UTF-8', 'CP1256', t('Kantooruren')),0,1,'L');

      $pdf->SetTextColor(0,0,0);
      $pdf->Cell(0, 8, '',0,1,'L');

      $pdf->Cell(85, $h, iconv('UTF-8', 'CP1256', t('Maandag')), 'T', 0, 'L');
      $pdf->SetFont('Arial', '', 10);
      $pdf->Cell(0, $h, iconv('UTF-8', 'CP1256', t('8 uur - 18 uur (doorlopend)')),'T',1,'L');
      $pdf->SetFont('Arial', 'B', 10);
      $pdf->Cell(85, $h, iconv('UTF-8', 'CP1256', t('Dinsdag')), 'T', 0, 'L', 1);
      $pdf->SetFont('Arial', '', 10);
      $pdf->Cell(0, $h, iconv('UTF-8', 'CP1256', t('8 uur - 18 uur (doorlopend)')),'T',1,'L', 1);
      $pdf->SetFont('Arial', 'B', 10);
      $pdf->Cell(85, $h, iconv('UTF-8', 'CP1256', t('Woensdag')), 'T', 0, 'L');
      $pdf->SetFont('Arial', '', 10);
      $pdf->Cell(0, $h, iconv('UTF-8', 'CP1256', t('8 uur - 18 uur (doorlopend)')),'T',1,'L');
      $pdf->SetFont('Arial', 'B', 10);
      $pdf->Cell(85, $h, iconv('UTF-8', 'CP1256', t('Donderdag')), 'T', 0, 'L', 1);
      $pdf->SetFont('Arial', '', 10);
      $pdf->Cell(0, $h, iconv('UTF-8', 'CP1256', t('8 uur - 18 uur (doorlopend)')),'T',1,'L', 1);
      $pdf->SetFont('Arial', 'B', 10);
      $pdf->Cell(85, $h, iconv('UTF-8', 'CP1256', t('Vrijdag')), 'T B', 0, 'L');
      $pdf->SetFont('Arial', '', 10);
      $pdf->Cell(0, $h, iconv('UTF-8', 'CP1256', t('8 uur - 17.30 uur (doorlopend)')),'T B',1,'L');
      $pdf->Cell(0, 8, '',0,1,'L');

      //UITSTEL VAN INTERVENTIE TER PLAATSE
      $pdf->SetFont('Arial', 'B', 12);
      $pdf->SetTextColor(139, 178, 59);
      $pdf->Cell(150, $h, '5. ' . iconv('UTF-8', 'CP1256', t('Uitstel van interventie ter plaatse')),0,1,'L');
      $pdf->Cell(0, 8,'',0,1,'L');

      $pdf->SetFont('Arial', '', 10);
      $pdf->SetTextColor(0,0,0);

      $pdf->MultiCell(0, $h, iconv('UTF-8', 'CP1256', t('ABC Industrial Parts bv is in de mogelijkheid om interventies ter plaatse uit te stellen/te verplaatsen in geval van overmacht.')),0,'L');
      $pdf->Cell(0, 8, '',0,1,'L');

      //BETALINGSVOORWAARDEN
      $pdf->SetFont('Arial', 'B', 12);
      $pdf->SetTextColor(139, 178, 59);
      $pdf->Cell(150, $h, '6. ' . iconv('UTF-8', 'CP1256', t('Voorafbetaling van de interventie ter plaatse')),0,1,'L');
      $pdf->Cell(0, 8,'',0,1,'L');

      $pdf->SetFont('Arial', '', 10);
      $pdf->SetTextColor(0,0,0);

      $pdf->MultiCell(0, $h, iconv('UTF-8', 'CP1256', t('Betaling van de interventie gebeurt steeds na de interventie en na ontvangst van de factuur, dit volgens de vastgelegde betalingsvoorwaarden.')),0,'L');

      $pdf->Cell(0, 10, '', 0,1,'L');

      $pdf->MultiCell(0, $h, iconv('UTF-8', 'CP1256', t('ABC Industrial Parts bv behoudt zich het recht voor om een voorafbetaling te vragen voor de interventie ter plaatse in volgende gevallen:')),0,'L');

      $pdf->Cell(0, 10, '', 0,1,'L');

      $pdf->Cell(10, $h, '');
      $pdf->Cell(7, $h, chr(149));
      $pdf->Cell(0, $h, iconv('UTF-8', 'CP1256', t('U bent een nieuwe klant')),0,1,'L');
      $pdf->Cell(10, $h, '');
      $pdf->Cell(7, $h, chr(149));
      $pdf->MultiCell(0, $h, iconv('UTF-8', 'CP1256', t('De kredietverzekering adviseerd ABC Industrial Parts bv een voorafbetaling te vragen')),0,'L');
      $pdf->Cell(10, $h, '');
      $pdf->Cell(7, $h, chr(149));
      $pdf->MultiCell(0, $h, iconv('UTF-8', 'CP1256', t('U heeft in het verleden reeds nagelaten om betalingen binnen de afgesproken betalingstermijn te betalen')),0,'L');
      $pdf->Cell(10, $h, '');
      $pdf->Cell(7, $h, chr(149));
      $pdf->MultiCell(0, $h, iconv('UTF-8', 'CP1256', t('U hebt op het moment van de interventie nog openstaande facturen bij ABC Industrial Parts bv.')),0,'L');

      $pdf->Cell(0, 15, '', 0, 1, 'L');

      $pdf->multiCell(0, $h, iconv('UTF-8', 'CP1256', t('In geval van voorafbetaling ontvangt u een pro-formafactuur. Onderstaande wordt aangerekend:')),0,'L');
      $pdf->Cell(0, $h, '',0,1,'L');

      $pdf->Cell(10, $h, '');
      $pdf->Cell(7, $h, chr(149));
      $pdf->Cell(0, $h, iconv('UTF-8', 'CP1256', t('Vaste startkost')),0,1,'L');
      $pdf->Cell(10, $h, '');
      $pdf->Cell(7, $h, chr(149));
      $pdf->Cell(0, $h, iconv('UTF-8', 'CP1256', t('Kilometervergoeding')),0,1,'L');
      $pdf->Cell(10, $h, '');
      $pdf->Cell(7, $h, chr(149));
      $pdf->Cell(0, $h, iconv('UTF-8', 'CP1256', t('Standaardforfait van 8 uren')),0,1,'L');

      $pdf->Cell(0, $h, '',0,1,'L');

      $pdf->MultiCell(0, $h, iconv('UTF-8', 'CP1256', t('Pas na ontvangst van een geldig betalingsbewijs of de effectieve betaling op rekening van ABC Industrial Parts bv wordt de interventie uitgevoerd.')),0,'L');

      $pdf->SetFont('Arial', 'B', 10);
      $pdf->SetTextColor(139, 178, 59);

      $pdf->Cell(0, 10, '',0,1,'L');

      $pdf->MultiCell(0,$h, iconv('UTF-8', 'CP1256', t('Akkoord voor deze overeenkomst wordt beschouwd als akkoord van de volledige firma.')),0,'L');
      $pdf->Cell(0, $h, '',0,1,'L');

      $pdf->MultiCell(0, $h, iconv('UTF-8', 'CP1256', t('Bij akkoord van deze overeenkomst gaat u automatisch akkoord met de algemene voorwaarden van ABC Industrial Parts bv.')),0,'L');
      $pdf->Cell(0, 10, '',0,1,'L');
      $pdf->SetTextColor(0,0,0);
      $pdf->Cell(0, $h, iconv('UTF-8', 'CP1256', t('De overeenkomst werd bevestigd door ') . $contact . t(' op ') . $datum));

      $compName = str_replace(array('/', '-', ' ', '+', '<', '>'), '', $compName);
      ob_get_clean();
      $pdf->Output('F', '/var/web/vd16778/public_html/application/files/agreements/agreementfs-' . $compName . '-'. str_replace('/', '', $datum) .'.pdf');
    }
  }
?>
