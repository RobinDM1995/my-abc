<?php
  namespace Concrete\Package\MyAbc\Src;

  use Loader;

  class Sendmail{
    public function setupmail($mailData){
      // echo '<pre>';
      // print_r($mailData);
      // exit;
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

        $mh = Loader::helper('mail');
        // echo $mailData['attachmentID'];
        // exit;
        if($mailData['attachmentID']){
          $attachment = \Concrete\Core\File\File::getByID($mailData['attachmentID']);
        }

        $bodyFull = $bodyHeader . $mailData['body'] . $bodyFooter;

        $mh->setSubject($mailData['subject']);
        $mh->setBodyHTML($bodyFull);
        $mh->getBodyHTML();
        $mh->addAttachment($attachment);
        $mh->from('info@abcparts.be', 'ABC parts Info');
        // $mh->bcc('abccontrolemail@gmail.com');
        // $mh->to($mailData['email']);
        $mh->to('rdm@abcparts.be');
        // $mh->cc('info@abcparts.be');

        $mh->sendmail();
    }
  }
?>
