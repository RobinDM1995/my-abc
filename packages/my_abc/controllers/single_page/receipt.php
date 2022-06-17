<?php
  namespace Concrete\Package\MyAbc\Controller\SinglePage;
  use Concrete\Core\Page\Controller\PageController;
  use Concrete\Package\PdfDesigner\Src\PDFDesigner;
  use Concrete\Package\FormidableFull\Src\Formidable;
  use Concrete\Package\FormidableFull\Src\Formidable\Result;
  use Concrete\Package\FormidableFull\Src\Formidable\Element;
  use Concrete\Package\MyAbc\Src\sendmail;
  use Concrete\Package\MyAbc\Src\ReceiptPdf;
  use Concrete\Package\MyAbc\Src\Functions;
  use Concrete\Core\File\Importer;
  use Concrete\Package\FormidableFull\Src\Formidable\Element\Signature;

  use Localization;
  use Core;
  use Loader;

  class receipt extends PageController{
    public function view(){
      $lang = localization::activeLanguage();

      switch($lang){
        default:
        case 'nl':
        $fID = 11;
        break;

        case 'en':
        $fID = 17;
        break;

        case 'fr':
        $fID = 18;
        break;
      }

      $form = new Formidable();
      $result = new Result();
      $element = new Element();
      $asID = $_SESSION['asID'];
      $userIP = $form->getIP();

      $answers = $result->getAnswersByID($fID, $asID); //Get answer set with asID

      //form answers will be in the same order for each language and each answerset
      $custData = array();
      $custData['contact'] = $answers[0]['answer_formated'];
      $custData['email'] = $answers[1]['answer_formated'];
      $custData['streetNr'] = $answers[2]['answer_formated'];
      $custData['city'] = $answers[3]['answer_formated'];
      $custData['zipcode'] = $answers[4]['answer_formated'];
      $custData['compName'] = $answers[5]['answer_formated'];
      $custData['compStreetNr'] = $answers[6]['answer_formated'];
      $custData['compCity'] = $answers[7]['answer_formated'];
      $custData['compZipcode'] = $answers[8]['answer_formated'];
      $custData['country'] = $answers[9]['answer_formated'];
      $custData['desc'] = $answers[10]['answer_formated'];
      $custData['invoiceNr'] = $answers[11]['answer_formated'];
      $custData['invoiceDate'] = $answers[12]['answer_formated'];
      $signature = explode('"', $answers[13]['answer_unformated']);
      $custData['sign'] = $signature[3];

      $custData['lang'] = $lang;

      $pdf = new ReceiptPdf();

      $signature = new Signature();
      $signature->getDisplayResult();

      $pdf->mailPdf($custData);

      $this->set('custData', $custData);
      $this->set('userIP', $userIP);
      $this->sendMail($custData);
    }

    public function sendMail($custData){
      Functions::setLang($custData['lang']);

      $mailData = array();

      $mailSubject = t('Thank you for filling in the acknowledgement of receipt');
      $email = $custData['email'];


      $body = '<h2>' . t('Thank you for acknowledging your receipt'). '</h2>';
      $body .= '<p>' . t('Dear %1$s, ', $custData['contact']) . '</p>';
      $body .= '<p>' . t('We hope you are happy with the product'). '</p>';
      $body .= '<p>' . t('You can find your receipt in the attachments');

      $datum = date('d/m/Y');
      $file = '/var/web/vd16778/public_html/application/files/receipt/receipt-' . $custData['invoiceNr']. str_replace('/', '', $datum).'.pdf';
      $filename = 'receipt-' . $custData['invoiceNr'] . '-' . str_replace('/', '', $datum) .'.pdf';

      $importer = new Importer();
      $file = $importer->import($file, $filename);
      $fID = $file->getFileID();

      $mailData['subject'] = $mailSubject;
      $mailData['email'] = $email;
      $mailData['body'] = $body;
      $mailData['attachmentID'] = $fID;
      $mail = new sendmail();

      $mail->setupmail($mailData);
    }
  }
?>
