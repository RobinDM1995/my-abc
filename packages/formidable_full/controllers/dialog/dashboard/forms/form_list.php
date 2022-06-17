<?php 
namespace Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Forms;

use \Concrete\Package\FormidableFull\Src\Formidable\FormList AS FFList;
use \Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\BackendInterfaceController;
use \Concrete\Core\User\User;

class FormList extends BackendInterfaceController {
	
	protected $viewPath = '/dialogs/forms/form_list';
	protected $token = 'formidable_form';

	public function view() {		
		$r = $this->validateAction();
		if ($r === true) {
			$list = new FFList();
			$list->filterByPermissions();
			$forms = $list->getResults();
			$this->set('forms', $forms);
		}
		$this->set('errors', $r);		
	}
}
