<?php 
namespace Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Results;

use \Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\BackendInterfaceController;
use \Concrete\Package\FormidableFull\Src\Formidable\Result as Result;
use \Concrete\Core\Http\Request;

class Dialog extends BackendInterfaceController {

	protected $viewPath = '/dialogs/results/dialog';
	protected $token = 'formidable_result';

	public function view() {
		$r = $this->validateAction();
		if ($r === true) {
			$results = array();
			$request = Request::getInstance()->request();
			$r = $this->checkFormPermissions($request['formID'], 'results');
			if ($r === true) {
				if (is_array($request['item']) && count($request['item'])) {	
					foreach ($request['item'] as $answerSetID) {
						$result = Result::getByID($answerSetID);
						if (is_object($result))	$results[] = $result;
					}
				}
				$this->set('formID', $request['formID']);
				$this->set('results', $results);
			}
		}
		$this->set('errors', $r);
	}
	
	public function delete() {
		$this->view();
	}

	public function resend() {
		$this->view();
	}

	public function delete_all() {
		$this->view();
	}
}
