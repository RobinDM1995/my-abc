<?php
namespace Concrete\Package\MyAbc\Src;

use Concrete\Package\MyAbc\Src\Functions;
use Package;
//
  class PDF extends \FPDF{
    public function header(){

    }

    public function footer(){

    }
  }
//
  class ReceiptPdf{
    public function mailPdf($custData){
      Functions::setLang($custData['lang']);
      $pdf = new PDF();
      $pdf->SetAutoPageBreak(1, 31);
      $pdf->SetMargins(20, 30, 20);
      $pdf->AliasNbPages();
      $pdf->AddPage();
      $pdf->setSubject(t('Acknowledgement of receipt'));

      if($custData != null){
        $im = New \imagick(); //Library to convert svg to png
        $svgSign = file_get_contents($custData['sign']);
        $im->readImageBlob($svgSign);
        $im->setImageFormat('png24');
        $im->resizeImage(360, 240, \imagick::FILTER_LANCZOS, 1); // resize image
        $im->writeImage('png24:sign.png'); // png24 needs to be declared because otherwise the png will be 16bit and fpdf doesn't support that
        header('Content-type: image/png');

        $sign = $im;
        $im->clear();
        $im->destroy();
      }
      $h = 6;

      //TITLE
      $yTop = $pdf->GetY(); //get y top for the height of side borders

      $pdf->SetFont('Arial', 'B', 16);
      $pdf->SetTextColor(116,185,67); //ABC green
      $pdf->SetDrawColor(116,185,67);
      $pdf->SetLineWidth(1);
      $pdf->Cell(0, 10, '', 'T', 1, 'L'); // TOP GREEN BORDER
      $pdf->Cell(0, $h, iconv('UTF-8', 'CP1256', t('Acknowledgement of receipt')), 0, 1, 'R');
      $pdf->Cell(0, 12, '',0,1,'L'); //Add some whitespace under the title

      //CLIENT + COMPANY DATA
      $xTop = $pdf->GetX();
      $pdf->SetFont('Arial', '', 12);
      $pdf->SetTextColor(0,0,0);
      $pdf->Cell(40, $h, iconv('UTF-8', 'CP1256', t('The undersigned')), 0, 0, 'L');
      // $pdf->SetFont('Arial', 'B', 12);
      $pdf->Cell(0, $h, iconv('UTF-8', 'CP1256', $custData['contact']), 0, 1, 'L');
      // $pdf->SetFont('Arial', '', 12);
      $pdf->Cell(40, $h, iconv('UTF-8', 'CP1256', t('Domiciled in ')), 0, 0, 'L');
      // $pdf->SetFont('Arial', 'B', 12);
      $pdf->MultiCell(0, $h, iconv('UTF-8', 'CP1256', $custData['streetNr'] . "\n" . $custData['zipcode'] . ' - ' . $custData['city']));

      // $pdf->SetFont('Arial', '', 12);

      $pdf->Cell(0, $h, '',0,1,'L'); //whitespace
      $pdf->Cell(0, $h, iconv('UTF-8', 'CP1256', t('Acting in name and for account of ')), 0, 1, 'L');
      $pdf->Cell(40, $h, '', 0, 0, 'L'); //whitespace
      // $pdf->SetFont('Arial', 'B', 12);
      $pdf->MultiCell(0, $h, iconv('UTF-8', 'CP1256', $custData['compName'] . "\n" . $custData['compStreetNr'] . "\n" . $custData['compZipcode'] . ' - ' . $custData['compCity']));
      // $pdf->SetFont('Arial', '', 12);

      //INVOICE DATA
      $pdf->Cell(0, 12, '', 0, 1, 'L'); //whitespace
      $pdf->Cell(0, $h, iconv('UTF-8', 'CP1256', t('Declares:')), 0, 1, 'L');
      $pdf->Cell(0, 3, '', 0,1,'L');  //whitespace
      $pdf->Cell(5, $h, chr(127), 0, 0, 'L'); //Bullet
      $pdf->MultiCell(0, $h, iconv('UTF-8', 'CP1256', t('to have received and picked up the following goods (being the subject of invoice n° %1$s d.d. %2$s) on %3$s:', $custData['invoiceNr'], date('d/m/Y', strtotime($custData['invoiceDate'])), date('d/m/Y'))));
      // $pdf->SetFont('Arial', 'B', 12);
      $pdf->Cell(5, $h, '', 0, 0, 'L'); //Bullet
      $pdf->MultiCell(0, $h, iconv('UTF-8', 'CP1256', $custData['desc']), 0);
      $pdf->SetFont('Arial', '', 12);
      $pdf->Cell(5, $h, chr(127), 0, 0, 'L'); //Bullet
      $pdf->Cell(97, $h, iconv('UTF-8', 'CP1256', t('destined to a location outside of the Community i.e.')), 0, 0, 'L');
      $pdf->Cell(0, $h, iconv('UTF-8', 'CP1256', $custData['country']),0, 1,'L');
      $pdf->Cell(0, 12, '',0,1,'L');

      //SIGNATURE + GEN INFO DOC
      $pdf->Cell(0, $h, iconv('UTF-8', 'CP1256', t('Drawn up in %1$s', $custData['country'])), 0, 1, 'L');
      $pdf->Cell(0, $h, t('On %1$s', date('d/m/Y')),0,1,'L');
      $pdf->Cell(80, $h, '', 0,0,'L');
      $pdf->Cell(0, $h, t('Signature, '), 0, 1, 'L');

      $pdf->Cell(0, $h, '', 0, 1, 'L');
      $pdf->Image('sign.png', 90, $yBot, 'png');

      $pdf->Cell(0, 3, '', 'B', 1, 'L'); // BOTTOM GREEN BORDER
      $yBot = $pdf->GetY(); //Get y bottom to calc side border height

      $pdf->SetXY($xTop, $yTop); //Start cell at the top
      $pdf->Cell(0, $yBot - $yTop, '', 'LR', 0, 'L'); //Left and right border


      $datum = date('d/m/Y');
      ob_get_clean();
      // $pdf->Output(); // Show pdf in browser
  	  $pdf->Output('F', '/var/web/vd16778/public_html/application/files/receipt/receipt-' . $custData['invoiceNr'] . str_replace('/', '', $datum) .'.pdf'); //Save pdf
    }
  }
?>
