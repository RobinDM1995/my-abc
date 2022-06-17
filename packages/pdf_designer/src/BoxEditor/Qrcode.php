<?php

namespace Concrete\Package\PdfDesigner\Src\BoxEditor;

defined('C5_EXECUTE') or die("Access Denied.");

use Concrete\Package\PdfDesigner\Src\BoxEditor;
use Concrete\Package\PdfDesigner\Src\IBoxEditor;
use Concrete\Package\PdfDesigner\Src\Qrcode as QrcodeGenerator;

class Qrcode extends BoxEditor implements IBoxEditor
{
    public function getViewPath()
    {
        return '../../pdf_designer/views/dialogs/box_editors/qrcode';
    }

    public function renderView()
    {
        $this->getPage()->set("box", $this);
    }


    public function renderPDF(&$pdf, $x, $y, $w, $h)
    {
        $data = $this->renderText($this->getAttribute("Data"));

        if ($data === "") {
            $data = "hhtp://www.bitter.de";
        }

        $qrCode = new QrcodeGenerator($data, 'L');
        $qrCode->disableBorder();
        $qrCode->displayFPDF($pdf, $x, $y, min($w, $h), array(255,255,255), array(0,0,0));
    }
}
