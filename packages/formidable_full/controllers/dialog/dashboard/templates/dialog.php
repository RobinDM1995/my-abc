<?php 
namespace Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Templates;

use \Concrete\Package\FormidableFull\Src\Formidable\Template;
use \Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\BackendInterfaceController;

class Dialog extends BackendInterfaceController {

	protected $viewPath = '/dialogs/templates/dialog';
	protected $token = 'formidable_form';

	public function delete() {
		$r = $this->validateAction();
		if ($r === true) {
			$t = Template::getByID($this->get('templateID'));
			if (is_object($t)) $this->set('t', $t);		
		}
		$this->set('errors', $r);
	}	
}