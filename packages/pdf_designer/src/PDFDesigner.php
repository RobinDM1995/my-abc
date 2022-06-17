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

define("PDF_DESIGNER_EXECUTE", true);

use Concrete\Package\PdfDesigner\Src\Entity\Box;
use Concrete\Package\PdfDesigner\Src\Entity\Template;
use Concrete\Package\PdfDesigner\Src\Entity\CustomFont;
use Concrete\Package\PdfDesigner\Src\Entity\BoxAttribute;
use Concrete\Package\PdfDesigner\Src\PDFDocument;
use Concrete\Package\PdfDesigner\Src\MakeFont\MakeFont;
use Concrete\Package\PdfDesigner\Src\Helpers;
use Concrete\Core\File\Importer;
use Database;
use Package;
use File;
use Core;
use \ZipArchive;

class PDFDesigner
{
    const googleFontsApiKey = "AIzaSyC43vTRtZGnuZVH23fCzH1HJyRtBewJyo0";

    private static $instance = null;
    private $templateId;
    private $em;
    private $boxEditors = array();
    private $cachedGoogleFonts = null;
    /** @var \Concrete\Core\File\Service\File */
    private $fileHelper;

    /**
     * @return PDFDesigner
     */
    public static function getInstance($templateId = null)
    {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }

        self::$instance->loadTemplate($templateId);

        return self::$instance;
    }

    public static function getTemplateByName($templateName)
    {
        $templateEntity = Database::connection()->
                getEntityManager()->
                getRepository('Concrete\Package\PdfDesigner\Src\Entity\Template')->
                findOneBy(array('templateTitle' => $templateName));

        if (is_object($templateEntity)) {
            return self::getTemplateByID($templateEntity->getTemplateId());
        }
    }

    public static function getTemplateByID($templateId)
    {
        return self::getInstance($templateId);
    }

    /**
     *
     * @param string $editorId
     * @param string $editorName
     */
    public function registerBoxEditor($editorId, $editorName)
    {
        $this->boxEditors[$editorId] = $editorName;
    }

    public function getBoxEditors()
    {
        return $this->boxEditors;
    }

    public function __construct()
    {
        $this->em = Database::connection()->getEntityManager();
        $this->fileHelper = Core::make("helper/file");
    }

    private function loadTemplate($templateId)
    {
        $this->templateId = $templateId;
    }

    /**
     *
     * @return Template
     */
    public function getTemplateEntity()
    {
        return $this->em->getRepository('Concrete\Package\PdfDesigner\Src\Entity\Template')
                        ->findOneBy(array('templateId' => $this->templateId));
    }

    /**
     *
     * @return Box
     */
    public function getBoxEntity($boxId)
    {
        return $this->em->getRepository('Concrete\Package\PdfDesigner\Src\Entity\Box')
                        ->findOneBy(array('boxId' => $boxId));
    }

    /**
     * @return array
     */
    public function getBoxEntities()
    {
        return $this->em->getRepository('Concrete\Package\PdfDesigner\Src\Entity\Box')
                        ->findBy(array('templateId' => $this->templateId), array('yPos' => 'ASC'));
    }

    /**
     * @return array
     */
    private function getCustomFontEntities()
    {
        return $this->em->getRepository('Concrete\Package\PdfDesigner\Src\Entity\CustomFont')
                        ->findBy(array('templateId' => $this->templateId));
    }

    /**
     *
     * @param integer $x
     * @param integer $y
     * @param integer $width
     * @param integer $height
     *
     * @return integer
     */
    public function addBox($x, $y, $width, $height)
    {
        $boxEntity = new Box;

        $boxEntity->setXPos($x);
        $boxEntity->setYPos($y);
        $boxEntity->setWidth($width);
        $boxEntity->setHeight($height);
        $boxEntity->setXPosInch($x * 0.03937007874015748);
        $boxEntity->setYPosInch($y * 0.03937007874015748);
        $boxEntity->setWidthInch($width * 0.03937007874015748);
        $boxEntity->setHeightInch($height * 0.03937007874015748);
        $boxEntity->setTemplateId($this->templateId);
        $boxEntity->setBoxType("");

        $this->em->persist($boxEntity);
        $this->em->flush();

        return $boxEntity->getBoxId();
    }

    /**
     *
     * @param integer $boxId
     * @param integer $x
     * @param integer $y
     *
     * @return boolean
     */
    public function moveBox($boxId, $x, $y)
    {
        $this->em->createQueryBuilder()
                ->update('Concrete\Package\PdfDesigner\Src\Entity\Box', 'b')
                ->set('b.xPos', ':xPos')
                ->set('b.yPos', ':yPos')
                ->set('b.xPosInch', ':xPosInch')
                ->set('b.yPosInch', ':yPosInch')
                ->where("b.boxId = :boxId")
                ->setParameter(':boxId', $boxId)
                ->setParameter(':xPos', $x)
                ->setParameter(':yPos', $y)
                ->setParameter(':xPosInch', $x * 0.03937007874015748)
                ->setParameter(':yPosInch', $y * 0.03937007874015748)
                ->getQuery()
                ->execute();

        $this->em->flush();

        return true;
    }

    /**
     *
     * @param integer $boxId
     * @param integer $width
     * @param integer $height
     *
     * @return boolean
     */
    public function resizeBox($boxId, $width, $height)
    {
        $this->em->createQueryBuilder()
                ->update('Concrete\Package\PdfDesigner\Src\Entity\Box', 'b')
                ->set('b.width', ':width')
                ->set('b.height', ':height')
                ->set('b.widthInch', ':widthInch')
                ->set('b.heightInch', ':heightInch')
                ->where("b.boxId = :boxId")
                ->setParameter(':boxId', $boxId)
                ->setParameter(':width', $width)
                ->setParameter(':height', $height)
                ->setParameter(':widthInch', $width * 0.03937007874015748)
                ->setParameter(':heightInch', $height * 0.03937007874015748)
                ->getQuery()
                ->execute();

        $this->em->flush();

        return true;
    }

    /**
     *
     * @param integer $boxId
     *
     * @return boolean
     */
    public function removeBox($boxId)
    {
        $this->em->createQueryBuilder()
                ->delete('Concrete\Package\PdfDesigner\Src\Entity\Box', 'b')
                ->where("b.boxId = :boxId")
                ->setParameter(':boxId', $boxId)
                ->getQuery()
                ->execute();

        $this->em->flush();

        $this->em->createQueryBuilder()
                ->delete('Concrete\Package\PdfDesigner\Src\Entity\BoxAttribute', 'a')
                ->where("a.associatedBoxId = :boxId")
                ->setParameter(':boxId', $boxId)
                ->getQuery()
                ->execute();

        $this->em->flush();


        return true;
    }

    public function createEmptyTemplate()
    {
        $emptyTemplate = new Template();

        $emptyTemplate->setPaperType("A4");

        $this->em->persist($emptyTemplate);

        $this->em->flush();

        $emptyTemplate->setTemplateTitle(t("New Document %s", $emptyTemplate->getTemplateId()));

        $this->em->persist($emptyTemplate);

        $this->em->flush();

        return $emptyTemplate->getTemplateId();
    }

    public function removeTemplate($templateId = null)
    {
        if ($templateId === null) {
            $templateId = $this->templateId;
        }

        $this->em->createQueryBuilder()
                ->delete('Concrete\Package\PdfDesigner\Src\Entity\Template', 't')
                ->where("t.templateId = :templateId")
                ->setParameter(':templateId', $templateId)
                ->getQuery()
                ->execute();

        $this->em->flush();

        // fetch boxes
        $rows = $this->em->createQueryBuilder()
                ->select('b')
                ->from('Concrete\Package\PdfDesigner\Src\Entity\Box', 'b')
                ->where('b.templateId = :templateId')
                ->setParameter(':templateId', $templateId)
                ->getQuery()
                ->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $this->removeBox($row["boxId"]);
            }
        }

        return true;
    }

    public function removeAllTemplates()
    {
        $rows = $this->em->createQueryBuilder()
                ->select('t')
                ->from('Concrete\Package\PdfDesigner\Src\Entity\Template', 't')
                ->getQuery()
                ->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $this->removeTemplate($row["templateId"]);
            }
        }
    }

    /**
     * @return array
     */
    public function getDocument()
    {
        // fetch document settings
        $document = $this->em->createQueryBuilder()
                ->select('t')
                ->from('Concrete\Package\PdfDesigner\Src\Entity\Template', 't')
                ->where('t.templateId = :templateId')
                ->setParameter(':templateId', $this->templateId)
                ->getQuery()
                ->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        if (count($document) === 1) {
            $document = $document[0];
        } else {
            $document = array();
        }

        // fetch boxes
        $document["boxes"] = $this->em->createQueryBuilder()
                ->select('b')
                ->from('Concrete\Package\PdfDesigner\Src\Entity\Box', 'b')
                ->where('b.templateId = :templateId')
                ->setParameter(':templateId', $this->templateId)
                ->getQuery()
                ->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        return $document;
    }

    private function isValidSetting($key)
    {
        return in_array($key, array(
            "marginTop",
            "marginBottom",
            "marginLeft",
            "marginRight",
            "marginTopInch",
            "marginBottomInch",
            "marginLeftInch",
            "marginRightInch",
            "templateTitle",
            "showGrid",
            "gridSize",
            "gridSizeInch",
            "letterPaperFirstPageFileId",
            "letterPaperFollowingPageFileId",
            "paperType",
            "portraitMode",
            "useMm",
            "useCmyk",
            "sampleData"
        ));
    }

    private function saveSetting($key, $value)
    {
        if (!$this->isValidSetting($key)) {
            return;
        }

        $this->em->createQueryBuilder()
                ->update('Concrete\Package\PdfDesigner\Src\Entity\Template', 't')
                ->set(sprintf("t.%s", $key), ':value')
                ->where("t.templateId = :templateId")
                ->setParameter(':value', $value)
                ->setParameter(':templateId', $this->templateId)
                ->getQuery()
                ->execute();

        $this->em->flush();

        if ($key === "paperType") {
            $this->getTemplateEntity()->updatePaperType();

            $this->em->persist($this->getTemplateEntity());

            $this->em->flush();
        }
    }

    /**
     *
     * @param array $arrSettings
     *
     * @return boolean
     */
    public function saveDocument($arrSettings)
    {
        if (isset($arrSettings["fonts"])) {
            $this->updateCustomFonts($arrSettings["fonts"]);

            unset($arrSettings["fonts"]);
        }

        foreach ($arrSettings as $key => $value) {
            $this->saveSetting($key, $value);
        }

        return $this->getDocument();
    }

    /**
     *
     * @return array
     */
    public function getTemplates()
    {
        $templates = array();

        $rows = $this->em->createQueryBuilder()
                ->select('t')
                ->from('Concrete\Package\PdfDesigner\Src\Entity\Template', 't')
                ->getQuery()
                ->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $templates[$row["templateId"]] = $row["templateTitle"];
            }
        }

        return $templates;
    }

    /**
     * Creates a PDF from this template file and returns the pdf as string.
     *
     * @param array $placeholders
     *
     * @return string
     */
    public function createPDF($placeholders = array())
    {
        $garbageCollector = array();

        $useMm = intval($this->getTemplateEntity()->getUseMm()) === 1;
        
        // Currently all Documents will generated in mm even if inch is selected.
        $useMm = true;
        
        $document = new PDFDocument('P', $useMm ? 'mm' : 'in');

        $document->setTemplate($this->getTemplateEntity());

        if ($useMm) {
            $document->SetTopMargin($document->getTemplate()->getMarginTop());
            $document->SetLeftMargin($document->getTemplate()->getMarginLeft());
            $document->SetRightMargin($document->getTemplate()->getMarginLeft());
            $document->SetAutoPageBreak(true, $document->getTemplate()->getMarginBottom());
        } else {
            $document->SetTopMargin($document->getTemplate()->getMarginTopInch());
            $document->SetLeftMargin($document->getTemplate()->getMarginLeftInch());
            $document->SetRightMargin($document->getTemplate()->getMarginLeftInch());
            $document->SetAutoPageBreak(true, $document->getTemplate()->getMarginBottomInch());
        }
        
        foreach ($this->getCustomFonts(false) as $font) {
            $fontName = $font["fontName"];

            $regularDefFile = "";

            // Add regular font
            if (intval($font["regularFontFileId"]) > 0) {
                $f = File::getById($font["regularFontFileId"]);

                if (is_object($f)) {
                    $ttfFile = Helpers::getAbsolutePath($f);

                    $defFile = MakeFont::MakeFont($ttfFile, $document->getFontpath(), $zFile);

                    $regularDefFile = $defFile;

                    array_push($garbageCollector, $defFile);
                    array_push($garbageCollector, $zFile);

                    $document->AddFont($fontName, "", basename($defFile));
                }
            }

            // Add italic font
            if (intval($font["italicFontFileId"]) > 0) {
                $f = File::getById($font["italicFontFileId"]);

                if (is_object($f)) {
                    $ttfFile = Helpers::getAbsolutePath($f);

                    $defFile = MakeFont::MakeFont($ttfFile, $document->getFontpath(), $zFile);

                    array_push($garbageCollector, $defFile);
                    array_push($garbageCollector, $zFile);

                    $document->AddFont($fontName, "I", basename($defFile));
                } else {
                    $document->AddFont($fontName, "I", basename($regularDefFile));
                }
            } else {
                $document->AddFont($fontName, "I", basename($regularDefFile));
            }

            // Add bold font
            if (intval($font["boldFontFileId"]) > 0) {
                $f = File::getById($font["boldFontFileId"]);

                if (is_object($f)) {
                    $ttfFile = Helpers::getAbsolutePath($f);

                    $defFile = MakeFont::MakeFont($ttfFile, $document->getFontpath(), $zFile);

                    array_push($garbageCollector, $defFile);
                    array_push($garbageCollector, $zFile);

                    $document->AddFont($fontName, "B", basename($defFile));
                } else {
                    $document->AddFont($fontName, "B", basename($regularDefFile));
                }
            } else {
                $document->AddFont($fontName, "B", basename($regularDefFile));
            }

            // Add bold italic font
            if (intval($font["boldItalicFontFileId"]) > 0) {
                $f = File::getById($font["boldItalicFontFileId"]);

                if (is_object($f)) {
                    $ttfFile = Helpers::getAbsolutePath($f);

                    $defFile = MakeFont::MakeFont($ttfFile, $document->getFontpath(), $zFile);

                    array_push($garbageCollector, $defFile);
                    array_push($garbageCollector, $zFile);

                    $document->AddFont($fontName, "BI", basename($defFile));
                } else {
                    $document->AddFont($fontName, "BI", basename($regularDefFile));
                }
            } else {
                $document->AddFont($fontName, "BI", basename($regularDefFile));
            }
        }

        if ($useMm) {
            if ($this->getTemplateEntity()->getPortraitMode()) {
                $document->AddPage("P", array($this->getTemplateEntity()->getDocumentWidth(), $this->getTemplateEntity()->getDocumentHeight()));
            } else {
                $document->AddPage("L", array($this->getTemplateEntity()->getDocumentHeight(), $this->getTemplateEntity()->getDocumentWidth()));
            }
        } else {
            if ($this->getTemplateEntity()->getPortraitMode()) {
                $document->AddPage("P", array($this->getTemplateEntity()->getDocumentWidthInch(), $this->getTemplateEntity()->getDocumentHeightInch()));
            } else {
                $document->AddPage("L", array($this->getTemplateEntity()->getDocumentHeightInch(), $this->getTemplateEntity()->getDocumentWidthInch()));
            }
        }
        
        foreach ($this->getBoxEntities() as $boxEntity) {
            if ($useMm) {
                $x = $boxEntity->getXPos();
                $y = $boxEntity->getYPos();
                $w = $boxEntity->getWidth();
                $h = $boxEntity->getHeight();
            } else {
                $x = $boxEntity->getXPosInch();
                $y = $boxEntity->getYPosInch();
                $w = $boxEntity->getWidthInch();
                $h = $boxEntity->getHeightInch();
            }
            
            if (intval($boxEntity->getPositionType()) === 1) {
                // Relative Position
                if ($useMm) {
                    $distanceToPreviousBox = $y - ($previousBox->getYPos() + $previousBox->getHeight());
                    
                    $y = $document->getY() + $distanceToPreviousBox;
                } else {
                    $distanceToPreviousBox = $y - ($previousBox->getYPosInch() + $previousBox->getHeightInch());
                    
                    $y = $document->getY() + $distanceToPreviousBox;
                }
            }
            
            $boxEntity->renderBox($document, $placeholders, $x, $y, $w, $h);
            
            $previousBox = $boxEntity;
        }

        // Save PDF data in string
        $pdfData = $document->Output("S", "", true);

        // Remove generated files
        if (count($garbageCollector) > 0) {
            foreach ($garbageCollector as $tempFile) {
                @unlink($tempFile);
            }
        }

        return $pdfData;
    }

    /**
     * Creates a PDF from this template file and outputs the file content to the browser.
     *
     * @param array $placeholders
     *
     * @return string
     */
    public function outputPDF($placeholders = array())
    {
        $pdfContent = $this->createPDF($placeholders);

        header('Content-Type: application/pdf');

        print $pdfContent;

        exit();
    }

    /**
     * @param integer $fontId
     * @param array $arrFont
     */
    private function updateCustomFont($fontId, $arrFont)
    {
        $this->em->createQueryBuilder()
                ->update('Concrete\Package\PdfDesigner\Src\Entity\CustomFont', 'f')
                ->set('f.fontName', ':fontName')
                ->set('f.regularFontFileId', ':regularFontFileId')
                ->set('f.boldFontFileId', ':boldFontFileId')
                ->set('f.italicFontFileId', ':italicFontFileId')
                ->set('f.boldItalicFontFileId', ':boldItalicFontFileId')
                ->where("f.id = :id")
                ->setParameter(':id', $fontId)
                ->setParameter(':fontName', $arrFont["fontName"])
                ->setParameter(':regularFontFileId', $arrFont["regularFontFileId"])
                ->setParameter(':boldFontFileId', $arrFont["boldFontFileId"])
                ->setParameter(':italicFontFileId', $arrFont["italicFontFileId"])
                ->setParameter(':boldItalicFontFileId', $arrFont["boldItalicFontFileId"])
                ->getQuery()
                ->execute();

        $this->em->flush();
    }

    /**
     *
     * @param array $arrFonts
     */
    private function updateCustomFonts($arrFonts)
    {
        foreach ($arrFonts as $fontId => $arrFont) {
            $this->updateCustomFont($fontId, $arrFont);
        }
    }

    /**
     *
     * @return array
     */
    public function getCustomFonts($simple = true)
    {
        $customFonts = array();

        $rows = $this->em->createQueryBuilder()
                ->select('f')
                ->from('Concrete\Package\PdfDesigner\Src\Entity\CustomFont', 'f')
                ->where("f.templateId = :templateId")
                ->setParameter(':templateId', $this->templateId)
                ->getQuery()
                ->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        if (count($rows) > 0) {
            foreach ($rows as $row) {
                if ($simple) {
                    array_push($customFonts, $row["fontName"]);
                } else {
                    array_push($customFonts, $row);
                }
            }
        }

        return $customFonts;
    }

    /**
     *
     * @return boolean
     */
    public function addFont()
    {
        $font = new CustomFont();

        $font->setTemplateId($this->templateId);

        $this->em->persist($font);

        $this->em->flush();

        $rows = $this->em->createQueryBuilder()
                ->select('f')
                ->from('Concrete\Package\PdfDesigner\Src\Entity\CustomFont', 'f')
                ->where("f.id = :id")
                ->setParameter(":id", $font->getId())
                ->getQuery()
                ->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        return $rows[0];
    }

    /**
     *
     * @param integer $id
     *
     * @return boolean
     */
    public function removeFont($id)
    {
        $this->em->createQueryBuilder()
                ->delete('Concrete\Package\PdfDesigner\Src\Entity\CustomFont', 'f')
                ->where("f.id = :id")
                ->setParameter(':id', $id)
                ->getQuery()
                ->execute();

        $this->em->flush();

        return true;
    }

    /**
     *
     * @param boolean $output
     *
     * @return string
     */
    public function exportTemplate($output = true)
    {
        $filesToExport = array();

        $xmlFile = new \SimpleXMLElement('<PdfDesigner></PdfDesigner>');

        $xmlFile->addChild("VersionNumber", Package::getByHandle("pdf_designer")->getPackageVersion());

        $xmlTemplate = $xmlFile->addChild("Template");

        $xmlTemplate->addChild("DocumentHeight", $this->getTemplateEntity()->getDocumentHeight());
        $xmlTemplate->addChild("DocumentWidth", $this->getTemplateEntity()->getDocumentWidth());
        $xmlTemplate->addChild("DocumentHeightInch", $this->getTemplateEntity()->getDocumentHeightInch());
        $xmlTemplate->addChild("DocumentWidthInch", $this->getTemplateEntity()->getDocumentWidthInch());
        $xmlTemplate->addChild("GridSize", $this->getTemplateEntity()->getGridSize());
        $xmlTemplate->addChild("GridSizeInch", $this->getTemplateEntity()->getGridSizeInch());

        $letterPaperFirstPage = Helpers::getAbsolutePathByFileId($this->getTemplateEntity()->getLetterPaperFirstPageFileId());

        array_push($filesToExport, $letterPaperFirstPage);

        $xmlTemplate->addChild("LetterPaperFirstPage", basename($letterPaperFirstPage));

        $letterPaperFollowingPage = Helpers::getAbsolutePathByFileId($this->getTemplateEntity()->getLetterPaperFollowingPageFileId());

        array_push($filesToExport, $letterPaperFollowingPage);

        $xmlTemplate->addChild("LetterPaperFollowingPage", basename($letterPaperFollowingPage));

        $xmlTemplate->addChild("MarginTop", $this->getTemplateEntity()->getMarginTop());
        $xmlTemplate->addChild("MarginLeft", $this->getTemplateEntity()->getMarginLeft());
        $xmlTemplate->addChild("MarginRight", $this->getTemplateEntity()->getMarginRight());
        $xmlTemplate->addChild("MarginBottom", $this->getTemplateEntity()->getMarginBottom());
        $xmlTemplate->addChild("MarginTopInch", $this->getTemplateEntity()->getMarginTopInch());
        $xmlTemplate->addChild("MarginLeftInch", $this->getTemplateEntity()->getMarginLeftInch());
        $xmlTemplate->addChild("MarginRightInch", $this->getTemplateEntity()->getMarginRightInch());
        $xmlTemplate->addChild("MarginBottomInch", $this->getTemplateEntity()->getMarginBottomInch());
        $xmlTemplate->addChild("PaperType", $this->getTemplateEntity()->getPaperType());
        $xmlTemplate->addChild("PortraitMode", $this->getTemplateEntity()->getPortraitMode());
        $xmlTemplate->addChild("SampleData", $this->getTemplateEntity()->getSampleData());
        $xmlTemplate->addChild("ShowGrid", $this->getTemplateEntity()->getShowGrid());
        $xmlTemplate->addChild("TemplateTitle", $this->getTemplateEntity()->getTemplateTitle());
        $xmlTemplate->addChild("UseMm", $this->getTemplateEntity()->getUseMm());
        $xmlTemplate->addChild("UseCmyk", $this->getTemplateEntity()->getUseCmyk());

        $xmlBoxes = $xmlTemplate->addChild("Boxes");

        foreach ($this->getBoxEntities() as $boxEntity) {
            $xmlBox = $xmlBoxes->addChild("Box");

            $xmlBox->addChild("BoxType", $boxEntity->getBoxType());
            $xmlBox->addChild("PositionType", $boxEntity->getPositionType());
            $xmlBox->addChild("Height", $boxEntity->getHeight());
            $xmlBox->addChild("HeightInch", $boxEntity->getHeightInch());
            $xmlBox->addChild("Width", $boxEntity->getWidth());
            $xmlBox->addChild("WidthInch", $boxEntity->getWidthInch());
            $xmlBox->addChild("XPos", $boxEntity->getXPos());
            $xmlBox->addChild("XPosInch", $boxEntity->getXPosInch());
            $xmlBox->addChild("YPos", $boxEntity->getYPos());
            $xmlBox->addChild("YPosInch", $boxEntity->getYPosInch());

            $xmlBoxAttributes = $xmlBox->addChild("Attributes");

            foreach ($boxEntity->getBoxAttributeEntities() as $boxAttributeEntity) {
                $xmlBoxAttribute = $xmlBoxAttributes->addChild("Attribute");

                $xmlBoxAttribute->addChild("Name", $boxAttributeEntity->getAttributeName());

                if ($boxAttributeEntity->getAttributeName() === "Image") {
                    $fileId = Helpers::getAbsolutePathByFileId($boxAttributeEntity->getAttributeValue());

                    array_push($filesToExport, $fileId);

                    $xmlBoxAttribute->addChild("Value", basename($fileId));
                } else {
                    $xmlBoxAttribute->addChild("Value", $boxAttributeEntity->getAttributeValue());
                }
            }
        }

        $xmlCustomFonts = $xmlTemplate->addChild("CustomFonts");

        foreach ($this->getCustomFontEntities() as $customFontEntity) {
            $xmlCustomFont = $xmlCustomFonts->addChild("CustomFont");

            $xmlCustomFont->addChild("FontName", $customFontEntity->getFontName());

            $boldFont = Helpers::getAbsolutePathByFileId($customFontEntity->getBoldFontFileId());
            $boldItalicFont = Helpers::getAbsolutePathByFileId($customFontEntity->getBoldItalicFontFileId());
            $italicFont = Helpers::getAbsolutePathByFileId($customFontEntity->getItalicFontFileId());
            $regularFont = Helpers::getAbsolutePathByFileId($customFontEntity->getRegularFontFileId());

            array_push($filesToExport, $boldFont);
            array_push($filesToExport, $boldItalicFont);
            array_push($filesToExport, $italicFont);
            array_push($filesToExport, $regularFont);

            $xmlCustomFont->addChild("BoldFont", basename($boldFont));
            $xmlCustomFont->addChild("BoldItalicFont", basename($boldItalicFont));
            $xmlCustomFont->addChild("ItalicFont", basename($italicFont));
            $xmlCustomFont->addChild("RegularFont", basename($regularFont));
        }

        $xmlFileContent = Helpers::convertSimpleXmlObjectToText($xmlFile);

        // Generate ZIP
        $zip = new ZipArchive();

        $zipFilename = Helpers::generateTempFile();

        if ($zip->open($zipFilename, ZipArchive::CREATE) === true) {
            $zip->addFromString("Template.xml", $xmlFileContent);

            foreach ($filesToExport as $fileToExport) {
                $zip->addFile($fileToExport, "Files/" . basename($fileToExport));
            }

            $zip->close();
        }

        $zipFileData = $this->fileHelper->getContents($zipFilename);

        @unlink($zipFilename);

        if ($output) {
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $this->getTemplateEntity()->getTemplateTitle() . '.pdt"');
            header('Content-Length: ' . strlen($zipFileData));

            print $zipFileData;
        } else {
            return $zipFileData;
        }
    }

    /**
     *
     * @param string $zipFileName
     * @param boolean $returnTemplateId
     *
     * @return boolean
     */
    public function importTemplate($zipFileName, $returnTemplateId = false)
    {
        $zip = new ZipArchive;

        $importer = new Importer();

        $fileMap = array();

        if ($zip->open($zipFileName) === true) {
            $tempFolder = Helpers::getTempFolder();

            $zip->extractTo($tempFolder);

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $fileName = $zip->getNameIndex($i);

                $tempFile = $tempFolder . "/" . $fileName;

                if ($fileName === "Template.xml") {
                    $zipFileData = $this->fileHelper->getContents($tempFile);
                } else {
                    $fileObject = $importer->import($tempFile, basename($fileName));

                    if (is_object($fileObject)) {
                        $fileMap[basename($fileName)] = $fileObject->getFileID();
                    } else {
                        return false;
                    }
                }

                //@unlink($zipFileName);
            }

            $zip->close();

            // parse xml
            $xmlTemplate = simplexml_load_string($zipFileData);

            $template = new Template;

            $template->setDocumentHeight(floatval($xmlTemplate->Template->DocumentHeight));
            $template->setDocumentWidth(floatval($xmlTemplate->Template->DocumentWidth));
            $template->setGridSize(floatval($xmlTemplate->Template->GridSize));
            $template->setDocumentHeightInch(floatval($xmlTemplate->Template->DocumentHeightInch));
            $template->setDocumentWidthInch(floatval($xmlTemplate->Template->DocumentWidthInch));
            $template->setGridSizeInch(floatval($xmlTemplate->Template->GridSizeInch));

            if (isset($fileMap[(string) $xmlTemplate->Template->LetterPaperFirstPage])) {
                $template->setLetterPaperFirstPageFileId($fileMap[(string) $xmlTemplate->Template->LetterPaperFirstPage]);
            }

            if (isset($fileMap[(string) $xmlTemplate->Template->LetterPaperFollowingPage])) {
                $template->setLetterPaperFollowingPageFileId($fileMap[(string) $xmlTemplate->Template->LetterPaperFollowingPage]);
            }

            $template->setMarginTop(floatval($xmlTemplate->Template->MarginTop));
            $template->setMarginLeft(floatval($xmlTemplate->Template->MarginLeft));
            $template->setMarginRight(floatval($xmlTemplate->Template->MarginRight));
            $template->setMarginBottom(floatval($xmlTemplate->Template->MarginBottom));
            $template->setMarginTopInch(floatval($xmlTemplate->Template->MarginTopInch));
            $template->setMarginLeftInch(floatval($xmlTemplate->Template->MarginLeftInch));
            $template->setMarginRightInch(floatval($xmlTemplate->Template->MarginRightInch));
            $template->setMarginBottomInch(floatval($xmlTemplate->Template->MarginBottomInch));
            $template->setPaperType((string) $xmlTemplate->Template->PaperType);
            $template->setPortraitMode(intval($xmlTemplate->Template->PortraitMode) === 1);
            $template->setSampleData((string) $xmlTemplate->Template->SampleData);
            $template->setShowGrid(intval($xmlTemplate->Template->ShowGrid) === 1);
            $template->setTemplateTitle((string) $xmlTemplate->Template->TemplateTitle);
            $template->setUseMm(intval($xmlTemplate->Template->UseMm) === 1);
            $template->setUseCmyk(intval($xmlTemplate->Template->UseCmyk) === 1);
            $template->updatePaperType();

            $this->em->persist($template);

            $this->em->flush();

            $templateId = $template->getTemplateId();

            if (is_object($xmlTemplate->Template->Boxes->Box)) {
                foreach ($xmlTemplate->Template->Boxes->Box as $xmlBox) {
                    $boxEntity = new Box();

                    $boxEntity->setBoxType((string) $xmlBox->BoxType);
                    $boxEntity->setPositionType(floatval($xmlBox->PositionType));
                    $boxEntity->setHeight(floatval($xmlBox->Height));
                    $boxEntity->setHeightInch(floatval($xmlBox->HeightInch));
                    $boxEntity->setWidth(floatval($xmlBox->Width));
                    $boxEntity->setWidthInch(floatval($xmlBox->WidthInch));
                    $boxEntity->setXPos(floatval($xmlBox->XPos));
                    $boxEntity->setXPosInch(floatval($xmlBox->XPosInch));
                    $boxEntity->setYPos(floatval($xmlBox->YPos));
                    $boxEntity->setYPosInch(floatval($xmlBox->YPosInch));
                    $boxEntity->setTemplateId($templateId);

                    $this->em->persist($boxEntity);

                    $this->em->flush();

                    $boxId = $boxEntity->getBoxId();

                    if (is_object($xmlBox->Attributes->Attribute)) {
                        foreach ($xmlBox->Attributes->Attribute as $xmlAttribute) {
                            $boxAttributeEntity = new BoxAttribute;

                            $boxAttributeEntity->setAssociatedBoxId($boxId);

                            $boxAttributeEntity->setAttributeName((string) $xmlAttribute->Name);
                            $boxAttributeEntity->setAttributeValue((string) $xmlAttribute->Value);

                            if ($boxAttributeEntity->getAttributeName() === "Image") {
                                if (isset($fileMap[$boxAttributeEntity->getAttributeName()])) {
                                    $boxAttributeEntity->setAttributeName($fileMap[$boxAttributeEntity->getAttributeName()]);
                                }
                            }

                            $this->em->persist($boxAttributeEntity);

                            $this->em->flush();
                        }
                    }
                }
            }

            if (is_object($xmlTemplate->Template->CustomFonts->CustomFont)) {
                foreach ($xmlTemplate->Template->CustomFonts->CustomFont as $xmlCustomFont) {
                    $customFontEntity = new CustomFont();

                    $customFontEntity->setTemplateId($templateId);
                    $customFontEntity->setFontName((string) $xmlCustomFont->FontName);


                    if (isset($fileMap[(string) $xmlCustomFont->CustomFont->BoldFont])) {
                        $customFontEntity->setBoldFontFileId($fileMap[(string) $xmlCustomFont->BoldFont]);
                    }

                    if (isset($fileMap[(string) $xmlCustomFont->CustomFont->BoldItalicFont])) {
                        $customFontEntity->setBoldItalicFontFileId($fileMap[(string) $xmlCustomFont->BoldItalicFont]);
                    }

                    if (isset($fileMap[(string) $xmlCustomFont->CustomFont->ItalicFont])) {
                        $customFontEntity->setItalicFontFileId($fileMap[(string) $xmlCustomFont->ItalicFont]);
                    }

                    if (isset($fileMap[(string) $xmlCustomFont->CustomFont->RegularFont])) {
                        $customFontEntity->setRegularFontFileId($fileMap[(string) $xmlCustomFont->RegularFont]);
                    }

                    $this->em->persist($customFontEntity);

                    $this->em->flush();
                }
            }
        }

        if ($returnTemplateId) {
            return $templateId;
        } else {
            return true;
        }
    }

    public function installSampleData()
    {
        $this->importTemplate(DIR_PACKAGES . "/pdf_designer/files/SampleInvoice5008.pdt");
    }

    public function duplicateTemplate()
    {
        $fileData = $this->exportTemplate(false);

        $tempFile = Helpers::generateTempFile();

        $this->fileHelper->append($tempFile, $fileData);

        $templateId = $this->importTemplate($tempFile, true);

        @unlink($tempFile);

        $this->loadTemplate($templateId);

        $templateEntity = $this->getTemplateEntity();

        $templateEntity->setTemplateTitle(t("%s (Duplicate)", $templateEntity->getTemplateTitle()));

        $this->em->persist($templateEntity);

        $this->em->flush($templateEntity);

        return true;
    }

    private function getGoogleFonts()
    {
        if (is_null($this->cachedGoogleFonts)) {
            $url = sprintf("https://www.googleapis.com/webfonts/v1/webfonts?key=%s", self::googleFontsApiKey);

            $data = Helpers::fetchUrl($url);

            $this->cachedGoogleFonts = json_decode($data, true);
        }

        return $this->cachedGoogleFonts;
    }

    /**
     *
     * @param string $fontUrl
     * @return integer
     */
    private function importFont($fontUrl)
    {
        $importer = new Importer;

        $tempFile = Helpers::generateTempFile();

        $fileData = Helpers::fetchUrl($fontUrl);

        $this->fileHelper->append($tempFile, $fileData);

        $fileObject = $importer->import($tempFile, basename($fontUrl));

        @unlink($tempFile);

        return $fileObject->getFileID();
    }

    /**
     *
     * @param string $fontName
     *
     * @return $fontEntity
     */
    public function googleImportFont($fontName)
    {
        $fontEntity = new CustomFont;

        $fontEntity->setFontName($fontName);
        $fontEntity->setTemplateId($this->templateId);

        if ($this->getRegularFontFileByFontName($fontName) != "") {
            $fontEntity->setRegularFontFileId($this->importFont($this->getRegularFontFileByFontName($fontName)));
        }

        if ($this->getItalicFontFileByFontName($fontName) != "") {
            $fontEntity->setItalicFontFileId($this->importFont($this->getItalicFontFileByFontName($fontName)));
        }

        if ($this->getBoldFontFileByFontName($fontName) != "") {
            $fontEntity->setBoldFontFileId($this->importFont($this->getBoldFontFileByFontName($fontName)));
        }

        if ($this->getBoldItalicFontFileByFontName($fontName) != "") {
            $fontEntity->setBoldItalicFontFileId($this->importFont($this->getBoldItalicFontFileByFontName($fontName)));
        }

        $this->em->persist($fontEntity);

        $this->em->flush();

        $rows = $this->em->createQueryBuilder()
                ->select('f')
                ->from('Concrete\Package\PdfDesigner\Src\Entity\CustomFont', 'f')
                ->where("f.id = :id")
                ->setParameter(":id", $fontEntity->getId())
                ->getQuery()
                ->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        return $rows[0];
    }

    private function getRegularFontFileByFontName($fontName)
    {
        $fontFiles = $this->getFontFilesByFontName($fontName);

        if (isset($fontFiles["regular"])) {
            return $fontFiles["regular"];
        }

        if (isset($fontFiles["300"])) {
            return $fontFiles["300"];
        }

        return false;
    }

    private function getItalicFontFileByFontName($fontName)
    {
        $fontFiles = $this->getFontFilesByFontName($fontName);

        if (isset($fontFiles["italic"])) {
            return $fontFiles["italic"];
        }

        if (isset($fontFiles["300italic"])) {
            return $fontFiles["300italic"];
        }

        return false;
    }

    private function getBoldFontFileByFontName($fontName)
    {
        $fontFiles = $this->getFontFilesByFontName($fontName);

        if (isset($fontFiles["bold"])) {
            return $fontFiles["bold"];
        }

        if (isset($fontFiles["800"])) {
            return $fontFiles["800"];
        }


        if (isset($fontFiles["600"])) {
            return $fontFiles["600"];
        }

        return false;
    }

    private function getBoldItalicFontFileByFontName($fontName)
    {
        $fontFiles = $this->getFontFilesByFontName($fontName);

        if (isset($fontFiles["800italic"])) {
            return $fontFiles["800italic"];
        }

        if (isset($fontFiles["600italic"])) {
            return $fontFiles["600italic"];
        }

        return false;
    }

    private function getFontFilesByFontName($fontName)
    {
        $fonts = array();

        $json = $this->getGoogleFonts();

        if (isset($json["items"])) {
            foreach ($json["items"] as $item) {
                if (isset($item["family"]) && $item["family"] === $fontName) {
                    return $item["files"];
                }
            }
        }

        return $fonts;
    }

    /**
     *
     * @param string $searchTerm
     *
     * @return array
     */
    public function googleLookupFonts($searchTerm)
    {
        $fonts = array();

        $json = $this->getGoogleFonts();

        if (isset($json["items"])) {
            foreach ($json["items"] as $item) {
                if (isset($item["family"])) {
                    if (strpos(strtolower($item["family"]), strtolower($searchTerm)) !== false) {
                        array_push($fonts, $item["family"]);
                    }
                }
            }
        }

        return $fonts;
    }

    /**
     *
     * @param string $searchTerm
     *
     * @return array
     */
    public function googleGetAllFonts()
    {
        $fonts = array();

        $json = $this->getGoogleFonts();

        if (isset($json["items"])) {
            foreach ($json["items"] as $item) {
                if (isset($item["family"])) {
                    array_push($fonts, $item["family"]);
                }
            }
        }

        return $fonts;
    }

    public function getPositionTypes()
    {
        return array(
            0 => t("Absolute"),
            1 => t("Relative")
        );
    }
    
    public function getBoxImage($boxEditor)
    {
        if (class_exists($boxEditor)) {
            $boxEditor = new $boxEditor;

            $boxEditor->setTemplateId($this->templateId);

            return $boxEditor->getImage();
        }
        
        return "";
    }

    /**
     * Create an HTML fragment of attribute values, merging any CSS class names as necessary.
     *
     * @param string $defaultClass Default CSS class name
     * @param array $attributes A hash array of attributes (name => value), possibly including 'class'.
     *
     * @return string A fragment of attributes suitable to put inside of an HTML tag
     */
    protected function parseMiscFields($defaultClass, $attributes)
    {
        $attributes = (array) $attributes;
        if ($defaultClass) {
            $attributes['class'] = trim((isset($attributes['class']) ? $attributes['class'] : '') . ' ' . $defaultClass);
        }
        $attr = '';
        foreach ($attributes as $k => $v) {
            $attr .= " $k=\"$v\"";
        }

        return $attr;
    }

    /**
     * Render a Template-Selector Element
     *
     * @param string $name
     * @param integer $selectedTemplateId
     * @param array $miscFields
     * @param boolean $allowNothing
     * @param boolean $output
     * @return boolean|string
     */
    public function getTemplateSelector($name, $selectedTemplateId = null, $miscFields = array(), $allowNothing = false, $output = false)
    {
        $html = sprintf(
            "<select name=\"%s\" id=\"%s\"%s>",
            addslashes($name),
            addslashes($name),
            $this->parseMiscFields('form-control', $miscFields)
        );
        
        if ($allowNothing) {
            $html .= sprintf("<option value=\"\">%s</option>", t("(Please select)"));
        }
        
        foreach ($this->getTemplates() as $templateId => $templateName) {
            $html .= sprintf(
                "<option value=\"%s\"%s>%s</option>",
                $templateId,
                intval($templateId) === intval($selectedTemplateId) ? " selected=\"selected\"" : "",
                $templateName
            );
        }
        
        $html .= "</select>";
        
        if ($output) {
            print $html;
            
            return true;
        } else {
            return $html;
        }
    }
}
