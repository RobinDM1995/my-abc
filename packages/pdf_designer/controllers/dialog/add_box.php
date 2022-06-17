<?php

/**
 * @project:   PDFDesigner (concrete5 add-on)
 *
 * @author     Fabian Bitter
 * @copyright  (C) 2016 Fabian Bitter (www.bitter.de)
 * @version    1.2.1
 */

namespace Concrete\Package\PdfDesigner\Controller\Dialog;

defined('C5_EXECUTE') or die('Access denied');

use Concrete\Controller\Backend\UserInterface\Page as BackendPageController;
use Concrete\Package\PdfDesigner\Src\Entity\Box;
use Concrete\Package\PdfDesigner\Src\PDFDesigner;
use Concrete\Core\Page\EditResponse;
use Database;

class AddBox extends BackendPageController
{
    protected $viewPath = '/dialogs/add_box';
    
    public function __construct()
    {
        $this->em = Database::connection()->getEntityManager();
        
        parent::__construct();
    }
    
    public function on_start()
    {
        // Do nothing
    }
    
    public function canAccess()
    {
        return true;
    }
    
    public function submit()
    {
        $box = new Box;
        
        $box->setTemplateId($this->get("templateId"));
        
        $box->setBoxType($this->get("boxType"));
        
        $box->setPositionType($this->post("positionType"));
        
        $box->setXPos($this->post("xPos"));
        $box->setYPos($this->post("yPos"));
        $box->setWidth($this->post("width"));
        $box->setHeight($this->post("height"));
        
        $box->setXPosInch($this->post("xPosInch"));
        $box->setYPosInch($this->post("yPosInch"));
        $box->setWidthInch($this->post("WidthInch"));
        $box->setHeightInch($this->post("HeightInch"));
        
        $this->em->persist($box);
        
        $this->em->flush();
        
        $er = new EditResponse();
        $er->setMessage(t('Box added successfully.'));
        $er->outputJSON();
    }

    public function action()
    {
        // Do nothing
    }
    
    public function view()
    {
        $this->set("templateId", $this->get("templateId"));
        $this->set("boxType", $this->get("boxType"));
        $this->set("pdfDesigner", PDFDesigner::getInstance($this->get("templateId")));
        $this->set("template", PDFDesigner::getInstance($this->get("templateId"))->getTemplateEntity());
    }
}
