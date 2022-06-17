<?php      
namespace Concrete\Package\FormidableFull\Src\Formidable\Element;

use \Concrete\Package\FormidableFull\Src\Formidable\Element;
use Core;

class Text extends Element {
	
	public $element_text = 'Text Field';
	public $element_type = 'text';
	public $element_group = 'Basic Elements';	
		
	public $properties = array(
		'label' => true,
		'label_hide' => true,
	    'default' => true,
		'placeholder' => true,
	    'mask' => '',
	    'required' => true,
	    'min_max' => '',
	    'tooltip' => true,
		'handling' => true,
		'css' => true,
		'errors' => array(
			'empty' => true,
		)
	);
	
	public $dependency = array(
		'has_value_change' => true,
		'has_placeholder_change' => true
	);
	
	public function __construct($elementID = 0) {		
		$this->properties['mask'] = array(
			'note' => array(
				'0 - '.t('Represents a numeric character').'(0-9)',
			    '9 - '.t('Represents a optional numeric character').'(0-9)',
			    '# - '.t('Recursive item, only the previous pattern is allowed after the hashtag'),
			    'A - '.t('Represents an alphanumeric character').'(A-Z,a-z,0-9)',
			    'S - '.t('Represents an alpha character').'(A-Z,a-z)',
				t('Examples:'),
				t('Phone').': (999) 999-9999',
				t('Product Code').': SA-999-a999',
				t('More information about masking: <a href="%s" target="_blank">click here</a>', 'https://igorescobar.github.io/jQuery-Mask-Plugin')
			)
		);
		
		$this->properties['min_max'] = array(
			'words' => t('Words'), 
			'chars' => t('Characters')
		);
	}
	
	public function generateInput() {				
		$this->setAttribute('input', Core::make('helper/form')->text($this->getHandle(), $this->getValue(), $this->getAttributes()));
		if (!empty($this->getPropertyValue('mask'))) {
			$placeholder = str_replace(array('0', '9', '#', 'A', 'S'), '_', $this->getPropertyValue('mask_format'));
			if (!empty($this->getPropertyValue('placeholder'))) $placeholder = $this->getPropertyValue('placeholder_value');
			$this->addJavascript("if ($.fn.mask) { $('#".$this->getHandle()."').mask('".$this->getPropertyValue('mask_format')."', {placeholder: '".$placeholder."'}) }");
		}
	}
}