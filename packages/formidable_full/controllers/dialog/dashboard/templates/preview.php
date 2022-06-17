<?php 
namespace Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Templates;

use \Concrete\Package\FormidableFull\Src\Formidable\Template;
use \Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\BackendInterfaceController;
use BlockType;
use Core;
use \Concrete\Core\Page\Page;
use Permissions;

class Preview extends BackendInterfaceController {

	protected $viewPath = '/dialogs/templates/preview';
	protected $token = 'formidable_preview';

	public function __construct() {
		parent::__construct();
		$t = Template::getByID($this->get('templateID'));
		if (is_object($t)) $this->set('template', $t);			
	}

	public function view() {
		$this->preview();
	}

	private function preview() {		
		$r = $this->validateAction();
		$this->set('errors', $r);
	}
}