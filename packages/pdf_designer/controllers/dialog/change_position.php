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
use Concrete\Package\PdfDesigner\Src\PDFDesigner;
use Concrete\Core\Page\EditResponse;
use Database;

class ChangePosition extends BackendPageController
{
    protected $viewPath = '/dialogs/change_position';
    
    private $box;
    
    public function __construct()
    {
        $this->em = Database::connection()->getEntityManager();
        
        $this->box = PDFDesigner::getInstance($this->get("templateId"))
            ->getBoxEntity($this->get("boxId"));
        
        $this->set("box", $this->box);
        
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
        $this->box->setPositionType($this->post("positionType"));
        
        $this->box->setXPos($this->post("xPos"));
        $this->box->setYPos($this->post("yPos"));
        $this->box->setWidth($this->post("width"));
        $this->box->setHeight($this->post("height"));
        
        $this->box->setXPosInch($this->post("xPosInch"));
        $this->box->setYPosInch($this->post("yPosInch"));
        $this->box->setWidthInch($this->post("WidthInch"));
        $this->box->setHeightInch($this->post("HeightInch"));
        
        $this->em->persist($this->box);
        
        $this->em->flush();
        
        $er = new EditResponse();
        $er->setMessage(t('Box updated successfully.'));
        $er->outputJSON();
    }

    public function action()
    {
        // Do nothing
    }
    
    public function view()
    {
        //
    }
}
