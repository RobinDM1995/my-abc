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

/**
 * @Entity
 * @Table(name="PdfDesignerTemplate")
 * */
class Template
{

    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $templateId;

    /**
     * @Column(type="integer") * */
    protected $marginTop = 0;

    /**
     * @Column(type="integer") * */
    protected $marginBottom = 0;

    /**
     * @Column(type="integer") * */
    protected $marginLeft = 0;

    /**
     * @Column(type="integer") * */
    protected $marginRight = 0;

    /**
     * @Column(type="decimal", precision=7, scale=4) * */
    protected $marginTopInch = 0;

    /**
     * @Column(type="decimal", precision=7, scale=4) * */
    protected $marginBottomInch = 0;

    /**
     * @Column(type="decimal", precision=7, scale=4) * */
    protected $marginLeftInch = 0;

    /**
     * @Column(type="decimal", precision=7, scale=4) * */
    protected $marginRightInch = 0;

    /**
     * @Column(type="string") * */
    protected $templateTitle = '';

    /**
     * @Column(type="string") * */
    protected $paperType = '';

    /**
     * @Column(type="boolean") * */
    protected $showGrid = false;

    /**
     * @Column(type="integer") * */
    protected $gridSize = 0;

    /**
     * @Column(type="decimal", precision=7, scale=4) * */
    protected $gridSizeInch = 0;

    /**
     * @Column(type="integer") * */
    protected $letterPaperFirstPageFileId = 0;

    /**
     * @Column(type="integer") * */
    protected $letterPaperFollowingPageFileId = 0;

    /**
     * @Column(type="integer") * */
    protected $headerHeight = 0;

    /**
     * @Column(type="integer") * */
    protected $footerHeight = 0;

    /**
     * @Column(type="integer") * */
    protected $documentHeight = 0;

    /**
     * @Column(type="integer") * */
    protected $documentWidth = 0;

    /**
     * @Column(type="decimal", precision=7, scale=4) * */
    protected $documentHeightInch = 0;

    /**
     * @Column(type="decimal", precision=7, scale=4) * */
    protected $documentWidthInch = 0;

    /**
     * @Column(type="boolean") * */
    protected $portraitMode = 1;

    /**
     * @Column(type="text") * */
    protected $sampleData = '';

    /**
     * @Column(type="boolean") * */
    protected $useMm = true;

    /**
     * @Column(type="boolean") * */
    protected $useCmyk = false;

    public function __construct()
    {
        if ($this->sampleData == "") {
            $this->sampleData = json_encode(array(
                "receiver" => array(
                    "firstname" => "Mister",
                    "lastname" => "Smith",
                    "street" => "Name of the Street",
                    "zip" => "Zip Code",
                    "city" => "City"
                ),

                "myTable" => array(
                    // Define the Columns
                    "columns" => array(
                        array(
                            "align" => "L", // Left Align
                            "text" => "Column 1"
                        ),
                        array(
                            "align" => "C", // Left Align
                            "text" => "Column 2"
                        ),
                        array(
                            "align" => "R", // Right Align
                            "text" => "Column 3"
                        )
                    ),

                    "rows" => array(
                        array(
                            "Data 1",
                            "Data 2",
                            "Data 3"
                        ),
                        array(
                            "Data 1",
                            "Data 2",
                            "Data 3"
                        ),
                        array(
                            "Data 1",
                            "Data 2",
                            "Data 3"
                        )

                        // Add so many rows you want...
                    )
                )
            ));
        }
    }
    
    public function getPortraitMode()
    {
        return $this->portraitMode;
    }

    public function setPortraitMode($portraitMode)
    {
        $this->portraitMode = $portraitMode;
    }

    public function getTemplateId()
    {
        return $this->templateId;
    }

    public function getMarginTop()
    {
        return $this->marginTop;
    }

    public function getMarginBottom()
    {
        return $this->marginBottom;
    }

    public function getMarginLeft()
    {
        return $this->marginLeft;
    }

    public function getMarginRight()
    {
        return $this->marginRight;
    }
    
    public function getMarginTopInch()
    {
        return $this->marginTopInch;
    }

    public function getMarginBottomInch()
    {
        return $this->marginBottomInch;
    }

    public function getMarginLeftInch()
    {
        return $this->marginLeftInch;
    }

    public function getMarginRightInch()
    {
        return $this->marginRightInch;
    }

    public function getGridSizeInch()
    {
        return $this->gridSizeInch;
    }

    public function setMarginTopInch($marginTopInch)
    {
        $this->marginTopInch = $marginTopInch;
    }

    public function setMarginBottomInch($marginBottomInch)
    {
        $this->marginBottomInch = $marginBottomInch;
    }

    public function setMarginLeftInch($marginLeftInch)
    {
        $this->marginLeftInch = $marginLeftInch;
    }

    public function setMarginRightInch($marginRightInch)
    {
        $this->marginRightInch = $marginRightInch;
    }

    public function setGridSizeInch($gridSizeInch)
    {
        $this->gridSizeInch = $gridSizeInch;
    }

    public function getTemplateTitle()
    {
        return $this->templateTitle;
    }

    public function getShowGrid()
    {
        return $this->showGrid;
    }

    public function getGridSize()
    {
        return $this->gridSize;
    }

    public function getLetterPaperFirstPageFileId()
    {
        return $this->letterPaperFirstPageFileId;
    }

    public function getLetterPaperFollowingPageFileId()
    {
        return $this->letterPaperFollowingPageFileId;
    }

    public function getHeaderHeight()
    {
        return $this->headerHeight;
    }

    public function getFooterHeight()
    {
        return $this->footerHeight;
    }

    public function getDocumentHeight()
    {
        return $this->documentHeight;
    }

    public function getDocumentWidth()
    {
        return $this->documentWidth;
    }

    public function setTemplateId($templateId)
    {
        $this->templateId = $templateId;
    }

    public function setMarginTop($marginTop)
    {
        $this->marginTop = $marginTop;
    }

    public function setMarginBottom($marginBottom)
    {
        $this->marginBottom = $marginBottom;
    }

    public function setMarginLeft($marginLeft)
    {
        $this->marginLeft = $marginLeft;
    }

    public function setMarginRight($marginRight)
    {
        $this->marginRight = $marginRight;
    }

    public function setTemplateTitle($templateTitle)
    {
        $this->templateTitle = $templateTitle;
    }

    public function setShowGrid($showGrid)
    {
        $this->showGrid = $showGrid;
    }

    public function setGridSize($gridSize)
    {
        $this->gridSize = $gridSize;
    }

    public function setLetterPaperFirstPageFileId($letterPaperFirstPageFileId)
    {
        $this->letterPaperFirstPageFileId = $letterPaperFirstPageFileId;
    }

    public function setLetterPaperFollowingPageFileId($letterPaperFollowingPageFileId)
    {
        $this->letterPaperFollowingPageFileId = $letterPaperFollowingPageFileId;
    }

    public function setHeaderHeight($headerHeight)
    {
        $this->headerHeight = $headerHeight;
    }

    public function setFooterHeight($footerHeight)
    {
        $this->footerHeight = $footerHeight;
    }

    public function setDocumentHeight($documentHeight)
    {
        $this->documentHeight = $documentHeight;
    }

    public function setDocumentWidth($documentWidth)
    {
        $this->documentWidth = $documentWidth;
    }
    
    public function getDocumentHeightInch()
    {
        return $this->documentHeightInch;
    }

    public function getDocumentWidthInch()
    {
        return $this->documentWidthInch;
    }

    public function setDocumentHeightInch($documentHeightInch)
    {
        $this->documentHeightInch = $documentHeightInch;
    }

    public function setDocumentWidthInch($documentWidthInch)
    {
        $this->documentWidthInch = $documentWidthInch;
    }

    public function getPaperType()
    {
        if ($this->paperType === "") {
            return "A4";
        } else {
            return $this->paperType;
        }
    }

    public function setPaperType($paperType)
    {
        $this->paperType = $paperType;
        
        $this->updatePaperType();
    }
    
    public function updatePaperType()
    {
        $this->setDocumentWidth($this->getPaperTypes()[$this->getPaperType()]["width"]);
        $this->setDocumentHeight($this->getPaperTypes()[$this->getPaperType()]["height"]);
        $this->setDocumentWidthInch($this->getPaperTypes()[$this->getPaperType()]["widthInch"]);
        $this->setDocumentHeightInch($this->getPaperTypes()[$this->getPaperType()]["heightInch"]);
    }
    
    public function getPaperTypes()
    {
        return array(
            // international papertypes
            "A0" => array("label" => t("A0"), "width" => 841, "height" => 1189, "widthInch" => 33.1, "heightInch" => 46.8),
            "A1" => array("label" => t("A1"), "width" => 594, "height" => 841, "widthInch" => 23.4, "heightInch" => 33.1),
            "A2" => array("label" => t("A2"), "width" => 420, "height" => 594, "widthInch" => 16.5, "heightInch" => 23.4),
            "A3" => array("label" => t("A3"), "width" => 297, "height" => 420, "widthInch" => 11.7, "heightInch" => 16.5),
            "A4" => array("label" => t("A4"), "width" => 210, "height" => 297, "widthInch" => 8.3, "heightInch" => 11.7),
            "A5" => array("label" => t("A5"), "width" => 148, "height" => 210, "widthInch" => 5.8, "heightInch" => 8.3),
            "A6" => array("label" => t("A6"), "width" => 105, "height" => 148, "widthInch" => 4.1, "heightInch" => 5.8),
            "A7" => array("label" => t("A7"), "width" => 74, "height" => 105, "widthInch" => 2.9, "heightInch" => 4.1),
            "A8" => array("label" => t("A8"), "width" => 52, "height" => 74, "widthInch" => 2.05, "heightInch" => 2.9),
            "A9" => array("label" => t("A9"), "width" => 37, "height" => 52, "widthInch" => 1.46, "heightInch" => 2.05),
            "A10" => array("label" => t("A10"), "width" => 26, "height" => 37, "widthInch" => 1.02, "heightInch" => 1.46),
            
            // american papertypes
            "Extra" => array("label" => t("Extra"), "width" => 304.8, "height" => 457.2, "widthInch" => 12, "heightInch" => 18),
            "Tabloid" => array("label" => t("Tabloid (Ledger)"), "width" => 279.4, "height" => 431.8, "widthInch" => 11, "heightInch" => 17),
            "Legal" => array("label" => t("Legal"), "width" => 215.9, "height" => 355.6, "widthInch" => 8.5, "heightInch" => 14),
            "Legal13" => array("label" => t("Legal 13 \""), "width" => 215.9, "height" => 330.2, "widthInch" => 8.5, "heightInch" => 13),
            "Letter" => array("label" => t("Letter (US Standard Letter)"), "width" => 215.9, "height" => 279.4, "widthInch" => 8.5, "heightInch" => 11),
            "Executive" => array("label" => t("Executive"), "width" => 184.1, "height" => 266.7, "widthInch" => 7.25, "heightInch" => 10.5),
            "Statement" => array("label" => t("Statement (half letter)"), "width" => 139.7, "height" => 215.9, "widthInch" => 5.5, "heightInch" => 8.5),
            "Commercial10" => array("label" => t("Commercial #10"), "width" => 104.8, "height" => 241.3, "widthInch" => 4.125, "heightInch" => 9.5),
            "Monarch" => array("label" => t("Monarch"), "width" => 98.4, "height" => 190.5, "widthInch" => 3.875, "heightInch" => 7.5),
            "5x7" => array("label" => t("5 x 7 Card"), "width" => 127, "height" => 177.8, "widthInch" => 5, "heightInch" => 7),
            "4x6" => array("label" => t("4 x 6 Card"), "width" => 101.6, "height" => 152.4, "widthInch" => 4, "heightInch" => 6),
        );
    }
    
    public function getPaperTypesSimple()
    {
        $paperTypes = array();
        
        foreach ($this->getPaperTypes() as $key => $paperType) {
            $paperTypes[$key] = $paperType["label"];
        }
        
        return $paperTypes;
    }
    
    public function getUseMm()
    {
        return $this->useMm;
    }

    public function setUseMm($useMm)
    {
        $this->useMm = $useMm;
    }
    
    public function getSampleData()
    {
        return $this->sampleData;
    }

    public function setSampleData($sampleData)
    {
        $this->sampleData = $sampleData;
    }

    public function getUseCmyk()
    {
        return $this->useCmyk;
    }

    public function setUseCmyk($useCmyk)
    {
        $this->useCmyk = $useCmyk;
    }
}
