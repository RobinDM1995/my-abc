<?php

/**
 * @project:   PDFDesigner (concrete5 add-on)
 *
 * @author     Fabian Bitter
 * @copyright  (C) 2016 Fabian Bitter (www.bitter.de)
 * @version    1.2.1
 */

namespace Concrete\Package\PdfDesigner\Src\BoxEditor;

use Concrete\Package\PdfDesigner\Src\BoxEditor;
use Concrete\Package\PdfDesigner\Src\IBoxEditor;
use Concrete\Package\PdfDesigner\Src\PDFDesigner;
use Concrete\Package\PdfDesigner\Src\Helpers;

defined('C5_EXECUTE') or die("Access Denied.");

class Table extends BoxEditor implements IBoxEditor
{
    public function getImage()
    {
        return Helpers::generateURL("/packages/pdf_designer/images/box_icons/table.png");
    }
    
    public function getFontNames()
    {
        return array_merge(
            array(
                "Courier",
                "Helvetica",
                "Arial",
                "Times",
                "Symbol",
                "ZapfDingbats"
            ),
                
            PDFDesigner::getInstance($this->getTemplateId())->getCustomFonts()
        );
    }
    
    public function getFontSizes()
    {
        return array(5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 18, 20, 22, 24, 26, 28, 32, 36, 40);
    }
    
    public function getViewPath()
    {
        return '/dialogs/box_editors/table';
    }
    
    public function renderView()
    {
        $this->getPage()->set("box", $this);
    }
    
    public function renderPDF(&$pdf, $x, $y, $w, $h)
    {
        $pdf->SetFont($this->getAttribute("FontName", "Arial"), '', $this->getAttribute("FontSize", "10"));
        
        $dataSource = $this->getAttribute("DataSource");
        
        if (substr($dataSource, 0, 1) === "{" && substr($dataSource, strlen($dataSource) - 1) === "}") {
            $dataSource = substr($dataSource, 1, strlen($dataSource) - 2);
        }
        
        if (isset($this->getPlaceholders()[$dataSource])) {
            $data = $this->getPlaceholders()[$dataSource];
            
            if (isset($data["columns"]) && isset($data["rows"])) {
                $pdf->AddTable(
                    $data["columns"],
                    $data["rows"],
                    $this->getAttribute("FontColor", "#000000"),
                    $this->getAttribute("BorderColor", "#000000"),
                    $x,
                    $y,
                    $w,
                    $h
                );
            }
        }
    }
}
