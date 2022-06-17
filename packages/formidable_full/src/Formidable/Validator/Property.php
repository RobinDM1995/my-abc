<?php    
namespace Concrete\Package\FormidableFull\Src\Formidable\Validator;

use \Concrete\Package\FormidableFull\Src\Formidable;
use \Concrete\Core\Utility\Service\Validation\Strings;
use \Concrete\Core\Utility\Service\Validation\Numbers;
use \Concrete\Core\Support\Facade\Application;
use \Concrete\Core\Support\Facade\Config;

class Property extends Formidable {
	
	protected $app = '';

	protected $element = '';

	protected $post = array();
	protected $errors = array();

	public function __construct() {
		$this->app = Application::getFacadeApplication();
	}

	public function setData($post) {
		$this->post = $post;
	}
	public function addError($error) {		
		$this->errors[] = $error;	
	}
	public function getList() {		
		return (is_array($this->errors)&&count($this->errors))?$this->errors:false;	
	}
	
	protected function notempty($value) {
		$str = new Strings($this->app);
		if ($str->notempty($value)) return true;
		return false;
	}	
	// Validation for elements
	public function label() {
		if (!$this->notempty($this->post['label'])) $this->addError(t('Field "%s" is invalid', t('Label / Name')));	
	}

	public function placeholder() {
		$str = new Strings($this->app);
		if ($this->post['placeholder'] && !$this->notempty($this->post['placeholder_value'])) $this->addError(t('Field "%s" is invalid', t('Placeholder')));	
	}
	
	public function defaultValue() {
		$str = new Strings($this->app);
		if ($this->post['default'] && !$this->notempty($this->post['default_value']) && !$this->notempty($this->post['default_value_'.$this->post['default_value_type']])) $this->addError(t('Field "%s" is invalid', t('Default')));		
	}

	public function submissionUpdate() {
		$str = new Strings($this->app);
		if ($this->post['submission_update'] && !$this->notempty($this->post['submission_update_value']) && !$this->notempty($this->post['submission_update_'.$this->post['submission_update_type']])) $this->addError(t('Field "%s" is invalid', t('Submission Update')));		
	}

	public function mask() {
		$str = new Strings($this->app);
		if ($this->post['mask'] && !$this->notempty($this->post['mask_format'])) $this->addError(t('Field "%s" is invalid', t('Masking')));	
	}

	public function minMax() {
		$nbr = new Numbers($this->app);
		if ($this->post['min_max']) {	
			if (!$nbr->integer($this->post['min_value'])) $this->addError(t('Field "%s" isn\'t a valid integer', t('Minimum value')));
			if (!$nbr->integer($this->post['max_value']) && $this->notempty($this->post['max_value'])) $this->addError(t('Field "%s" isn\'t a valid integer', t('Maximum value')));
			if (!$this->notempty($this->post['min_max_type'])) $this->addError(t('Field "%s" invalid', t('Minimum/Maximum type')));
		}
	}

	public function tooltip() {
		$str = new Strings($this->app);
		if ($this->post['tooltip'] && !$this->notempty($this->post['tooltip_value'])) $this->addError(t('Field "%s" is invalid', t('Tooltip')));	
	}
	
	public function tinymce() {
		if (!$this->notempty($this->post['tinymce_value'])) $this->addError(t('Field "%s" is invalid', t('Content')));	
	}

	public function htmlCode() {
		if (!$this->notempty($this->post['html_value'])) $this->addError(t('Field "%s" is invalid', t('Code')));	
	}

	public function appearance() {
		if (!$this->notempty($this->post['appearance'])) $this->addError(t('Field "%s" is invalid', t('Appearance')));	
	}

	public function advanced() {
		$str = new Strings($this->app);
		if ($this->post['advanced'] && !$this->notempty($this->post['advanced_value'])) $this->addError(t('Field "%s" is invalid', t('Advanced options')));	
	}

	public function fileset() {
		$str = new Strings($this->app);
		if ($this->post['fileset'] && !$this->notempty($this->post['fileset_value'])) $this->addError(t('Field "%s" is invalid', t('Assign to fileset')));	
	}

	public function css() {
		$str = new Strings($this->app);
		if ($this->post['css'] && !$this->notempty($this->post['css_value'])) $this->addError(t('Field "%s" is invalid', t('CSS')));	
	}

	public function options() {		
		if ($this->post['option_dynamic'] == 'manual') {
			$options = $this->post['options_name'];
			if (is_array($options) && count($options)) {	
				foreach ($options as $option) {
					if ($this->notempty($option)) {
						return true;
					}
				}
			}
			$this->addError(t('Field "%s" are invalid', t('Options')));
		}
		elseif ($this->post['option_dynamic'] == 'pages') {
			// Nothing to do?!
			return true;
		}
		elseif ($this->post['option_dynamic'] == 'members') {
			// Nothing to do?!
			return true;
		}
	}

	public function other() {	
		if ($this->post['option_other']) {
			if (!$this->notempty($this->post['option_other_value'])) $this->addError(t('Field "%s" invalid', t('Other option (value)')));
			if (!$this->notempty($this->post['option_other_type'])) $this->addError(t('Field "%s" invalid', t('Other option (type)')));	
		}
	}

	public function format() {	
		if (!$this->notempty($this->post['format'])) $this->addError(t('Field "%s" invalid', t('Format')));		
		elseif ($this->post['format'] == 'other' && !$this->notempty($this->post['format_other'])) $this->addError(t('Field "%s" invalid', t('Format (other)')));	
	}

	public function country() {	
		if (intval($this->post['enable_custom_countries']) == 1) {
			if (!is_array($this->post['custom_countries']) || !count($this->post['custom_countries'])) $this->addError(t('Field "%s" invalid', t('Available countries')));	
		}
	}

	public function allowedExtensions() {
		if ($this->post['allowed_extensions']) {
			$str = new Strings($this->app);
			$extensions = $this->post['allowed_extensions_value'];			
			if (!$str->notempty($extensions) || !$str->min($extensions, 2)) $this->addError(t('Field "%s" is invalid', t('Allowed extensions')));
			else {
                $allowed_extensions = explode(";", strtolower(str_replace(array("*","."," "), "", Config::get('concrete.upload.extensions'))));
				$extensions = explode(",", strtolower(str_replace(array("*","."," "), "", $extensions)));
				$difference = array_diff($extensions, $allowed_extensions);
				if (!empty($difference)) $this->addError(t('Extensions "%s" in "%s" aren\'t allowed globally (check Allowed File Types)', @implode(', ', $difference), t('Allowed extensions')));
			}
		}		
	}	

	// Validation for mailings
	public function subject() {
		if (!$this->notempty($this->post['subject'])) $this->addError(t('Field "%s" is invalid', t('Subject')));	
	}

	public function message() {
		if (!$this->notempty($this->post['message'])) $this->addError(t('Field "%s" is invalid', t('Message')));	
	}
	
	public function from() {
		if ($this->post['from_type'] != 'other') {
			if (!$this->notempty($this->post['from_type'])) $this->addError(t('Field "%s" is invalid', t('From')));
		}
		else {
			$str = new Strings($this->app);
			if (!$this->notempty($this->post['from_name']))	$this->addError(t('Field "%s" is invalid', t('From (Name)')));			
			if (!$str->email($this->post['from_email'])) $this->addError(t('Field "%s" is invalid', t('From (Email Address)')));
			
			if ($this->post['reply_type'] != 'other') {
				if (!$str->notempty($this->post['reply_type'])) $this->addError(t('Field "%s" is invalid', t('Reply To')));
			}
			else {
				if (!$this->notempty($this->post['reply_name'])) $this->addError(t('Field "%s" is invalid', t('Reply To (Name)')));			
				if (!$str->email($this->post['reply_email'])) $this->addError(t('Field "%s" is invalid', t('Reply To (Email Address)')));
			}			
		}	
	}	

	public function sendTo() {    
		if (intval($this->post['send_custom']) != 1) {
			$send = $this->post['send'];
			if (!is_array($send)) $send = array_filter(@explode(',', $send));
			if (!is_array($send) || count($send) <= 0) $this->addError(t('Field "%s" isn\'t selected', t('Send to')));
		}
		else {
			$emails = $this->post['send_custom_value'];
			if (!is_array($emails)) $emails = array_filter(@explode(',', $emails));
			if (!is_array($emails) || count($emails) <= 0) $this->addError(t('Field "%s" is invalid', t('Send to (custom)')));  
			else {
				$str = new Strings($this->app);
				foreach ($emails as $email) {
					if (!$str->email(trim($email))) $this->addError(t('Field "%s" is invalid', t('Send to (custom)')));
					break;
				}
			}
		}
	} 
}	
