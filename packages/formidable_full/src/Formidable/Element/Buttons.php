<?php         
namespace Concrete\Package\FormidableFull\Src\Formidable\Element;

use \Concrete\Package\FormidableFull\Src\Formidable\Element;
use Core;

class Buttons extends Element {
	
	public $element_text = 'Buttons';
	public $element_type = 'buttons';
	public $element_group = 'Layout Elements';	
	
	public $is_layout = true; // Is layout element, so change the view.... 
	
	public $properties = array(
		'label' => true,
		'label_hide' => true,
		'handling' => false,
		'css' => true,
	);
	
	public $dependency = array(
		'has_value_change' => false
	);
	
	public function generateInput() {				
		$attribs = $this->getAttributes();
		if (!$this->getPropertyValue('css')) $attribs['class'] = 'btn btn-success'; 
		$html  = '<input type="submit" class="submit '.$attribs['class'].'" id="submit" name="'.$this->getHandle().'" value="'.Core::make('helper/text')->specialchars($this->getLabel()).'">';
        $html .= '<div class="please_wait_loader"><img src="'.BASE_URL.'/packages/formidable_full/images/loader.gif" alt="'.t('Please wait...').'"></div>';
        $this->setAttribute('input', $html);

	}
}