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

use Database;
use Request;
use Concrete\Package\PdfDesigner\Src\Entity\BoxAttribute;
use StringTemplate\Engine;

class BoxEditor
{
    private $boxId;
    private $templateId;
    private $page;
    private $templateEngine;
    private $placeholders;

    public function getImage()
    {
        return Helpers::generateURL("/packages/pdf_designer/images/box_icons/unknown.png");
    }
    
    public function __construct()
    {
        $this->em = Database::connection()->getEntityManager();
        $this->templateEngine = new Engine;
    }
    
    public function getBoxId()
    {
        return $this->boxId;
    }
    
    public function getBoxEntity()
    {
        return $this->em->getRepository('Concrete\Package\PdfDesigner\Src\Entity\Box')
            ->findOneBy(array('boxId' => $this->boxId));
    }
    
    public function getTemplateEntity()
    {
        return $this->em->getRepository('Concrete\Package\PdfDesigner\Src\Entity\Template')
            ->findOneBy(array('templateId' => $this->getTemplateId()));
    }

    public function getTemplateId()
    {
        return $this->templateId;
    }

    public function setBoxId($boxId)
    {
        $this->boxId = $boxId;
    }

    public function setTemplateId($templateId)
    {
        $this->templateId = $templateId;
    }
    
    public function getPage()
    {
        return $this->page;
    }

    public function setPage($page)
    {
        $this->page = $page;
    }

    /**
     *
     * @return array
     */
    public function getAttributes()
    {
        $attributes = array();
        
        $rows = $this->em->createQueryBuilder()
            ->select('ba')
            ->from('Concrete\Package\PdfDesigner\Src\Entity\BoxAttribute', 'ba')
            ->where('ba.associatedBoxId = :boxId')
            ->setParameter(':boxId', $this->getBoxId())
            ->getQuery()
            ->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
        
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $attributes[$row["attributeName"]] = $row["attributeValue"];
            }
        }
        
        return $attributes;
    }
    
    /**
     *
     * @param string $attributeName
     * @param string $attributeValue
     */
    public function setAttribte($attributeName, $attributeValue)
    {
        $this->em->createQueryBuilder()
            ->delete('Concrete\Package\PdfDesigner\Src\Entity\BoxAttribute', 'ba')
            ->where("ba.associatedBoxId = :boxId")
            ->andWhere("ba.attributeName = :attributeName")
            ->setParameter(':boxId', $this->getBoxId())
            ->setParameter(':attributeName', $attributeName)
            ->getQuery()
            ->execute();
        
        $this->em->flush();
        
        $boxAttribute = new BoxAttribute;
        
        $boxAttribute->setAttributeName($attributeName);
        $boxAttribute->setAttributeValue($attributeValue);
        $boxAttribute->setAssociatedBoxId($this->getBoxId());
        
        $this->em->persist($boxAttribute);
        
        $this->em->flush();
    }
    
    /**
     *
     * @param array $arrAttributes
     *
     * @return boolean
     */
    public function setAttributes($arrAttributes)
    {
        foreach ($arrAttributes as $attributeName => $attributeValue) {
            $this->setAttribte($attributeName, $attributeValue);
        }
    }

    /**
     *
     * @param string $varName
     * @param string $defaultValue
     *
     * @return string
     */
    public function getAttribute($varName, $defaultValue = '')
    {
        $attributes = $this->getAttributes();
        
        if (isset($attributes[$varName])) {
            return $attributes[$varName];
        } else {
            return $defaultValue;
        }
    }
    
    public function saveView()
    {
        $this->setAttributes(Request::getInstance()->post());
    }
    
    public function getTemplateEngine()
    {
        return $this->templateEngine;
    }

    public function getPlaceholders()
    {
        return $this->placeholders;
    }

    public function setPlaceholders($placeholders)
    {
        $this->placeholders = $placeholders;
    }

    public function renderText($string)
    {
        return $this->templateEngine->render($string, $this->getPlaceholders());
    }

    public function applyPlaceholders($string)
    {
        return $this->templateEngine->render($string, $this->getPlaceholders());
    }
}
