<?php 
namespace Concrete\Package\FormidableFull\Controller\Dialog\Dashboard;

use \Concrete\Package\FormidableFull\Src\Formidable\Form;
use \Concrete\Controller\Backend\UserInterface;
use \Concrete\Core\Http\Service\Json as Json; 
use Core;
use \Concrete\Core\Page\Page;
use Permissions;

class BackendInterfaceController extends UserInterface {

    protected $viewPath = '';

	protected function validateAction($tk = null) {
        $token = Core::make('token');
        if (empty($tk)) $tk = $this->token;
		if (!$token->validate($tk)) return array( 'type' => 'error', 'message' => $token->getErrorMessage().'<br>'.t('If this keeps happening, please clear the cache of your browser and this C5 installation.'));		
		if (!$this->canAccess()) return array('type' => 'error', 'message' => t('Access Denied'));
		return true;
	}

	protected function canAccess() {
		$c = Page::getByPath('/dashboard/formidable/');
		$cp = new Permissions($c);
		return $cp->canRead();
	}
	
	protected function checkFormPermissions($formID = 0, $type = 'form') {
		if (!is_object($formID)) $f = Form::getByID($formID);
		else $f = $formID;
		if (!is_object($f)) return array('type' => 'error', 'message' => t('Can\'t find the Formidable Form'));
		if (!$f->hasPermissions($type)) return array('type' => 'error',	'message' => t('You don\'t have permission to add, view or edit this form or result.'));
		return true;
	}

    protected function json($array) {
		echo Json::encode($array);
		die();
	}
}