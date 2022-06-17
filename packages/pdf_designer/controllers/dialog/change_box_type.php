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

class ChangeBoxType extends BackendPageController
{
    protected $viewPath = '/dialogs/change_box_type';
    
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
        $this->box->setBoxType($this->post("boxType"));
        $this->em->persist($this->box);
        $this->em->flush();
        
        $er = new EditResponse();
        $er->setMessage(t('Box updated successfully.'));
        $er->outputJSON();
    }

    public function action()
    {
        //
    }
    
    public function view()
    {
    }
}
