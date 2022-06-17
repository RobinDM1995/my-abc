<?php      
namespace Concrete\Package\FormidableFull\Src\Formidable\Element;

use \Concrete\Package\FormidableFull\Src\Formidable\Element;
use \Concrete\Package\FormidableFull\Src\Formidable\Validator\Result as ValidatorResult;
use Core;

class Captcha extends Element {
	
	public $element_text = 'Captcha';
	public $element_type = 'captcha';
	public $element_group = 'Special Elements';		
	
	public $properties = array(
		'label' => true,
		'label_hide' => true,
		'required' => true,					
		'tooltip' => true,
		'css' => false,
		'handling' => false,
		'errors' => array(
			'captcha' => true,
		)
	);
	
	public $dependency = array(
		'has_value_change' => false
	);
	
	public function generateInput() {			

		$attribs = $this->getAttributes();
		$aks = @implode(' ', array_map( function ($v, $k) { return sprintf("%s='%s'", $k, $v); }, $attribs, array_keys($attribs)));	

		// If captcha already done, show some other element
		if ($this->check()) {
			$element  = '<div class="captcha_holder '.$aks.'">';
			$element .= '<div id="'.$this->getHandle().'_done" class="captcha_done">'.t('Already verified you are human.').'</div>';
			$element .= '</div>';
		}
		else {
			$captcha = Core::make("captcha");	

			// Stupid hey!	
			ob_start();
			$captcha->display();
			$display = ob_get_clean();

			ob_start();
			$captcha->showInput();
			$input = ob_get_clean();		

			$element  = '<div class="captcha_holder '.$aks.'">';
			$element .= '<div id="'.$this->getHandle().'_image" class="captcha_image">'.$display.'</div>';
			$element .= '<div id="'.$this->getHandle().'" class="captcha_input">'.$input.'</div>';
			$element .= '<div id="'.$this->getHandle().'_done" class="captcha_done">'.t('Already verified you are human.').'</div>';
			$element .= '</div>';
		}

		$this->setAttribute('input', $element);
	}

	public function validateResult() {
		$val = new ValidatorResult();
		$val->setElement($this);
		$val->setData($this->post());
		if ($this->getPropertyValue('required')) {

			// If captcha is in session already...
			if ($this->check()) return false;
			$this->reset();

			$captcha = Core::make("captcha");
			if (!$captcha->check()) $val->addError('ERROR_CAPTCHA');
			else {
				// If captcha is posted, check!
				// After the check remove the captcha from the form.
				// You already proved to be human!
				$session = Core::make('app')->make('session');
				$session->set('captcha'.$this->getFormID(), time() + (60 * 15));
			}
		}
		return $val->getList();	
	}

	private function check() {
		$session = Core::make('app')->make('session');
		$time = $session->get('captcha'.$this->getFormID());
		if ($time && $time >= time()) return true;
		return false;
	}

	public function reset() {
		$session = Core::make('app')->make('session');
		$session->remove('captcha'.$this->getFormID());
	}
}