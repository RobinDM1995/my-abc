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
use Symfony\Component\HttpFoundation\JsonResponse;
use Concrete\Package\PdfDesigner\Src\PDFDesigner;

class ImportTemplates extends BackendPageController
{
    protected $viewPath = '/dialogs/import_templates';
    
    private $pdfDesigner;
    
    public function __construct()
    {
        $this->pdfDesigner = PDFDesigner::getInstance();
        
        $this->set("pdfDesigner", $this->pdfDesigner);
        
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
    
    public function import()
    {
        $success = false;
        
        if (isset($_FILES["file"]) && isset($_FILES["file"]["tmp_name"])) {
            $templateFile = $_FILES["file"]["tmp_name"];
            
            if (file_exists($templateFile)) {
                $success = $this->pdfDesigner->importTemplate($templateFile);
            }
        }

        return new JsonResponse(array(
            "success" => $success
        ));
    }
    
    public function submit()
    {
        // Do nothing
    }

    public function action()
    {
        // Do nothing
    }
    
    public function view()
    {
        $this->requireAsset("dropzone");
        //
    }
}
