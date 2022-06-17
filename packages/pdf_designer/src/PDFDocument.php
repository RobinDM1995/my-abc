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

use Concrete\Core\Entity\File\Version;
use Concrete\Package\PdfDesigner\Src\Entity\Template;
use Concrete\Package\PdfDesigner\Src\Helpers;
use File;

class PDFDocument extends \FPDI
{
    protected $_tplIdx;
    private $template;
    protected $B;
    protected $I;
    protected $U;
    protected $HREF;
    protected $fontList;
    protected $issetfont;
    protected $issetcolor;
    protected $fontpath;
    protected $widths;
    protected $aligns;
    protected $FontFamily;
    protected $FontSize;
    protected $FontStyle;
    protected $lMargin;
    protected $palign = "L";

    public function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
        if ($txt != '') {
            if (function_exists("iconv")) {
                $txt = iconv("UTF-8", "CP1252", $txt);
            } else {
                $txt = utf8_decode($txt);
            }
        }
      
        parent::Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
    }

    public function setFillColorByUserInput($userInput)
    {
        if (substr($userInput, 0, 1) === "#") {
            $coul = $this->hex2dec($userInput);
        
            $this->SetFillColor($coul['R'], $coul['G'], $coul['B']);
        } else {
            $coul = explode(",", $userInput);
            
            if (count($coul) === 4) {
                $this->SetFillColor(intval($coul[0]), intval($coul[1]), intval($coul[2]), intval($coul[3]));
            }
        }
    }

    public function SetTextColorByUserInput($userInput)
    {
        if (substr($userInput, 0, 1) === "#") {
            $coul = $this->hex2dec($userInput);
        
            $this->SetTextColor($coul['R'], $coul['G'], $coul['B']);
        } else {
            $coul = explode(",", $userInput);
            
            if (count($coul) === 4) {
                $this->SetTextColor(intval($coul[0]), intval($coul[1]), intval($coul[2]), intval($coul[3]));
            }
        }
    }

    public function SetDrawColorByUserInput($userInput)
    {
        if (substr($userInput, 0, 1) === "#") {
            $coul = $this->hex2dec($userInput);
        
            $this->SetDrawColor($coul['R'], $coul['G'], $coul['B']);
        } else {
            $coul = explode(",", $userInput);
            
            if (count($coul) === 4) {
                $this->SetDrawColor(intval($coul[0]), intval($coul[1]), intval($coul[2]), intval($coul[3]));
            }
        }
    }

    public function SetDrawColor($r, $g = null, $b = null)
    {
        //Set color for all stroking operations
        switch (func_num_args()) {
            case 1:
                $g = func_get_arg(0);
                $this->DrawColor = sprintf('%.3F G', $g / 100);
                break;
            case 3:
                $r = func_get_arg(0);
                $g = func_get_arg(1);
                $b = func_get_arg(2);
                $this->DrawColor = sprintf('%.3F %.3F %.3F RG', $r / 255, $g / 255, $b / 255);
                break;
            case 4:
                $c = func_get_arg(0);
                $m = func_get_arg(1);
                $y = func_get_arg(2);
                $k = func_get_arg(3);
                $this->DrawColor = sprintf('%.3F %.3F %.3F %.3F K', $c / 100, $m / 100, $y / 100, $k / 100);
                break;
            default:
                $this->DrawColor = '0 G';
        }
        if ($this->page > 0) {
            $this->_out($this->DrawColor);
        }
    }

    public function SetFillColor($r, $g = null, $b = null)
    {
        //Set color for all filling operations
        switch (func_num_args()) {
            case 1:
                $g = func_get_arg(0);
                $this->FillColor = sprintf('%.3F g', $g / 100);
                break;
            case 3:
                $r = func_get_arg(0);
                $g = func_get_arg(1);
                $b = func_get_arg(2);
                $this->FillColor = sprintf('%.3F %.3F %.3F rg', $r / 255, $g / 255, $b / 255);
                break;
            case 4:
                $c = func_get_arg(0);
                $m = func_get_arg(1);
                $y = func_get_arg(2);
                $k = func_get_arg(3);
                $this->FillColor = sprintf('%.3F %.3F %.3F %.3F k', $c / 100, $m / 100, $y / 100, $k / 100);
                break;
            default:
                $this->FillColor = '0 g';
        }
        $this->ColorFlag = ($this->FillColor != $this->TextColor);
        if ($this->page > 0) {
            $this->_out($this->FillColor);
        }
    }

    public function SetTextColor($r, $g = null, $b = null)
    {
        //Set color for text
        switch (func_num_args()) {
            case 1:
                $g = func_get_arg(0);
                $this->TextColor = sprintf('%.3F g', $g / 100);
                break;
            case 3:
                $r = func_get_arg(0);
                $g = func_get_arg(1);
                $b = func_get_arg(2);
                $this->TextColor = sprintf('%.3F %.3F %.3F rg', $r / 255, $g / 255, $b / 255);
                break;
            case 4:
                $c = func_get_arg(0);
                $m = func_get_arg(1);
                $y = func_get_arg(2);
                $k = func_get_arg(3);
                $this->TextColor = sprintf('%.3F %.3F %.3F %.3F k', $c / 100, $m / 100, $y / 100, $k / 100);
                break;
            default:
                $this->TextColor = '0 g';
        }
        $this->ColorFlag = ($this->FillColor != $this->TextColor);
    }

    public function SetWidths($w)
    {
        //Set the array of column widths
        $this->widths = $w;
    }

    public function SetAligns($a)
    {
        //Set the array of column alignments
        $this->aligns = $a;
    }

    public function Row($data, $initX)
    {
        //Calculate the height of the row

        $data = array_values($data);
        //print_r($data);die;
        $nb = 0;
        for ($i = 0; $i < count($data); $i++) {
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        }
        $h = 7 * $nb;
        //Issue a page break first if needed
        $this->CheckPageBreak($h);
        //Draw the cells of the row
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            if (is_numeric($data[$i]) && $i > 0) {
                $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'R';
                $data[$i] = number_format((float) $data[$i], 2, '.', '');
            } else {
                $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            }
            //Save the current position
            $x = $this->GetX();
            $y = $this->GetY();

            if ($x - $this->lMargin < $initX) {
                $x = $initX;
                $this->SetX($x);
            }

            //Draw the border
            $this->Rect($x, $y, $w, $h);
            //Print the text
            $this->MultiCell($w, 7, $data[$i], 0, $a);
            //Put the position to the right of the cell
            $this->SetXY($x + $w, $y);
        }
        //Go to the next line
        $this->Ln($h);
        $this->SetX($initX);
    }

    public function CheckPageBreak($h)
    {
        //If the height h would cause an overflow, add a new page immediately
        if ($this->GetY() + $h > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
        }
    }

    public function NbLines($w, $txt)
    {
        //Computes the number of lines a MultiCell of width w will take
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0) {
            $w = $this->w - $this->rMargin - $this->x;
        }
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n") {
            $nb--;
        }
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ') {
                $sep = $i;
            }
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j) {
                        $i++;
                    }
                } else {
                    $i = $sep + 1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else {
                $i++;
            }
        }
        return $nl;
    }

    public function AddTable($columns, $rows, $fontColor, $borderColor, $x, $y, $w, $h)
    {
        $this->SetDrawColorByUserInput($borderColor);

        $this->SetTextColorByUserInput($fontColor);

        $this->setXY($x, $y);

        $cellWidth = intval($w / count($columns));

        $width = array();
        $aligns = array();

        $originalFontStyle = $this->FontStyle;

        $this->SetFont($this->FontFamily, "B", $this->FontSizePt);

        foreach ($columns as $column) {
            $text = $column["text"];

            array_push($width, $cellWidth);

            array_push($aligns, $column["align"]);

            $this->Rect($this->GetX(), $this->getY(), $cellWidth, 7);

            $this->Cell($cellWidth, 7, $text, 0, 0, $column["align"]);
        }

        $this->SetFont($this->FontFamily, $originalFontStyle, $this->FontSizePt);

        $this->SetWidths($width);
        $this->SetAligns($aligns);

        $this->Ln();

        $this->setX($x);

        // Gebe Positionen aus
        foreach ($rows as $row) {
            $this->Row($row, $x);
        }
    }

    public function getFontpath()
    {
        return $this->fontpath;
    }

    public function setFontpath($fontpath)
    {
        $this->fontpath = $fontpath;
    }

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4')
    {
        //Call parent constructor
        parent::__construct($orientation, $unit, $format);

        //Initialization
        $this->B = 0;
        $this->I = 0;
        $this->U = 0;
        $this->HREF = '';

        $this->tableborder = 0;
        $this->tdbegin = false;
        $this->tdwidth = 0;
        $this->tdheight = 0;
        $this->tdalign = "L";
        $this->tdbgcolor = false;

        $this->oldx = 0;
        $this->oldy = 0;

        $this->fontlist = array("arial", "times", "courier", "helvetica", "symbol");
        $this->issetfont = false;
        $this->issetcolor = false;
    }

    public function getK()
    {
        return $this->k;
    }

    public function hex2dec($couleur = "#000000")
    {
        $R = substr($couleur, 1, 2);
        $rouge = hexdec($R);
        $V = substr($couleur, 3, 2);
        $vert = hexdec($V);
        $B = substr($couleur, 5, 2);
        $bleu = hexdec($B);
        $tbl_couleur = array();
        $tbl_couleur['R'] = $rouge;
        $tbl_couleur['G'] = $vert;
        $tbl_couleur['B'] = $bleu;
        return $tbl_couleur;
    }

    public function px2mm($px)
    {
        return $px * 25.4 / 72;
    }

    public function txtentities($html)
    {
        $trans = get_html_translation_table(HTML_ENTITIES);
        $trans = array_flip($trans);
        return strtr($html, $trans);
    }

    public function WriteHTML($html, $fontColor, $x, $y, $width, $height)
    {
        $html = str_replace("style=\"text-align: right;\"", "align=\"right\"", $html);
        $html = str_replace("style=\"text-align: left;\"", "align=\"left\"", $html);
        $html = str_replace("style=\"text-align: center;\"", "align=\"center\"", $html);

        $this->SetTextColorByUserInput($fontColor);

        $html = strip_tags($html, "<b><u><i><a><img><p><br><strong><em><font><tr><blockquote><hr><td><tr><table><sup>"); //remove all unsupported tags
        $html = str_replace("\n", '', $html); //replace carriage returns with spaces
        $html = str_replace("\t", '', $html); //replace carriage returns with spaces
        $a = preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE); //explode the string

        $this->SetXY($x, $y);

        $oldLeftMargin = $this->lMargin;
        $oldRightMargin = $this->rMargin;
        $oldWidth = $this->w;

        $this->lMargin = $x;
        $this->rMargin = 0;
        $this->w = $width;

        foreach ($a as $i => $e) {
            if ($i % 2 == 0) {
                //Text
                if ($this->HREF) {
                    $this->PutLink($this->HREF, $e);
                } elseif ($this->tdbegin) {
                    if (trim($e) != '' && $e != "&nbsp;") {
                        $this->Cell($this->tdwidth, $this->tdheight, $e, $this->tableborder, '', $this->tdalign, $this->tdbgcolor);
                    } elseif ($e == "&nbsp;") {
                        $this->Cell($this->tdwidth, $this->tdheight, '', $this->tableborder, '', $this->tdalign, $this->tdbgcolor);
                    }
                } else {
                    $this->w = $width + $this->x;

                    if ($this->palign === "L") {
                        $this->Write(5, stripslashes($this->txtentities($e)));
                    } else {
                        $this->MultiCell($width, 0, stripslashes($this->txtentities($e)), 0, $this->palign);
                    }


                    $this->w = $width;
                }
            } else {
                //Tag
                if ($e[0] == '/') {
                    $this->CloseTag(strtoupper(substr($e, 1)));
                } else {
                    //Extract attributes
                    $a2 = explode(' ', $e);
                    $tag = strtoupper(array_shift($a2));
                    $attr = array();
                    foreach ($a2 as $v) {
                        if (preg_match('/([^=]*)=["\']?([^"\']*)/', $v, $a3)) {
                            $attr[strtoupper($a3[1])] = $a3[2];
                        }
                    }

                    $this->OpenTag($tag, $attr);
                }
            }
        }

        $this->lMargin = $oldLeftMargin;
        $this->rMargin = $oldRightMargin;
        $this->w = $oldWidth;
    }

    public function OpenTag($tag, $attr)
    {
        //Opening tag
        switch ($tag) {

            case 'SUP':
                if (!empty($attr['SUP'])) {
                    //Set current font to 6pt
                    $this->SetFont('', '', 6);
                    //Start 125cm plus width of cell to the right of left margin
                    //Superscript "1"
                    $this->Cell(2, 2, $attr['SUP'], 0, 0, 'L');
                }
                break;

            case 'TABLE': // TABLE-BEGIN
                if (!empty($attr['BORDER'])) {
                    $this->tableborder = $attr['BORDER'];
                } else {
                    $this->tableborder = 0;
                }
                break;
            case 'TR': //TR-BEGIN
                break;
            case 'TD': // TD-BEGIN
                if (!empty($attr['WIDTH'])) {
                    $this->tdwidth = ($attr['WIDTH'] / 4);
                } else {
                    $this->tdwidth = 40;
                } // Set to your own width if you need bigger fixed cells
                if (!empty($attr['HEIGHT'])) {
                    $this->tdheight = ($attr['HEIGHT'] / 6);
                } else {
                    $this->tdheight = 6;
                } // Set to your own height if you need bigger fixed cells
                if (!empty($attr['ALIGN'])) {
                    $align = $attr['ALIGN'];
                    if ($align == 'LEFT') {
                        $this->tdalign = 'L';
                    }
                    if ($align == 'CENTER') {
                        $this->tdalign = 'C';
                    }
                    if ($align == 'RIGHT') {
                        $this->tdalign = 'R';
                    }
                } else {
                    $this->tdalign = 'L';
                } // Set to your own
                if (!empty($attr['BGCOLOR'])) {
                    $this->setFillColorByUserInput($attr['BGCOLOR']);
                    $this->tdbgcolor = true;
                }
                $this->tdbegin = true;
                break;

            case 'HR':
                if (!empty($attr['WIDTH'])) {
                    $Width = $attr['WIDTH'];
                } else {
                    $Width = $this->w - $this->lMargin - $this->rMargin;
                }
                $this->Ln(1);
                $x = $this->GetX();
                $y = $this->GetY();
                $this->SetLineWidth(0.2);
                $this->Line($x, $y, $x + $Width, $y);
                $this->SetLineWidth(0.2);
                $this->Ln(1);
                break;
            case 'STRONG':
                $this->SetStyle('B', true);
                break;
            case 'EM':
                $this->SetStyle('I', true);
                break;
            case 'B':
            case 'I':
            case 'U':
                $this->SetStyle($tag, true);
                break;
            case 'A':
                $this->HREF = $attr['HREF'];
                break;
            case 'IMG':
                if (isset($attr['SRC']) && (isset($attr['WIDTH']) || isset($attr['HEIGHT']))) {
                    if (!isset($attr['WIDTH'])) {
                        $attr['WIDTH'] = 0;
                    }
                    if (!isset($attr['HEIGHT'])) {
                        $attr['HEIGHT'] = 0;
                    }
                    $this->Image($attr['SRC'], $this->GetX(), $this->GetY(), $this->px2mm($attr['WIDTH']), $this->px2mm($attr['HEIGHT']));
                }
                break;
            case 'BLOCKQUOTE':
            case 'BR':
                $this->Ln(5);
                break;
            case 'P':
                $this->palign = 'L';

                if (!empty($attr['ALIGN'])) {
                    $align = strtoupper($attr['ALIGN']);
                    if ($align == 'LEFT') {
                        $this->palign = 'L';
                    }
                    if ($align == 'CENTER') {
                        $this->palign = 'C';
                    }
                    if ($align == 'RIGHT') {
                        $this->palign = 'R';
                    }
                }
                $this->Ln(10);
                break;
            case 'FONT':
                if (isset($attr['COLOR']) && $attr['COLOR'] != '') {
                    $this->SetTextColorByUserInput($attr['COLOR']);
                    $this->issetcolor = true;
                }
                if (isset($attr['FACE']) && in_array(strtolower($attr['FACE']), $this->fontlist)) {
                    $this->SetFont(strtolower($attr['FACE']));
                    $this->issetfont = true;
                }
                if (isset($attr['FACE']) && in_array(strtolower($attr['FACE']), $this->fontlist) && isset($attr['SIZE']) && $attr['SIZE'] != '') {
                    $this->SetFont(strtolower($attr['FACE']), '', $attr['SIZE']);
                    $this->issetfont = true;
                }
                break;
        }
    }

    public function CloseTag($tag)
    {
        //Closing tag
        if ($tag == 'SUP') {
        }

        if ($tag == 'TD') { // TD-END
            $this->tdbegin = false;
            $this->tdwidth = 0;
            $this->tdheight = 0;
            $this->tdalign = "L";
            $this->tdbgcolor = false;
        }
        if ($tag == 'TR') { // TR-END
            $this->Ln();
        }
        if ($tag == 'TABLE') { // TABLE-END
            $this->tableborder = 0;
        }

        if ($tag == 'STRONG') {
            $tag = 'B';
        }
        if ($tag == 'EM') {
            $tag = 'I';
        }
        if ($tag == 'B' || $tag == 'I' || $tag == 'U') {
            $this->SetStyle($tag, false);
        }
        if ($tag == 'A') {
            $this->HREF = '';
        }
        if ($tag == 'FONT') {
            if ($this->issetcolor == true) {
                $this->SetTextColor(0);
            }
            if ($this->issetfont) {
                $this->SetFont('arial');
                $this->issetfont = false;
            }
        }
    }

    public function SetStyle($tag, $enable)
    {
        //Modify style and select corresponding font
        $this->$tag += ($enable ? 1 : -1);
        $style = '';
        foreach (array('B', 'I', 'U') as $s) {
            if ($this->$s > 0) {
                $style .= $s;
            }
        }
        $this->SetFont('', $style);
    }

    public function PutLink($URL, $txt)
    {
        //Put a hyperlink
        $this->SetTextColor(0, 0, 255);
        $this->SetStyle('U', true);
        $this->Write(5, $txt, $URL);
        $this->SetStyle('U', false);
        $this->SetTextColor(0);
    }

    /**
     *
     * @return Template
     */
    public function getTemplate()
    {
        return $this->template;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public function Header()
    {
        if (null === $this->_tplIdx) {
            if (intval($this->getTemplate()->getLetterPaperFirstPageFileId()) > 0) {
                /** @var $sourceFile Version */
                $sourceFile = File::getByID($this->getTemplate()->getLetterPaperFirstPageFileId());

                $this->setSourceFile(Helpers::createTempFile($sourceFile->getFileContents()));

                $this->_tplIdx = $this->importPage(1);

                $this->useTemplate($this->_tplIdx);
            }
        } else {
            // Following page+
            if (intval($this->getTemplate()->getLetterPaperFollowingPageFileId()) > 0) {
                /** @var $sourceFile Version */
                $sourceFile = File::getByID($this->getTemplate()->getLetterPaperFollowingPageFileId());

                $this->setSourceFile(Helpers::createTempFile($sourceFile->getFileContents()));

                $this->_tplIdx = $this->importPage(1);

                $this->useTemplate($this->_tplIdx);
            } else {
                $this->_tplIdx = null;
            }
        }
    }
}
