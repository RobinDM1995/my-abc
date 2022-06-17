<?php 
namespace Concrete\Package\FormidableFull\Controller\SinglePage\Dashboard\Formidable;

use \Concrete\Core\Page\Controller\DashboardPageController;
use \Concrete\Package\FormidableFull\Src\Formidable;
use \Concrete\Package\FormidableFull\Src\Formidable\Form;
use \Concrete\Package\FormidableFull\Src\Formidable\Mailing;
use \Concrete\Core\Form\Service\Validation;
use \Concrete\Core\Form\Service\Widget\DateTime;
use \Concrete\Core\User\UserInfo;
use \Concrete\Core\Support\Facade\Config;
use GroupList;

class Forms extends DashboardPageController {

	private $default_mailing = '';
	
	public function __construct($c) {	
		parent::__construct($c);
				
		// Default mailing on creating ne form		
		$ui = UserInfo::getByID(USER_SUPER_ID);
		
		$sitename = Config::get('concrete.site');		
		$this->default_mailing = array(
			'mailingID' => 0,
		    'from_type' => 'other',
		    'from_name' => $sitename,
		    'from_email' => $ui?$ui->getUserEmail():'',
		    'send_custom' => 1,
		    'send_custom_value' => $ui?$ui->getUserEmail():'',
		    'subject' => t('%s submission', $sitename),
		    'message' => sprintf('<p>%s<br />%s</p><p>%s</p><p>%s</p><p>%s</p><p>%s</p>',
								 t('You successfully sent our %s on our Concrete5 website.', $sitename),
								 t('The following information was sent to us:'),
								 '{%all_elements%}',
								 t('Thank you!'),
								 t('Regards,'),
								 $sitename)
		);		
		
		$this->requireAsset('javascript-inline', 'formidable/inline/dashboard/forms/top');
		$this->requireAsset('javascript', 'formidable/dashboard/common');
		$this->requireAsset('javascript', 'formidable/dashboard/forms');
		$this->requireAsset('css', 'formidable/dashboard');

		$gl = new GroupList();
		$gl->sortBy('gID', 'asc');
		//$gl->includeAllGroups();
		$groups = $gl->getResults();
		$this->set('groups', $groups);

		$permission = Formidable::hasAddPermissions();
		$this->set('can_add', $permission);
	}            
		
	public function add() {	

		$permission = Formidable::hasAddPermissions();
		if (!$permission) $this->redirect('/dashboard/formidable/forms', 'message', 'no_permissions');

		$f = new Form();	
		$f->label = t('My Formidable Form');
		$f->handle = \Core::make('helper/text')->handle($f->label);
		$f->setAttribute('submission_redirect', $this->submission_redirect);
		$f->setAttribute('submission_redirect_content', t('Thank you!'));	
		$this->set('f', $f);
		$this->set('create_form', true);
	}
	
	public function edit($id, $form_new = false) {			
		if ($form_new) $this->set('message', t('Form saved successfully'));

		$f = Form::getByID($id);		
		if (!is_object($f)) $this->redirect('/dashboard/formidable/forms', 'message', 'notfound');
		elseif (!$f->hasPermissions()) $this->redirect('/dashboard/formidable/forms', 'message', 'no_permissions');
		else {				
			$this->set('f', $f);		
			$this->set('create_form', true);
		}
	}
		
	public function save() {	
		
		$date_time = new DateTime();

		$val = new Validation();	
		$val->setData($this->post());
		
		$val->addRequired('label', t('Field "%s" is invalid', t('From name')));	
		$val->addRequired('handle', t('Field "%s" is invalid', t('Handle')));	
		if (intval($this->post('css')) == 1) $val->addRequired('css_value', t('Field "%s" is invalid', t('CSS value')));		
		$val->addRequired('submission_redirect', t('Field "%s" is invalid', t('Action after submission')));		
		if (intval($this->post('submission_redirect')) == 1) $val->addRequired('submission_redirect_page', t('Field "%s" is invalid', t('Page (submission)')));
		else $val->addRequired('submission_redirect_content', t('Field "%s" is invalid', t('Message (submission)')));		
		if (intval($this->post('limits')) == 1) { 
			$val->addInteger('limits_value', t('Field "%s" is invalid number', t('Limit (value)')), false);
			$val->addRequired('limits_type', t('Field "%s" is invalid', t('Limit (type)')));	
			if (intval($this->post('limits_redirect')) == 1) $val->addRequired('limits_redirect_page', t('Field "%s" is invalid', t('Page (limits)')));
			else $val->addRequired('limits_redirect_content', t('Field "%s" is invalid', t('Message (limits)')));
		}
		if (intval($this->post('schedule')) == 1) { 
			$val->addRequired('schedule_start_dt', t('Field "%s" is invalid date', t('Start date (schedule)')));
			$val->addRequired('schedule_end_dt', t('Field "%s" is invalid date', t('End date (schedule)')));
			if (intval($this->post('schedule_redirect')) == 1) $val->addRequired('schedule_redirect_page', t('Field "%s" is invalid', t('Page (schedule)')));
			else $val->addRequired('schedule_redirect_content', t('Field "%s" is invalid', t('Message (schedule)')));
		}
		if (intval($this->post('gdpr')) == 1) { 
			$val->addInteger('gdpr_value', t('Field "%s" is invalid number', t('GDPR Compliance (value)')), false);
			$val->addRequired('gdpr_type', t('Field "%s" is invalid', t('GDPR Compliance (type)')));	
		}
		if (intval($this->post('permission')) == 1) {
			if (!count($this->post('permission_form'))) $val->addRequired('dummy', t('Field "%s" is invalid or non selected', t('Form Permissions')));
			if (!count($this->post('permission_results'))) $val->addRequired('dummy', t('Field "%s" is invalid or non selected', t('Result Permissions')));
		}

		

		if ($val->test()) {	
			$v = array(
				'formID' => $this->post('formID'),
				'label' => h($this->post('label')), 
				'handle' => \Core::make('helper/text')->handle($this->post('handle')),
				'submission_redirect' => intval($this->post('submission_redirect')), 
				'submission_redirect_page' => intval($this->post('submission_redirect'))==1?intval($this->post('submission_redirect_page')):0, 
				'submission_redirect_content' => $this->post('submission_redirect_content'),

				'limits' => intval($this->post('limits')), 
				'limits_value' => intval($this->post('limits'))==1?intval($this->post('limits_value')):0,
				'limits_type' => intval($this->post('limits'))==1?$this->post('limits_type'):'',
				'limits_redirect' => intval($this->post('limits_redirect')), 
				'limits_redirect_page' => intval($this->post('limits_redirect'))==1?intval($this->post('limits_redirect_page')):0, 
				'limits_redirect_content' => $this->post('limits_redirect_content'),
				
				'schedule' => intval($this->post('schedule')), 
				'schedule_start' => intval($this->post('schedule'))==1?$date_time->translate('schedule_start'):date("Y-m-d H:i:s"),
				'schedule_end' => intval($this->post('schedule'))==1?$date_time->translate('schedule_end'):date("Y-m-d H:i:s"),
				'schedule_redirect' => intval($this->post('schedule_redirect')), 
				'schedule_redirect_page' => intval($this->post('schedule_redirect'))==1?intval($this->post('schedule_redirect_page')):0, 
				'schedule_redirect_content' => $this->post('schedule_redirect_content'),

				'gdpr' => intval($this->post('gdpr')), 
				'gdpr_value' => intval($this->post('gdpr'))==1?intval($this->post('gdpr_value')):0,
				'gdpr_type' => intval($this->post('gdpr'))==1?$this->post('gdpr_type'):'',
				'gdpr_ip' => intval($this->post('gdpr'))==1?intval($this->post('gdpr_ip')):0, 
				'gdpr_browser' => intval($this->post('gdpr'))==1?intval($this->post('gdpr_browser')):0,

				'permission' => intval($this->post('permission')), 
				'permission_form' => intval($this->post('permission'))==1?implode(',',$this->post('permission_form')):'',
				'permission_results' => intval($this->post('permission'))==1?implode(',',$this->post('permission_results')):'',

				'css' => intval($this->post('css')), 
				'css_value' => intval($this->post('css'))==1?h($this->post('css_value')):''
			);
			
			$f = new Form();
			if ($this->post('formID')) $f->load($this->post('formID'));						
			$f->save($v);

			if (intval($this->post('formID')) == 0)	{	
				// Add default mailing to Formidable
				$default_mailing = $this->default_mailing;
				$default_mailing['formID'] = $f->getFormID();
				
				$fm = new Mailing();								
				$fm->save($default_mailing);
								
				$this->redirect('/dashboard/formidable/forms/elements/'.$f->getFormID().'/true/');
			}
			$this->redirect('/dashboard/formidable/forms/edit/'.$f->getFormID().'/true/');
		}

		$this->set('errors', $val->getError()->getList());
		$this->set('create_form', true);
		
		if ($this->post('formID')) {					
			$f = Form::getByID($this->post('formID'));
			if (is_object($f)) {					
				$this->set('f', $f);
			}
		}
	}
	
	public function message($mode = 'deleted') {
		$type = 'error';
		switch($mode) {
			case 'notfound': $msg = t('Form can\'t be found!'); break;			
			case 'no_permissions': $msg = t('You don\'t have permission to add, view or edit this form or result.'); break;
			case 'error': $msg = t('Oops, something went wrong!'); break;
			case 'saved': $msg = t('Form saved successfully');  $type = 'success';  break;
			default: $msg = t('Form deleted successfully'); $type = 'success';  break;
		}
		$this->addFooterItem('<script>$(function() { ccmFormidableSetMessage("'.$type.'", "'.$msg.'") }); </script>');
	}
}