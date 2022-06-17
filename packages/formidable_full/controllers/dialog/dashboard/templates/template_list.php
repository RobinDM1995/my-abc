<?php 
namespace Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Templates;

use \Concrete\Package\FormidableFull\Src\Formidable\TemplateList AS FTList;
use \Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\BackendInterfaceController;

class TemplateList extends BackendInterfaceController {
	
	protected $viewPath = '/dialogs/templates/template_list';
	protected $token= 'formidable_form';

	public function view() {		
		$r = $this->validateAction();
		if ($r === true) {
			$list = new FTList();
			$templates = $list->getResults();
			$this->set('templates', $templates);
		}
		$this->set('errors', $r);		
	}
}
