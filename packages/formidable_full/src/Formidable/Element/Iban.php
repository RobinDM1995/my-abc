<?php 
namespace Concrete\Package\FormidableFull\Src\Formidable\Element;

use \Concrete\Package\FormidableFull\Src\Formidable\Element;
use \Concrete\Package\FormidableFull\Src\Formidable\Validator\Result as ValidatorResult;
use Core;

class Iban extends Element {
	
	public $element_text = 'IBAN';
	public $element_type = 'iban';
	public $element_group = 'Special Elements';	
	
	public $properties = array(
		'label' => true,
		'label_hide' => true,
		'default' => true,
		'placeholder' => true,
		'required' => true,					
		'tooltip' => true,
		'handling' => true,
		'css' => true,
		'errors' => array(
			'empty' => true,
			'iban' => true,
		)
	);
	
	public $dependency = array(
		'has_value_change' => true,
		'has_placeholder_change' => true
	);
	
	public function generateInput() {				
		$input  = Core::make('helper/form')->url($this->getHandle(), $this->getValue(), $this->getAttributes());		
		$this->setAttribute('input', $input);		
	}

	public function validateResult() {
		$val = new ValidatorResult();
		$val->setElement($this);
		$val->setData($this->post());
		if (strlen($this->post($this->getHandle())) != 0) $val->iban();
		if ($this->getPropertyValue('required')) $val->required();
		return $val->getList();	
	}		
}