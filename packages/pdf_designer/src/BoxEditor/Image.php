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
use Concrete\Package\PdfDesigner\Src\Helpers;
use File;
use Core;

defined('C5_EXECUTE') or die("Access Denied.");

class Image extends BoxEditor implements IBoxEditor
{
    public function getImage()
    {
        return Helpers::generateURL("/packages/pdf_designer/images/box_icons/image.png");
    }
    
    public function getViewPath()
    {
        return '/dialogs/box_editors/image';
    }
    
    public function renderView()
    {
        $this->getPage()->set("box", $this);
    }
    
    public function renderPDF(&$pdf, $x, $y, $w, $h)
    {
        $imageUrl = $this->applyPlaceholders($this->getAttribute("ImageUrl"));
        
        $fileId = intval($this->getAttribute("Image"));

        $f = File::getByID($fileId);
        
        if ($fileId > 0 && is_object($f)) {
            $imageFile = Helpers::getAbsolutePath($f);
            
            if ($imageFile !== false) {
                // Get the dimensions of the image
                $imageWidth = $f->getAttribute('width');
                $imageHeight = $f->getAttribute('height');
                $imageRatio = $imageWidth / $imageHeight;

                // Calculate the ratio of the bounding box
                $boxRatio = $w / $h;

                // Make sure the image fits into the bounding box
                if ($imageRatio < $boxRatio) {

                    // Case: Bounding Box is wider than the image.

                    // Make the box smaller
                    $newWidth = $imageRatio * $h;

                    // center the image by adding half of the difference to the horizontal offset
                    $x += ($w - $newWidth) / 2;

                    // apply changes
                    $w = $newWidth;
                } else {

                    // Case: Bounding Box is heigher than the image.

                    // Make the box smaller
                    $newHeight = $w / $imageRatio;

                    // center the image by adding half of the difference to the vertical offset
                    $y += ($h - $newHeight) / 2;

                    // apply changes
                    $h = $newHeight;
                }

                $pdf->Image(
                    $imageFile,
                    $x,
                    $y,
                    $w,
                    $h
                );
            }
        } elseif (strlen($imageUrl) > 0) {
            $imageData = Helpers::fetchUrl($imageUrl);
            
            if (strlen($imageData) > 0) {
                $imageFile = Helpers::generateTempFile() . "." . $this->getAttribute("ImageFileType");

                $fileHelper = Core::make('helper/file');

                $fileHelper->append($imageFile, $imageData);

                $pdf->Image(
                    $imageFile,
                    $x,
                    $y,
                    $w,
                    $h
                );

                @unlink($imageFile);
            }
        }
    }
}
