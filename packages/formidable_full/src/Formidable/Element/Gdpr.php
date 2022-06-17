<?php      
namespace Concrete\Package\FormidableFull\Src\Formidable\Element;

use \Concrete\Package\FormidableFull\Src\Formidable\Element;
use Core;
use \Concrete\Core\Support\Facade\Config;

class Gdpr extends Element {
	
	public $element_text = 'GDPR Agree';
	public $element_type = 'gdpr';
	public $element_group = 'Special Elements';	
	
	protected $format = '<div class="checkbox"><label for="{ID}">{ELEMENT} {TITLE}</label></div>';

	public $properties = array(
		'label' => true,
		'label_hide' => true,
	    'required' => true,	    
	    'tooltip' => true,
		'handling' => true,
		'css' => true,
		'html' => true,
		'errors' => array(
			'empty' => true,
		)
	);
	
	public $dependency = array(
		'has_value_change' => true,
		'has_placeholder_change' => true
	);
	
	public function __construct($elementID = 0) {
		if (empty($this->getPropertyValue('html_value'))) {
			$this->setPropertyValue('html_value', t('<p>This form collects you personal data so that we can handle your request. %s needs your consent to collect your personal data. Checkout our <a href="javascript:;">privacy policy</a> for more information on how we protect and manage your submitted data. Please give us your consent to use your personal data.</p>', Config::get('concrete.site')));
		}
	}
	
	public function generateInput() {	
		$th = Core::make('helper/text');
		$id = $th->sanitizeFileSystem($this->getHandle());
		$checked = intval($this->getValue())==1?'checked="checked"':'';
		$checkbox = '<input type="checkbox" name="'.$this->getHandle().'" id="'.$id.'" value="1" '.$checked.'>';
		$input = str_replace(array('{ID}','{TITLE}','{ELEMENT}'), array($id, Core::make('helper/text')->decodeEntities($this->getPropertyValue('html_value')), $checkbox), $this->format);
		$this->setAttribute('input', $input);	
	}

	public function getDisplayValue($seperator = ' ', $urlify = true) {
		$value = $this->getValue();								
		return intval($value)==1?t('Accepted'):'';
	}
	public function getDisplayValueExport($seperator = ' ', $urlify = true) {
		return $this->getDisplayValue($seperator, $urlify);
	}
}