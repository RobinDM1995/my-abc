<?php      
namespace Concrete\Package\FormidableFull\Src\Formidable\Element;

use \Concrete\Package\FormidableFull\Src\Formidable\Element;
use Core;

class Signature extends Element {
	
	public $element_text = 'Signature';
	public $element_type = 'signature';
	public $element_group = 'Special Elements';	
		
	public $properties = array(
		'label' => true,
		'label_hide' => true,
	    'required' => true,
	    'tooltip' => true,
	    'advanced' => array(
			'value' => '\'color\': \'#F00\', \'background-color\': \'#EEE\'',
			'note' => ''
		),
		'handling' => false,
		'css' => true,
		'errors' => array(
			'empty' => true
		)
	);
	
	public $dependency = array(
		'has_value_change' => false,
		'has_placeholder_change' => false
	);
	
	public function __construct($elementID = 0) {
		$this->properties['advanced']['note'] = array(
			t('Manage some advanced options for jSignature'),
			t('Comma seperate options'),
			t('Example: "\'color\': \'#F00\', \'background-color\': \'#EEE\'"'),
			t('More information: ').'<a href="https://willowsystems.github.io/jSignature" target="_blank">'.t('click here').'</a>'
		);	
	}
	
	public function generateInput() {	

		$options = "";
		if ($this->getPropertyValue('advanced') == 1) $options = preg_replace('/"/', '\'', Core::make('helper/text')->decodeEntities($this->getPropertyValue('advanced_value')));

		$input  = '<div class="signature-holder '.$this->getAttribute('class').'" name="'.$this->getHandle().'" id="'.$this->getHandle().'" data-options="'.$options.'">';
		$input .= '<div class="overlay"></div>';
		$input .= '<div class="signature"></div>';
		$input .= '<div class="help-block">';
		if ($this->getPropertyValue('tooltip') == 1) $input .= '<span>'.$this->getPropertyValue('tooltip_value').'</span>';
		$input .= '<a class="btn btn-default pull-right btn-sm" data-signature="clear">'.t('Clear').'</a>';
		$input .= '</div>';
		$input .= Core::make('helper/form')->textarea($this->getHandle(), $this->getValue(), array('style' => 'display:none'));
		$input .= '</div>'; 

		$this->setAttribute('input', $input);
	}

	public function getDisplayValue($seperator = '', $urlify = true) {
		$value = $this->getValue();
		if (is_array($value)) $value = @implode('', $value); 		
		if (!empty($value)) return t('Signed');
		return t('Unsigned');
	}

	public function getDisplayResult() {
		$value = $this->getValue();
		if (is_array($value)) $value = @implode('', $value); 		
		if (!empty($value)) return '<a href="#" onclick="downloadImage(this)" data-title="'.$this->getLabel().'.png"><img src="'.$value.'" width="350"></a>';
		return '';
	}
}