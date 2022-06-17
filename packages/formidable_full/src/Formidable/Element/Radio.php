<?php      
namespace Concrete\Package\FormidableFull\Src\Formidable\Element;

use \Concrete\Package\FormidableFull\Src\Formidable\Element;
use Core;
use \Concrete\Core\User\UserInfo;

class Radio extends Element {
	
	public $element_text = 'Radiobuttons';
	public $element_type = 'radio';
	public $element_group = 'Basic Elements';
	
	protected $format = '<div class="radio {SIZE}"><label for="{ID}">{ELEMENT} {TITLE}</label></div>';
	
	public $properties = array(
		'label' => true,
		'label_hide' => true,
		'required' => true,
		'options' => true,
		'option_other' => '',
		'appearance' => '',
		'tooltip' => true,
		'handling' => true,
		'css' => true,
		'errors' => array(
			'empty' => true
		)
	);
		
	public $dependency = array(
		'has_value_change' => true
	);
		
	public function __construct() {				
			
		$this->properties['option_other'] = array(
			'text' => t('Single text'),
			'textarea' => t('Textarea')
		);
		
		$this->properties['appearance'] = array(
			'w100' => t('One column'),			
			'w50' => t('Two columns'),
			'w33' => t('Three columns'),
			'w25' => t('Four columns'),
			'w20' => t('Five columns'),
			'auto' => t('Automatically (let the width decide)')
		);
	}	
	
	public function generateInput() {
		$form = Core::make('helper/form');
		$th = Core::make('helper/text');
		
		$attribs = $this->getAttributes();
		if (strpos($attribs['class'], 'counter_disabled') === false) $attribs['class'] = $attribs['class'].' counter_disabled';	

		$aks = @implode(' ', array_map( function ($v, $k) { return sprintf("%s='%s'", $k, $v); }, $attribs, array_keys($attribs)));
		
		$value = $this->getValue();
		
		if ($this->getPropertyValue('options_dynamic') == 'manual') {
			$options = $this->getPropertyValue('options');	
			if (!empty($options) && is_array($options) && count($options)) {
				foreach ($options as $i => $o) {						
					$id = $th->sanitizeFileSystem($this->getHandle()).($i+1);
					if (empty($options[$i]['value'])) $options[$i]['value'] = $options[$i]['name'];	
					$checked = (@in_array($options[$i]['value'], (array)$value) || (empty($value) && $options[$i]['selected'] === true))?'checked="checked"':'';
					$radio = '<input type="radio" name="'.$this->getHandle().'[]" id="'.$id.'" value="'.$options[$i]['value'].'" '.$checked.' '.$aks.'>';
					$input[] = str_replace(array('{ID}','{TITLE}','{ELEMENT}','{SIZE}'), array($id, $options[$i]['name'], $radio, $this->getPropertyValue('appearance')), $this->format);
				}
			}
		}
		elseif ($this->getPropertyValue('options_dynamic') == 'pages') {
			$options_value = $this->getPropertyValue('options_dynamic_value');
			if (count($options_value)) {
				$pages = $this->getPagesForSelection($options_value['pages']);
				if (count($pages)) {
					foreach ($pages as $i => $p) {
						$id = $th->sanitizeFileSystem($this->getHandle()).($i+1);
						$checked = (@in_array($p->getCollectionID(), (array)$value))?'checked="checked"':'';
						$radio = '<input type="radio" name="'.$this->getHandle().'[]" id="'.$id.'" value="'.$p->getCollectionID().'" '.$checked.' '.$aks.'>';
						$input[] = str_replace(array('{ID}','{TITLE}','{ELEMENT}','{SIZE}'), array($id, $p->getCollectionName(), $radio, $this->getPropertyValue('appearance')), $this->format);
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
						$id = $th->sanitizeFileSystem($this->getHandle()).($i+1);						
						$checked = (@in_array($u->getUserID(), (array)$value))?'checked="checked"':'';
						$radio = '<input type="radio" name="'.$this->getHandle().'[]" id="'.$id.'" value="'.$u->getUserID().'" '.$checked.' '.$aks.'>';
						$input[] = str_replace(array('{ID}','{TITLE}','{ELEMENT}','{SIZE}'), array($id, $u->select_name, $radio, $this->getPropertyValue('appearance')), $this->format);
					}
				}
			}
		}

		if ($this->getPropertyValue('option_other') != 0) {
			$checked = (is_array($value) && count($value) && @in_array('option_other', $value))?'checked="checked"':'';			
			$id = $th->sanitizeFileSystem($this->getHandle()).'_other';
			$radio = '<input type="radio" name="'.$this->getHandle().'[]" id="'.$id.'" value="option_other" '.$checked.' '.$aks.'>';	
			$input[] = str_replace(array('{ID}','{TITLE}','{ELEMENT}','{SIZE}'), array($id, $this->getPropertyValue('option_other_value'), $radio, $this->getPropertyValue('appearance')), $this->format);	
			$this->setAttribute('other', $form->{$this->getPropertyValue('option_other_type')}($this->getHandle().'_other', $this->getDisplayOtherValue(), $this->getAttributes()));
		}	
		$this->setAttribute('input', '<div>'.@implode(PHP_EOL, $input).'</div>');
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
			if (!empty($value)) {
				$p = Page::getByID($value);
				if (is_object($p)) $_value[] = $p->getCollectionName();			
				else $_value[] = t('Unknown page');
			}			
		}
		elseif ($this->getPropertyValue('options_dynamic') == 'members') {
			if (!empty($value)) {
				$options_value = $this->getPropertyValue('options_dynamic_value');
				$user = UserInfo::getByID($value);
				if (is_object($user)) $_value[] = $this->getMemberValueByHandle($user, $options_value['members']['name']);	
				else $_value[] = t('Unknown member');
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
		return $this->getDisplayValue($seperator, $urlify);
	}
}