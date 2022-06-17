<?php 
namespace Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Forms;

use \Concrete\Package\FormidableFull\Src\Formidable\Form;
use \Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\BackendInterfaceController;

class Dialog extends BackendInterfaceController {

	protected $viewPath = '/dialogs/forms/dialog';
	protected $token = 'formidable_form';

	public function delete() {
		$r = $this->validateAction();
		if ($r === true) {
			$f = Form::getByID($this->get('formID'));
			if (is_object($f)) {
				$r = $this->checkFormPermissions($f);
				if ($r === true) $this->set('f', $f);
			}
		}
		$this->set('errors', $r);
	}	
}