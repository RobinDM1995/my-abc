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

class EditBox extends BackendPageController
{
    protected $viewPath = '';
    private $editor;
    
    public function __construct()
    {
        $this->editor =
            PDFDesigner::getInstance($this->get("templateId"))
            ->getBoxEntity($this->get("boxId"))
            ->getEditor();
        
        $this->editor->setPage($this);
        
        $this->viewPath = $this->editor->getViewPath();
        
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
        $this->editor->saveView();
        
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
        $this->requireAsset("css", "bootstrap");
        $this->requireAsset("jquery/ui");
        $this->requireAsset("colorpicker");
        
        $this->editor->renderView();
    }
}
