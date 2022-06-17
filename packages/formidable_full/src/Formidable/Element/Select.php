<?php      
namespace Concrete\Package\FormidableFull\Src\Formidable\Element;

use \Concrete\Package\FormidableFull\Src\Formidable\Element;
use \Concrete\Package\FormidableFull\Src\Formidable\Validator\Property as ValidatorProperty;
use Core;
use \Concrete\Core\User\UserInfo;

class Select extends Element {
	
	public $element_text = 'Selectbox';
	public $element_type = 'select';
	public $element_group = 'Basic Elements';	
	
	public $properties = array(
		'label' => true,
		'label_hide' => true,
		'required' => true,
		'options' => true,
		'default' => true,
		'placeholder' => array(
			'note' => array(
				'First choice in the selectbox. Leave empty for an empty option.'
			)
		),
		'option_other' => '',
		'appearance' => '',
		'min_max' => '',
		'tooltip' => true,
		'multiple' => true,
		'handling' => true,
		'css' => true,
		'errors' => array(
			'empty' => true
		)
	);
	
	public $dependency = array(
		'has_value_change' => true,
		'has_placeholder_change' => false
	);
	
	public function __construct() {		

		$this->properties['min_max'] = array(
			'options' => t('Selected options')
		);
			
		$this->properties['option_other'] = array(
			'text' => t('Single text'),
			'textarea' => t('Textarea')
		);

		$this->properties['appearance'] = array(
			'select' => t('Selectbox'),			
			'tags' => t('Tags (selectize)'),
		);
	}
	
	public function generateInput() {

		$form = Core::make('helper/form');

		$value = $this->getValue();

		$attribs = $this->getAttributes();

		if ($this->getPropertyValue('multiple')) $attribs['multiple'] = 'multiple';
		if (strpos($attribs['class'], 'form-control') === false) $attribs['class'] = $attribs['class'].' form-control';

		$aks = @implode(' ', array_map( function ($v, $k) { return sprintf("%s='%s'", $k, $v); }, $attribs, array_keys($attribs)));						

		$select[] = '<select name="'.$this->getHandle().'[]" id="'.$this->getHandle().'" '.$aks.'>';
		if ($this->getPropertyValue('placeholder')) $select[] = '<option value="">'.h($this->getPropertyValue('placeholder_value')).'</option>';

		if ($this->getPropertyValue('options_dynamic') == 'manual') {
			$options = $this->getPropertyValue('options');	
			if (!empty($options) && is_array($options) && count($options)) {
				foreach ($options as $i => $o) {						
					if (empty($options[$i]['value'])) $options[$i]['value'] = $options[$i]['name'];						
					$selected = (@in_array($options[$i]['value'], (array)$value) || (empty($value) && $options[$i]['selected'] === true))?'selected="selected"':'';								
					$select[]= '<option value="'.$options[$i]['value'].'" '.$selected.'>'.$options[$i]['name'].'</option>';
				}
			}
		}
		elseif ($this->getPropertyValue('options_dynamic') == 'pages') {
			$options_value = $this->getPropertyValue('options_dynamic_value');
			if (count($options_value)) {
				$pages = $this->getPagesForSelection($options_value['pages']);
				if (count($pages)) {
					foreach ($pages as $i => $p) {
						$selected = (@in_array($p->getCollectionID(), (array)$value))?'selected="selected"':'';
						$select[]= '<option value="'.$p->getCollectionID().'" '.$selected.'>'.$p->getCollectionName().'</option>';
					}
				}
			}
		}
		elseif ($this->getPropertyValue('options_dynamic') == 'members') {
			$options_value = $this->getPropertyValue('options_dynamic_value');
			if (count($options_value)) {
				$users = $this->getUsersForSelection($options_value['members']);
				if (count($users)) {
					foreach ($users as $i => $u) {
						$selected = (@in_array($u->getUserID(), (array)$value))?'selected="selected"':'';
						$select[]= '<option value="'.$u->getUserID().'" '.$selected.'>'.$u->select_name().'</option>';
					}
				}
			}
		}

		if ($this->getPropertyValue('option_other')) {
			$selected = (is_array($value) && count($value) && @in_array('option_other', $value))?'selected="selected"':'';			
			$select[] .= '<option value="option_other" '.$selected.'>'.$this->getPropertyValue('option_other_value').'</option>';			
			$this->setAttribute('other', $form->{$this->getPropertyValue('option_other_type')}($this->getHandle().'_other', $this->getDisplayOtherValue(), $this->getAttributes()));
		}		
		$select[] = '</select>';		
		$this->setAttribute('input', @implode(PHP_EOL, $select));

		if ($this->getPropertyValue('appearance') == 'tags') {

			$max = 20;
			if ($this->getPropertyValue('min_max')) $max = $this->getPropertyValue('max_value');	

			$script .= '
				if ($.fn.selectize) { 
					$(\'#'.$this->getHandle().'\').selectize({
		                plugins: [\'remove_button\'],
						valueField: \'id\',
						labelField: \'text\',
						openOnFocus: false,
						create: false,
						createFilter: function(input) {
							return input.length >= 1;
						},	
						onChange: function(value) {
							var max = '.$max.';
							var current = 0;
							if (value !== null) current = value.length;							
							$(\'#'.$this->getHandle().'\').closest(\'.element\').find(\'.counter span\').text(max - current);
						},		
						delimiter: \',\',
						maxItems: '.$max.','.
						($this->getPropertyValue('placeholder')?'placeholder: \''.$this->getPropertyValue('placeholder_value').'\',':'').'
					});
				}';
			$this->addJavascript($script);
		}
	}

	public function getDisplayValue($seperator = ' ', $urlify = true) {
		$value = $this->getValue();	
		
		$_value = array();
		if ($this->getPropertyValue('options_dynamic') == 'manual') {
			$options = $this->getPropertyValue('options');	
			if (!empty($options) && is_array($options) && count($options)) {
				foreach ($options as $i => $o) {						
					if (empty($options[$i]['value'])) $options[$i]['value'] = html_entity_decode($options[$i]['name']);						
					if (@in_array($options[$i]['value'], (array)$value)) $_value[] = $options[$i]['name'];				
				}
			}
		}
		elseif ($this->getPropertyValue('options_dynamic') == 'pages') {
			if (count($value)) {
				foreach ($value as $v) {
					$p = Page::getByID($v);
					if (is_object($p)) $_value[] = $p->getCollectionName();			
					else $_value[] = t('Unknown page');
				}
			}			
		}
		elseif ($this->getPropertyValue('options_dynamic') == 'members') {
			if (count($value)) {
				$options_value = $this->getPropertyValue('options_dynamic_value');
				foreach ($value as $v) {					
					$user = UserInfo::getByID($v);
					if (is_object($user)) $_value[] = $this->getMemberValueByHandle($user, $options_value['members']['name']);	
					else $_value[] = t('Unknown member');
				}
			}
		}

		// Check if there is an other value
		if (is_array($value) && @in_array('option_other', $value)) {
			$other = array_pop($value); 
			if (!empty($other)) array_push($_value, $this->getPropertyValue('option_other_value').' '.$this->getDisplayOtherValue());
		}	

		if (is_array($_value)) $_value = @implode($seperator, $_value); 		
		if (!$urlify) return h($_value);
		return html_entity_decode($_value);
	}

	public function getDisplayValueExport($seperator = ' ', $urlify = true) {
		return htmlentities($this->getDisplayValue($seperator, $urlify));
	}

	// Use your own validation beacause placeholder is normally required
	// The selectbox don't need this to be required.
	public function validateProperty() {
		$val = new ValidatorProperty();
		$val->setData($this->post());
		if ($this->getProperty('label')) $val->label();
		if ($this->getProperty('min_max')) $val->minMax();
		if ($this->getProperty('tooltip')) $val->tooltip();		
		if ($this->getProperty('options')) $val->options();
		if ($this->getProperty('option_other')) $val->other();
		if ($this->getProperty('appearance')) $val->appearance();		
		if ($this->getProperty('css')) $val->css();
		if ($this->getProperty('submission_update')) $val->submissionUpdate();
		return $val->getList();	
	}	
}