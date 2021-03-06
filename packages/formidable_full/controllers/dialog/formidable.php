<?php 
namespace Concrete\Package\FormidableFull\Controller\Dialog;

use \Concrete\Package\FormidableFull\Src\Formidable as FormidableForm;
use \Concrete\Package\FormidableFull\Src\Formidable\Form;
use \Concrete\Core\Controller\Controller;
use BlockType;
use \Concrete\Core\Http\Service\Json as Json;
use \Concrete\Core\Page\Page;
use Block;
use Core;
use Events;

class Formidable extends Controller {

	protected $form = '';

	public function view() {
		
		$token = Core::make('token');
		if (!$token->validate('formidable_form')) {
			header('Content-type: application/json');
			echo Json::encode(array('message' => $token->getErrorMessage()));
			die();
		}

		$session = Core::make('app')->make('session');		
		$formID = $session->get('formidableFormID');		
		if (intval($this->post('formID')) != 0) $formID = intval($this->post('formID'));

		$bt = BlockType::getByHandle('formidable');
		if (intval($this->post('bID')) != 0) $bt = Block::getByID(intval($this->post('bID')));
		if (!is_object($bt)) return false;

		$form = Form::getByID($formID);		
		if (!is_object($form)) return false;
		$this->form = $form;

		switch ($this->post('action')) {				
			case 'submit':		
			case 'reviewed_back':	
			case 'reviewed_submit':	
				$r = $this->submit();
			break;
			case 'step':
				$r = $this->step();
			break;			
			case 'reset':
				$r = $this->reset();
			break;				
			case 'upload_file':	
				$r = $this->uploadFile();
				if ($r === false) $r = array('error' => t('Can\'t upload file'));
			break;
			case 'delete_file':	
				$r = $this->deleteFile();
				if ($r === true) $r = array('message' => t('File successfully deleted'));
				if ($r === false) $r = array('error' => t('Can\'t delete file'));
			break;		
		}
		
		if (!is_array($r)) {
			header('Content-type: text/html');
			if (method_exists($bt, 'display')) echo $bt->display();
			else {
				$type = 'view';
				if (intval($this->post('dashboard')) != 0) $type = '/templates/dashboard/view';
				$bt->render($type); 
			}
		} else {
			header('Content-type: application/json');
			echo Json::encode($r);
			exit();
		}
	}

	public function uploadFile() {
		if (!is_object($this->form)) return false;
		$elementID = $this->post('elementID');
		if (empty($elementID)) return false;
		$element = $this->form->getElementByID($elementID);	
		if (method_exists($element, 'uploadFile')) return $element->uploadFile();
		return false;
	}

	public function deleteFile() {
		if (!is_object($this->form)) return false;
		$elementID = $this->post('elementID');
		if (empty($elementID)) return false;
		$this->form->getElements();
		$element = $this->form->getElementByID($elementID);			
		if (method_exists($element, 'deleteFile')) return $element->deleteFile();
		return false;
	}

	public function submit() {		

		$form = $this->form;
		if (!is_object($form)) return false;

		// Set viewtype for previewing in Dash
		$view_type = $this->post('dashboard');
		if (!in_array($view_type, array('editing', 'preview'))) $view_type = '';

		// Validate submission
		$errors = array(
			'message' => array(),
			'errors' => array(),
			'clear' => true
		);
		
		// Check formID matches
		if (intval($this->post('formID')) != intval($form->getFormID())) $errors['message'][] = t("Wrong form is loaded in the page, please try again");	
								
		// Validate IP
		$ip = Core::make('helper/validation/ip');
		if ($ip->isBanned()) $errors['message'][] = $ip->getErrorMessage();		
		
		// Check for spammers...
		$antispam = Core::make('helper/validation/antispam');
		if (!$antispam->check(@implode("\n\r", $this->post()), 'formidable')) $errors['message'][] = t("Unable to complete action due to our spam policy. Please contact the administrator of this site for more information.");		
		
		// Honeypot
		if (strlen(trim($this->post('emailaddress'))) != 0) $errors['message'][] = t("Unable to complete action due to our spam policy. Please contact the administrator of this site for more information.");

		// If any error now, just return.. No use to go on.		
		if (is_array($errors['message']) && count($errors['message'])) return $errors;
		
		// Validate elements;	
		$elements = $form->getElements();		
		foreach((array)$elements as $element) {
			if (!is_object($element) || ($element->getElementType() == 'captcha' && in_array($view_type, array('editing', 'preview')))) continue;
			$error = $element->validateResult();
			if ($error === false) continue;
			$errors['clear'] = false;
			foreach ((array)$error as $e) { 
				$errors['errors'][] = array(
					'elementID' => $element->getElementID(),
					'handle' => $element->getHandle(),
					'message' => $e
				);
			}
		}

		// Return errors if there are any...
		if ((is_array($errors['message']) && (count($errors['message'])) ||  (is_array($errors['errors']) && count($errors['errors'])))) {
			if ($errors['clear'] === true) $this->reset();
			return $errors;
		}
		
		// No errors, do some saving...
		$data = array();
		
		if ($view_type != 'editing') {
			
			$c = FormidableForm::getCollection($this->post('cID'));						
			$u = FormidableForm::getUser();
			
			$bi = $form->registerBrowser()?FormidableForm::getBrowserInfo():'(disabled by GDPR)';
			$ip = $form->registerIP()?FormidableForm::getIP():'(disabled by GDPR)';

			if (!empty($this->post('locale'))) FormidableForm::setLocale($this->post('locale'));
			$locale = FormidableForm::getLocale();
				
			$data = array(
				'formID' => $form->getFormID(),
			    'userID' => is_object($u)?$u->getUserID():0, 
		   	  	'collectionID' => is_object($c)?$c->getCollectionID():0, 
			  	'browser' => is_array($bi)?@implode(' ', array($bi['name'], $bi['version'])):$bi,
			  	'platform' => is_array($bi)?ucfirst($bi['platform']):$bi,
			  	'resolution' => is_array($bi)?$this->post('resolution'):$bi,
			  	'submitted' => date("Y-m-d H:i:s"),
			  	'locale' => $locale,
			  	'ip' => $ip,
			  	'temp' => false
			);
		} 

		// Process the post to saveable data...
		if (is_array($elements) && count($elements)) {
			foreach ($elements as $element) {
				if ($element->isLayout() || $element->getElementType() == 'captcha') continue;		 

				// Now load the post data in the element
				$element->setValue();

				if ($element->getElementType() == 'upload') {
					$result = $element->processFiles();
					if (is_array($result['errors']) && count($result['errors'])) return array('message' => @implode('<br>', $result['errors']));
					$element->setValue(array('value' => $result['files']), true);		
				}

				$data['answers'][$element->getElementID()] = array(
					'formID' => $form->getFormID(),
				   	'elementID' => $element->getElementID(),
				   	'answer_formated' => $element->getDisplayValue(),
				   	'answer_unformated' => $element->getSerializedValue()
				);
			}
		}
			
		// Now save
		$result = $form->getResult();
		if (!$result->save($data)) {
			return array('message' => t('Can\'t save data! Please try again later'));
		}		
		
		// If the form is loaded in the dash, don't send any emails..
		if ($view_type != 'editing') {
			// Update element submissions if there are any
			if (is_array($elements) && count($elements)) {
				foreach ($elements as $element) {
					$element->updateOnSubmission();		
				}
			}
			$mailings = $form->getMailings();
			if (is_array($mailings) && count($mailings)) {
				foreach ($mailings as $mailing) {				
					$mailing->setResult($result, true);
					$mailing->send(true);
				}
			}	
		}
		
		// Fire event
		$event = new \Symfony\Component\EventDispatcher\GenericEvent();
		$event->setArgument('form', $form);
		$event->setArgument('data', $this->post());
		$event->setArgument('result', $result);		
		Events::dispatch('on_formidable_submit', $event);
		
		// Reset answerset and clear post data
		$this->reset();
		
		// Show message
		$message = $form->getAttribute('submission_redirect_content');
		if (in_array($view_type, array('editing', 'preview'))) {			
			if ($form->getAttribute('submission_redirect')) $message = t('Submission is successfull. (In the front-end version the user will be redirected to a page.)');
			return array('success' => $message,'clear' => true);
		}

		// Redirect to page
		if ($form->getAttribute('submission_redirect')) {
			$p = Page::getByID($form->getAttribute('submission_redirect_page'));
			if (is_object($p)) return array('redirect' => Core::make('helper/navigation')->getLinkToCollection($p));
		}

		return array('success' => $message, 'clear' => true);
	}

	public function step() {	

		$form = $this->form;
		if (!is_object($form)) return false;

		// Set viewtype for previewing in Dash
		$view_type = $this->post('dashboard');
		if (!in_array($view_type, array('editing', 'preview'))) $view_type = '';

		// Validate submission
		$errors = array(
			'message' => array(),
			'errors' => array()
		);
		
		// Check formID matches
		if (intval($this->post('formID')) != intval($form->getFormID())) $errors['message'][] = t("Wrong form is loaded in the page, please try again");	
								
		// If any error now, just return.. No use to go on.		
		if (is_array($errors['message']) && count($errors['message'])) return $errors;

		// Validate elements;	
		$elements = $form->getElementsByStep($this->post('step'));		
		foreach((array)$elements as $element) {
			if (!is_object($element) || ($element->getElementType() == 'captcha' && in_array($view_type, array('editing', 'preview')))) continue;		
			$error = $element->validateResult();
			if ($error === false) {
				if ($element->getElementType() == 'captcha') $errors['captcha'] = true;
				continue;
			}			
			foreach ((array)$error as $e) { 
				$errors['errors'][] = array(
					'elementID' => $element->getElementID(),
					'handle' => $element->getHandle(),
					'message' => $e
				);
			}
		}

		// Return errors if there are any...
		if ((is_array($errors['message']) && count($errors['message'])) || (is_array($errors['errors']) && count($errors['errors']))) {
			return $errors;
		}
						
		// Fire event
		$event = new \Symfony\Component\EventDispatcher\GenericEvent();
		$event->setArgument('form', $form);
		$event->setArgument('data', $this->post());
		Events::dispatch('on_formidable_step', $event);
	
		return array('success' => true);
	}

	public function reset() {
		$session = Core::make('app')->make('session');
		$session->remove('answerSetID'.$this->form->getFormID());	
		$session->remove('captcha'.$this->form->getFormID());
		// Not sure if this is allowed... But I want to reset all post vars
		unset($_POST); 
	}

	public function topJS() {
		$script = "var I18N_FF = {
                        'Choose State/Province': '".t('Choose State/Province')."',
						'Please wait...': '".t('Please wait...')."'
                   }"; 
		header('Content-Type: application/javascript');
		echo $script;
		exit();
	}

	// Method: bottomJS
	// This is loaded for each Formidable Block, so hard to individualize.
	public function bottomJS($formID) {		
		$form = Form::getByID($formID);		
		if (!is_object($form)) return false;
		$form->generate();
		$script  = $form->getJavascript();
		/*
		// Jquery is currently loaded in each view.php of the block.
		// This way customizing the javascript is much easier!
		$script .= '
			$(function() {
				var formObj'.$form->getFormID().' = $(\'form[id="ff_'.$form->getFormID().'"]\');
				var ff_'.$form->getFormID().' = formObj'.$form->getFormID().'.formidable({
	                \'error_messages_on_top\': false,
	                \'error_messages_on_top_class\': \'alert alert-danger\',
	                \'warning_messages_class\': \'alert alert-warning\',
	                \'error_messages_beneath_field\': true,
	                \'error_messages_beneath_field_class\': \'text-danger error\',
	                \'success_messages_class\': \'alert alert-success\',
	                \'remove_form_on_success\': true,
	                errorCallback: function() { },
	                successCallback: function() { }
	            });
	           	'.$form->getJquery().'
	           	ff_'.$form->getFormID().'.check_depencencies();
			});';     
		*/  	
        header('Content-Type: application/javascript');
		echo $script!==false?$script:'';
		exit();      	
	}
}