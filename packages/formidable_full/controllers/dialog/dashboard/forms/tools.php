<?php 
namespace Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Forms;

use \Concrete\Package\FormidableFull\Src\Formidable\Form;
use \Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\BackendInterfaceController;
use \Concrete\Core\Http\Request;
use \Concrete\Core\Support\Facade\Application;

class Tools extends BackendInterfaceController {
	
	protected $token = 'formidable_form';
	
	public function handle() {
		$r = $this->validateAction();
		if ($r === true) {
			$app = Application::getFacadeApplication();
			if (empty($name)) $name = $this->get('name');
			$r = array(
				'type' => 'success', 
				'message' => t('Form handle successfully created'),
				'handle' => $app->make('helper/text')->handle($name)
			);
		}
		$this->json($r);
	}

	public function duplicate() {
		$r = $this->validateAction();
		if ($r === true) {
			$r = array(
				'type' => 'error', 
				'message' => t('Error: Form can\'t be duplicated')
			);
			$f = Form::getByID($this->post('formID'));					
			if (is_object($f)) {
				$r = $this->checkFormPermissions($f);	
				if ($r === true) {				
					if ($f->duplicate()) {
						$r = array(
							'type' => 'info', 
							'message' => t('Form successfully duplicated')
						);
					}
				}
			}			
		}
		$this->json($r);
	}

	public function delete() {
		$r = $this->validateAction();
		if ($r === true) {
			$r = array(
				'type' => 'error', 
				'message' => t('Error: Form can\'t be deleted')
			);
			$f = Form::getByID($this->post('formID'));	
			if (is_object($f)) {
				$r = $this->checkFormPermissions($f);	
				if ($r === true) {
					if ($f->delete()) {
						$r = array(
							'type' => 'info', 
							'message' => t('Form is successfully deleted')
						);
					}
				}
			}
		}
		$this->json($r);
	}
}
