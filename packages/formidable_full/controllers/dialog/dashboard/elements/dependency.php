<?php 
namespace Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Elements;

use \Concrete\Package\FormidableFull\Src\Formidable\Form;
use \Concrete\Package\FormidableFull\Src\Formidable\Element;
use \Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\BackendInterfaceController;
use Core;
use \Concrete\Core\Page\Page;
use Permissions;

class Dependency extends BackendInterfaceController {
	
	protected $viewPath = '/dialogs/elements/dependency';
	protected $token = 'formidable_dependency';
	protected $element = '';

	public function __construct() {
		parent::__construct();
		$el = Element::getByID($this->get('elementID'));	
		if (is_object($el)) {
			$this->element = $el;
			$this->set('current_element', $this->element);
		}
	}

	public function add() {		
		$r = $this->validateAction();
		if ($r === true) {
			$rule = intval($this->get('rule'));			
			$el = $this->element;
			if (is_object($el)) {
				$dependency = $el->getDependencyRule($rule);
				if (!$dependency) $dependency = array();
				$this->set('dependency', $dependency);
				$this->set('rule', $rule);		
			}
		}
	}

	public function action($dependency_rule = '', $rule = '') {
		$r = $this->validateAction();
		if ($r === true) {
			$el = $this->element;
			if (is_object($el)) {
				if (empty($dependency_rule)) $dependency_rule = intval($this->get('dependency_rule'));
				if (empty($rule)) $rule = intval($this->get('rule'));

				$dependency = $el->getDependencyRule($dependency_rule);
				if (!$dependency) $dependency = array();
				
				$dependency_action = !empty($dependency['actions'][$rule])?$dependency['actions'][$rule]:array();

				$actions = array(
					'' => t('Select behaviour'),
					'show' => t('Show'),
					'enable' => t('Enable'),
					'class' => t('Toggle classname')
				);
								  			
				if ($el->getDependencyProperty('has_placeholder_change') === true) $actions['placeholder'] = t('Change placeholder to');				
				if ($el->getDependencyProperty('has_value_change') === true) $actions['value'] = t('Change value to');

				$values = array();
				$options = array();
				if ($el->getProperty('options')) {
					$options_dynamic = $el->getPropertyValue('options_dynamic');
					$options_value = $el->getPropertyValue('options_dynamic_value');
					if ($options_dynamic == 'manual') {
						$options = $el->getPropertyValue('options');	
					}	
					elseif ($options_dynamic == 'pages') {
						$options = $el->getPagesForSelection($options_value['pages']);
					}
					elseif ($options_dynamic == 'members') {
						$options = $el->getUsersForSelection($options_value['members']);
					}
				}				
				
				if (is_array($options) && count($options)) {		
					for ($i=0; $i<count($options); $i++) {
						if ($options_dynamic == 'manual') {
							if (empty($options[$i]['value'])) $options[$i]['value'] = $options[$i]['name'];
							$values[html_entity_decode($options[$i]['value'])] = $options[$i]['name'];
						}
						elseif ($options_dynamic == 'pages') {
							if (is_object($options[$i])) $values[$options[$i]->getCollectionID()] = $options[$i]->getCollectionName();		
						}
						elseif ($options_dynamic == 'members') {
							if (is_object($options[$i])) $values[$options[$i]->getUserID()] = $options[$i]->select_name;	
						}
					}
				}
				$this->set('rule', $dependency_rule);
				$this->set('action_rule', $rule);

				$this->set('action', array(
						'dependency_action' => $dependency_action, 
						'actions' => $actions, 
						'values' => $values
					)
				);
			}
		}
	}

	public function element($dependency_rule = '', $rule = '') {
		$r = $this->validateAction();
		if ($r === true) {
			$el = $this->element;
			if (is_object($el)) {
				if (empty($dependency_rule)) $dependency_rule = intval($this->get('dependency_rule'));
				if (empty($rule)) $rule = intval($this->get('rule'));

				$dependency = $el->getDependencyRule($dependency_rule);
				if (!$dependency) $dependency = array();
				
				$dependency_element = !empty($dependency['elements'][$rule])?$dependency['elements'][$rule]:array();
	
				$conditions = array(
					'enabled' => t('is enabled'),
					'disabled' => t('is disabled'),
					'empty' => t('is empty'),
					'not_empty' => t('is not empty')
				);			
				$els = array(
					'' => t('Select an element')
				);

				$f = Form::getByID($el->getFormID());
				if (!is_object($f))	return false;

				$elements = $f->getElements();
				if (is_array($elements) && count($elements)) {
					foreach($elements as $element) {				
						if ($element->isLayout() || $element->getElementID() == $el->getElementID()) continue;						
						$els[$element->getElementID()] = $element->getLabel();	
						if ($element->getElementType() == 'gdpr') {
							$conditions = array(
								'empty' => t('isn\'t accepted'),
								'not_empty' => t('is accepted'),
							);	
							continue;	
						}									
						if ($element->getElementID() == $dependency_element['element']) {
							$options = array();
							if ($element->getProperty('options')) {
								$options_dynamic = $element->getPropertyValue('options_dynamic');
								$options_value = $element->getPropertyValue('options_dynamic_value');
								if ($options_dynamic == 'manual') {
									$options = $element->getPropertyValue('options');	
								}	
								elseif ($options_dynamic == 'pages') {
									$options = $element->getPagesForSelection($options_value['pages']);
								}
								elseif ($options_dynamic == 'members') {
									$options = $element->getUsersForSelection($options_value['members']);
								}
							}
							if (is_array($options) && count($options)) {						
								// unset empty conditions
								unset($conditions['empty'], $conditions['not_empty']);
								$element_values['any_value'] = t('any value');
								$element_values['no_value'] = t('no value');
								for ($i=0; $i<count($options); $i++) {
									if ($options_dynamic == 'manual') {
										if (empty($options[$i]['value'])) $options[$i]['value'] = $options[$i]['name'];
										$element_values[html_entity_decode($options[$i]['value'])] = $options[$i]['name'];
									}
									elseif ($options_dynamic == 'pages') {
										if (is_object($options[$i])) $element_values[$options[$i]->getCollectionID()] = $options[$i]->getCollectionName();		
									}
									elseif ($options_dynamic == 'members') {
										if (is_object($options[$i])) $element_values[$options[$i]->getUserID()] = $options[$i]->select_name;	
									}
								}
							} else {
								$conditions = array_merge($conditions, array(
									'equals' => t('equals'),
								   	'not_equals' => t('not equal to'),
								   	'contains' => t('contains'),
								   	'not_contains' => t('does not contain')
								));
							}	
							/*if ($element->getElementType() == 'integer') {
								$conditions = array_merge($conditions, array(
									'less' => t('less than'),
									'more' => t('more than'),
									'less_equals' => t('less than or equals'),
									'more_equals' => t('less than or equals'),
									'between' => t('between')
								));	
							}*/		
						}	
							
					}
				}
				$this->set('rule', $dependency_rule);
				$this->set('element_rule', $rule);			
				$this->set('element', array(
						'dependency_element' => $dependency_element, 
						'elements' => $els,
						'conditions' => $conditions,
						'values' => $element_values,
					)
				);
			}
		}
	}

	public function delete() {		
		$r = $this->validateAction();
		if ($r === true) {					
			$el = $this->element;
			if (is_object($el)) {
				$dependency_rule = intval($this->get('rule'));	
				$dependency = is_array($el->getDependencyRule($dependency_rule))?true:false;
				$this->set('dependency', $dependency);
				$this->set('rule', $dependency_rule);		
			}
		}
	}
}
