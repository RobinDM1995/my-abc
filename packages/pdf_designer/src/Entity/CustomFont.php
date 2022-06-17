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
 * @Table(name="CustomFont")
 * */
class CustomFont
{

    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Column(type="string") * */
    protected $fontName = '';

    /**
     * @Column(type="integer") * */
    protected $templateId = 0;

    /**
     * @Column(type="integer") * */
    protected $regularFontFileId = 0;

    /**
     * @Column(type="integer") * */
    protected $boldFontFileId = 0;

    /**
     * @Column(type="integer") * */
    protected $italicFontFileId = 0;

    /**
     * @Column(type="integer") * */
    protected $boldItalicFontFileId = 0;

    public function getId()
    {
        return $this->id;
    }

    public function getFontName()
    {
        return $this->fontName;
    }

    public function getRegularFontFileId()
    {
        return $this->regularFontFileId;
    }

    public function getBoldFontFileId()
    {
        return $this->boldFontFileId;
    }

    public function getItalicFontFileId()
    {
        return $this->italicFontFileId;
    }

    public function getBoldItalicFontFileId()
    {
        return $this->boldItalicFontFileId;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setFontName($fontName)
    {
        $this->fontName = $fontName;
    }

    public function setRegularFontFileId($regularFontFileId)
    {
        $this->regularFontFileId = $regularFontFileId;
    }

    public function setBoldFontFileId($boldFontFileId)
    {
        $this->boldFontFileId = $boldFontFileId;
    }

    public function setItalicFontFileId($italicFontFileId)
    {
        $this->italicFontFileId = $italicFontFileId;
    }

    public function setBoldItalicFontFileId($boldItalicFontFileId)
    {
        $this->boldItalicFontFileId = $boldItalicFontFileId;
    }

    public function getTemplateId()
    {
        return $this->templateId;
    }

    public function setTemplateId($templateId)
    {
        $this->templateId = $templateId;
    }
}
