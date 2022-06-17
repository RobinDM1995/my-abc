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
//use Concrete\Core\Page\EditResponse;
use Database;

class ImportGoogleFont extends BackendPageController
{
    protected $viewPath = '/dialogs/import_google_font';
    
    private $em;
    
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
        //
    }

    public function action()
    {
        //
    }
    
    public function view()
    {
        $this->set("fonts", PDFDesigner::getInstance($this->get("templateId"))->googleGetAllFonts());
        
        $this->requireAsset("css", "bootstrap");
    }
}
