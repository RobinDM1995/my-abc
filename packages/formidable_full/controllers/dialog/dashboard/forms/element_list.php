<?php
namespace Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Forms;

use \Concrete\Package\FormidableFull\Src\Formidable\Form;
use \Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\BackendInterfaceController;

class ElementList extends BackendInterfaceController {
	
	protected $viewPath = '/dialogs/forms/element_list';
	protected $token = 'formidable_element';

	public function view() {		
		$r = $this->validateAction();
		if ($r === true) {
			$f = Form::getByID(intval($this->post('formID')));		
			if (is_object($f)) {
				$r = $this->checkFormPermissions($f);
				if ($r === true) {
					$layout = $f->getLayout();
					if (!is_array($layout) || !count($layout)) $r = array('message' => t('Form is empty or corrupt. Please remove form and create a new one.'));
					$this->set('layouts', $layout);
				}
			}
		}
		$this->set('errors', $r);		
	}
}
