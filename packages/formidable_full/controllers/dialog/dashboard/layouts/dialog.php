<?php 
namespace Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Layouts;

use \Concrete\Package\FormidableFull\Src\Formidable\Form;
use \Concrete\Package\FormidableFull\Src\Formidable\Layout;
use \Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\BackendInterfaceController;

class Dialog extends BackendInterfaceController {

	protected $viewPath = '/dialogs/layouts/dialog';
	protected $token = 'formidable_layout';

	public function view() {
		$r = $this->validateAction();
		if ($r === true) {
			$f = Form::getByID($this->get('formID'));
			if (is_object($f)) {
				$r = $this->checkFormPermissions($f);	
				if ($r === true) {
					$layouts = $f->getLayout();				
					if ($this->get('layoutID') == -1 && intval($this->get('rowID')) > -1) $layout = $layouts[intval($this->get('rowID'))]; 
					elseif ($this->get('layoutID') > -1) $layout = Layout::getByID($this->get('layoutID'));	
					else $layout = new Layout();			
					$this->set('layout', $layout);

					if ($this->get('layoutID') > -1) {
						$this->set('appearances', array(
							'default' => t('Div'), 
							'fieldset' => t('Fieldset (with legend, if label exists)')
						));
					}
					else {
						$this->set('appearances', array(
							'default' => t('Row'), 
							'step' => t('Step (multistep form)')
						));
					}
				}
			}
		}
		$this->set('errors', $r);
	}

	public function select() {
		$r = $this->validateAction('formidable_element');
		if ($r === true) {
			$f = Form::getByID($this->get('formID'));
			if (is_object($f)) {
				$r = $this->checkFormPermissions($f);	
				if ($r === true) {
					$this->set('f', $f);
				}
			}
		}
		$this->set('errors', $r);
	}

	public function delete() {
		$r = $this->validateAction();
		if ($r === true) {
			$f = Form::getByID($this->get('formID'));
			if (is_object($f)) {		
				$r = $this->checkFormPermissions($f);	
				if ($r === true) {		
					$layouts = $f->getLayout();				
					if ($this->get('layoutID') == -1 && intval($this->get('rowID')) > -1) $layout = $layouts[intval($this->get('rowID'))];
					elseif ($this->get('layoutID') > -1) $layout = Layout::getByID($this->get('layoutID'));	
				
					$this->set('layout', $layout);
					$this->set('layoutID', $this->get('layoutID'));
					$this->set('rowID', $this->get('rowID'));
				}
			}
		}
		$this->set('errors', $r);
	}

	public function duplicate() {
		$this->delete();
	}
}