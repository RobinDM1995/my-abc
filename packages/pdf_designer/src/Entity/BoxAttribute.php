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
 * @Table(name="PdfDesignerBoxAttribute")
 * */
class BoxAttribute
{

    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $boxAttributeId;

    /**
     * @Column(type="integer") * */
    protected $associatedBoxId;
    
    /**
     * @Column(type="string") * */
    protected $attributeName;

    /**
     * @Column(type="text") * */
    protected $attributeValue;
    
    public function getBoxAttributeId()
    {
        return $this->boxAttributeId;
    }

    public function getAssociatedBoxId()
    {
        return $this->associatedBoxId;
    }

    public function getAttributeName()
    {
        return $this->attributeName;
    }

    public function getAttributeValue()
    {
        return $this->attributeValue;
    }

    public function setBoxAttributeId($boxAttributeId)
    {
        $this->boxAttributeId = $boxAttributeId;
    }

    public function setAssociatedBoxId($associatedBoxId)
    {
        $this->associatedBoxId = $associatedBoxId;
    }

    public function setAttributeName($attributeName)
    {
        $this->attributeName = $attributeName;
    }

    public function setAttributeValue($attributeValue)
    {
        $this->attributeValue = $attributeValue;
    }
}
