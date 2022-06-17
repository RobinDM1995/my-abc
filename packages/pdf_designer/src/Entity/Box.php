<?php

/**
 * @project:   PDFDesigner (concrete5 add-on)
 *
 * @author     Fabian Bitter
 * @copyright  (C) 2016 Fabian Bitter (www.bitter.de)
 * @version    1.2.1
 */

namespace Concrete\Package\PdfDesigner\Src\Entity;

defined('C5_EXECUTE') or die("Access Denied.");

use Concrete\Package\PdfDesigner\Src\BoxType;
use Concrete\Package\PdfDesigner\Src\PDFDesigner;
use Database;

/**
 * @Entity
 * @Table(name="PdfDesignerBox")
 * */
class Box
{

    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $boxId;

    /**
     * @Column(type="integer") * */
    protected $templateId;

    /**
     * @Column(type="integer") * */
    protected $positionType = 0;

    /**
     * @Column(type="integer") * */
    protected $xPos;

    /**
     * @Column(type="integer") * */
    protected $yPos;

    /**
     * @Column(type="integer") * */
    protected $width;

    /**
     * @Column(type="integer") * */
    protected $height;

    /**
     * @Column(type="decimal", precision=7, scale=4) * */
    protected $xPosInch;

    /**
     * @Column(type="decimal", precision=7, scale=4) * */
    protected $yPosInch;

    /**
     * @Column(type="decimal", precision=7, scale=4) * */
    protected $widthInch;

    /**
     * @Column(type="decimal", precision=7, scale=4) * */
    protected $heightInch;

    /**
     * @Column(type="string") * */
    protected $boxType;
    
    private static $instance = null;
    
    public function getBoxId()
    {
        return $this->boxId;
    }

    public function getXPos()
    {
        return $this->xPos;
    }

    public function getYPos()
    {
        return $this->yPos;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getBoxType()
    {
        if (strlen($this->boxType) === 0) {
            return "\Concrete\Package\PdfDesigner\Src\BoxEditor\TextArea";
        } else {
            return $this->boxType;
        }
    }

    public function setBoxId($boxId)
    {
        $this->boxId = $boxId;
    }

    public function setXPos($xPos)
    {
        $this->xPos = $xPos;
    }

    public function setYPos($yPos)
    {
        $this->yPos = $yPos;
    }

    public function setWidth($width)
    {
        $this->width = $width;
    }

    public function setHeight($height)
    {
        $this->height = $height;
    }

    public function setBoxType($boxType)
    {
        $this->boxType = $boxType;
    }
    
    public function getTemplateId()
    {
        return $this->templateId;
    }

    public function setTemplateId($templateId)
    {
        $this->templateId = $templateId;
    }
    
    /**
     *
     * @param integer $boxId
     *
     * @return BoxType
     */
    public function getEditor()
    {
        $classRef = $this->getBoxType();
        
        if (class_exists($classRef)) {
            $boxEditor = new $classRef;

            $boxEditor->setBoxId($this->getBoxId());
            $boxEditor->setTemplateId($this->getTemplateId());

            self::$instance = $boxEditor;
        }
        
        return self::$instance;
    }
    
    /**
     * @return array
     */
    public function getEditors()
    {
        return PDFDesigner::getInstance()->getBoxEditors();
    }
    
    public function renderBox(&$pdf, $placehodlers, $x, $y, $w, $h)
    {
        $editor = $this->getEditor();
        
        $editor->setPlaceholders($placehodlers);
        $editor->renderPDF($pdf, $x, $y, $w, $h);
    }
    
    public function getTemplateEntity()
    {
        return Database::connection()->getEntityManager()->getRepository('Concrete\Package\PdfDesigner\Src\Entity\Template')
            ->findOneBy(array('templateId' => $this->getTemplateId()));
    }
    
    public function getXPosInch()
    {
        return $this->xPosInch;
    }

    public function getYPosInch()
    {
        return $this->yPosInch;
    }

    public function getWidthInch()
    {
        return $this->widthInch;
    }

    public function getHeightInch()
    {
        return $this->heightInch;
    }

    public function setXPosInch($xPosInch)
    {
        $this->xPosInch = $xPosInch;
    }

    public function setYPosInch($yPosInch)
    {
        $this->yPosInch = $yPosInch;
    }

    public function setWidthInch($widthInch)
    {
        $this->widthInch = $widthInch;
    }

    public function setHeightInch($heightInch)
    {
        $this->heightInch = $heightInch;
    }

    /**
     * @return array
     */
    public function getBoxAttributeEntities()
    {
        return Database::connection()->getEntityManager()->getRepository('Concrete\Package\PdfDesigner\Src\Entity\BoxAttribute')
                        ->findBy(array('associatedBoxId' => $this->boxId));
    }

    public function getPositionType()
    {
        return $this->positionType;
    }

    public function setPositionType($positionType)
    {
        $this->positionType = $positionType;
    }

    public function getPositionTypes()
    {
        return array(
            0 => t("Absolute"),
            1 => t("Relative")
        );
    }
}
