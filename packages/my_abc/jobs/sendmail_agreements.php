<?php
  namespace Concrete\Package\MyAbc\Job;
  use \Concrete\Core\Job\Job;

  use Loader;
  use Core;
  use Package;
  use Localization;

  use \Box\Spout\Reader\Common\Creator\ReaderFactory;
  use Box\Spout\Common\Type;


  class SendmailAgreements extends Job{
    public function getJobName(){
      return t('Send mail for agreement fs');
    }

    public function getJobDescription(){
      return t('Send a mail to customers to agree with the fieldservice agreement');
    }

    public function run(){
      // $recipients['to'] = 'margaux.van.noten@waak.be'; //test
      // $recipients['cc'] = 'onderhoud@vdlbelgium.com';
      // $recipients['lang'] = 'nl';
      // $this->sendMail($recipients);
      $customers = $this->getAllCustomers();

      foreach($customers as $customer){
        $recipients = $this->setRecipients($customer);
        $this->sendMail($recipients);
      }
    }

    public function getAllCustomers(){
      $pkg = Package::getByHandle('my_abc');
	    $pkgpath = $pkg->getPackagePath();
	    include_once($pkgpath . '/libraries/spout/src/Spout/Autoloader/autoload.php');

      $inputfilename = '/var/web/vd16778/public_html/application/files/excel/fs_contactpersons.xlsx';

      $reader = ReaderFactory::createFromType(Type::XLSX);
			$reader->open($inputfilename);

      $customers = array();
      $i = 0;
      foreach($reader->getSheetIterator() as $sheet){
        foreach($sheet->getRowIterator() as $row){
          $columns = $row->toArray();
          if($prev != $columns[1] && $i != 0){
            $i = 1;
          }

          if($i != 0){
            $customers[$columns[1]][$i-1]['contactName'] = $columns[2];
            $customers[$columns[1]][$i-1]['contactEmail'] = $columns[3];
            $customers[$columns[1]][$i-1]['language'] = $columns[4];
          }
          $prev = $columns[1];
          $i++;
        }
      }

      $customers['ABC'][0]['contactName'] = 'Robin De Meerleer';
      $customers['ABC'][0]['contactEmail'] = 'rdm@abcparts.be';
      $customers['ABC'][0]['language'] = 'Nederlands';

      return $customers;
    }

    public function setRecipients($customer){
      for($i = 0; $i < count($customer); $i++){
        if($i == 0){
          $data['to'] = $customer[$i]['contactEmail'];
        }else{
          if(empty($data['cc'])){
            $data['cc'] = $customer[$i]['contactEmail'];
          }else{
            $data['cc'] .= ', ' . $customer[$i]['contactEmail'];
          }
        }

        if($customer[$i]['language'] == 'Nederlands'){
          $data['lang'] = 'nl';
        }else{
          $data['lang'] = 'fr';
        }

      }
      return $data;
    }

    public function sendMail($recipients){
      $this->setLang($recipients['lang']);

      $mh = Core::make('mail');
      $mailBody = $this->getMailBody($recipients['lang']);

      $mh->setSubject(t('Interventieovereenkomst 2022'));
      $mh->setBodyHTML($mailBody);
      $mh->getBodyHTML();
      $mh->from('offer@abcparts.be', 'ABC parts Offer');
      $mh->to($recipients['to']);
      if(!empty($recipients['cc'])){
        $mh->cc($recipients['cc']);
      }

      $mh->sendMail();
    }

    public function getMailBody($lang){
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
          <td mc:edit="footer_copyright" width="600" colspan="7" style="padding-left:20px; padding-right:20px; font-family:Arial, Helvetica, sans-serif; font-size:11px; line-height:13pt; color:#777777; text-align:center;"><!-- TrustBox widget - Review Collector --> <div class="trustpilot-widget" data-locale="%localetrustpilot%" data-template-id="56278e9abfbbba0bdcd568bc" data-businessunit-id="5d14e4694d01c600010eee1a" data-style-height="52px" data-style-width="100%"> <a href="https://nl.trustpilot.com/review/abcparts.be" target="_blank" rel="noopener"><img src="https://www.abcparts.be/application/files/6615/6172/4284/trustpilot.png" width="300" height="150px" style="border:none"></a> </div> <!-- End TrustBox widget -->Copyright &copy; ' . date('Y', strtotime($datum)) . ' <a href="https://www.abcparts.be" style="text-decoration:underline; color:#8bb23b;" target="_blank"><span style="color:#8bb23b">ABC Industrial Parts</span></a>, All rights reserved.</td>
        </tr>
        </table>
        <!-- End of footer -->

        </td></tr></table>



        </body>
        </html>';

      $body = '<p>' .t('Beste, '). '</p>';
      $body .= '<p>' .t('Naar aanleiding van onze samenwerking in 2021, ontvangt u deze e-mail.'). '</p>';
      $body .= '<p>' .t('In 2021 heeft u een beroep gedaan op ABC Industrial Parts bv voor het uitvoeren van een interventie ter plaatse. In 2022 willen we deze aanvragen even efficiënt kunnen behandelen en willen we geen tijd verliezen met het invullen en ondertekenen van de interventieovereenkomst.'). '</p>';
      $body .= '<p>' .t('Daarom vindt u hieronder de link met de nieuwe jaarovereenkomst waarbij de interventievoorwaarden voor 2022 worden verduidelijkt. Wij vragen u vriendelijk deze overeenkomst in te vullen voor akkoord.'). '</p>';
      $body .= '<p>https://my.abcparts.be/'.$lang.'/agreementfs?date=1/1/2022 </p>';
      $body .= '<p>' .t('Zonder getekende overeenkomst zullen er geen interventies uitgevoerd worden.'). '</p>';
      $body .= '<p>' .t('Mocht u verdere vragen hebben, aarzel niet om ons te contacteren.'). '</p>';
      $body .= '<p>' .t('Het ABC Team'). '</p>';

      $fullBody = $bodyHeader . $body . $bodyFooter;

      return $fullBody;
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

  }
?>
