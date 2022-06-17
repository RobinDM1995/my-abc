<?php

/**
 * @project:   PDFDesigner (concrete5 add-on)
 *
 * @author     Fabian Bitter
 * @copyright  (C) 2016 Fabian Bitter (www.bitter.de)
 * @version    1.2.1
 */

namespace Concrete\Package\PdfDesigner\Src;

defined('C5_EXECUTE') or die("Access Denied.");

interface IBoxEditor
{
    public function getViewPath();
    public function renderView();
    public function renderPDF(&$pdf, $x, $y, $w, $h);
}
