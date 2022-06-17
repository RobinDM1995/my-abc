<?php 
namespace Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Forms;

use \Concrete\Package\FormidableFull\Src\Formidable\Form;
use \Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\BackendInterfaceController;

class MailingList extends BackendInterfaceController {
	
	protected $viewPath = '/dialogs/forms/mailing_list';
	protected $token = 'formidable_mailing';

	public function view() {		
		$r = $this->validateAction();
		if ($r === true) {
			$mailings = false;
			$f = Form::getByID(intval($this->post('formID')));		
			if (is_object($f)) {
				$r = $this->checkFormPermissions($f);
				if ($r === true) {
					$mailings = $f->getMailings(); 
					$this->set('mailings', $mailings);
				}
			}
		}
		$this->set('errors', $r);
	}
}
