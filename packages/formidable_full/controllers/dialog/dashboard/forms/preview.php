<?php 
namespace Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Forms;

use \Concrete\Package\FormidableFull\Src\Formidable\Form;
use \Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\BackendInterfaceController;
use Core;

class Preview extends BackendInterfaceController {
	
	protected $viewPath = '/dialogs/forms/preview';
	protected $token = 'formidable_preview';

	public function __construct() {
		parent::__construct();
		$f = Form::getByID(intval($this->get('formID')));		
		if (is_object($f)) $this->set('form', $f);		
	}

	public function view() {
		$this->preview();
	}

	public function result() {
		$r = $this->validateAction();
		if ($r === true) {
			$form = $this->get('form');
			if (is_object($form)) {
				$form->setAnswerSetID($this->get('answerSetID'), false);
				$result = $form->getResult();
				$this->set('result', $result);
			}
		}
		$this->set('errors', $r);
	}

	private function preview() {		
		$r = $this->validateAction();
		if ($r === true) {	
			$form = $this->get('form');
			if (is_object($form)) {				
					
				// Clear answerset in case one is still active
				$session = Core::make('app')->make('session');
				$session->remove('answerSetID'.$form->getFormID());

				// set answerset if needed
				$answerSetID = intval($this->get('answerSetID'));
				if ($answerSetID == 0) $answerSetID = intval($session->get('answerSetID'.$form->getFormID()));
				$form->setAnswerSetID($answerSetID);		

				$view_type = 'preview';
				if (!empty($form->getAnswerSetID())) $view_type = 'editing';

				$this->set('view_type', $view_type);
				$this->set('form', $form);	
			}
		}
		$this->set('errors', $r);
	}
}