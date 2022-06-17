<?php 
namespace Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Templates;

use \Concrete\Package\FormidableFull\Src\Formidable\Template;
use \Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\BackendInterfaceController;

class Tools extends BackendInterfaceController {
	
	protected $token = 'formidable_form';
	
	public function duplicate() {
		$r = $this->validateAction();
		if ($r === true) {
			$r = array(
				'type' => 'error', 
				'message' => t('Error: Template can\'t be duplicated')
			);
			$t = Template::getByID($this->post('templateID'));
			if (is_object($t)) {						
				if ($t->duplicate()) {
					$r = array(
						'type' => 'info', 
						'message' => t('Template successfully duplicated')
					);
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
				'message' => t('Error: Template can\'t be deleted')
			);
			$t = Template::getByID($this->post('templateID'));
			if (is_object($t)) {						
				if ($t->delete()) {
					$r = array(
						'type' => 'info', 
						'message' => t('Template is successfully deleted')
					);
				}
			}
		}
		$this->json($r);
	}
}
