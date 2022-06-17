<?php    
namespace Concrete\Package\FormidableFull\Src\Formidable;

use \Concrete\Package\FormidableFull\Src\Formidable;
use \Concrete\Package\FormidableFull\Src\Formidable\Validator\Result as ValidatorResult;
use \Concrete\Package\FormidableFull\Src\Formidable\Validator\Property as ValidatorProperty;
use \Concrete\Package\FormidableFull\Src\Formidable\Validator\Dependency as ValidatorDependency;
use \Concrete\Core\Support\Facade\Database;
use Core;
use Request;
use PageList;
use \Concrete\Core\User\UserList;
use \Concrete\Core\User\Group\Group as Group;
use CollectionAttributeKey;
use UserAttributeKey;

class Element extends Formidable {
	
	public $searchable = true;

	public static function getByID($elementID) {
		if (intval($elementID) == 0) return false;
		$db = Database::connection();					
		$element = $db->fetchAssoc("SELECT elementID, element_type FROM FormidableFormElements WHERE elementID = ?", array($elementID));	
		if ($element) {
			$f = new Formidable();
			return $f->loadElement($element['element_type'], $element['elementID']);	
		}	
		return false;
	}

	public function load($elementID) {		
		if (intval($elementID) == 0) return false;		
		$db = Database::connection();					
		$element = $db->fetchAssoc("SELECT * FROM FormidableFormElements WHERE elementID = ?", array($elementID));	
		if (!$element) return false;		

		$this->setAttributes($element);
		
		// Replace params for more readable vars
		$params = array();
		if (!empty($this->params)) {
			$params = unserialize($this->params);
			if (is_array($params) && count($params)) {
				$params['chars_allowed_value'] = @explode(',', $params['chars_allowed_value']);	
				$params['label_hide'] = $this->label_hide==1?true:false;
			}
			$this->setAttribute('property_value', $params);
			unset($this->params);
		}		

		// Set some default attributes
		$attributes = array();
		if ($this->getPropertyValue('placeholder') && $this->getPropertyValue('placeholder_value')) $attributes['placeholder'] = $this->getPropertyValue('placeholder_value');
		if ($this->getPropertyValue('css') && $this->getPropertyValue('css_value')) $attributes['class'] = $this->getPropertyValue('css_value');
		if ($this->getPropertyValue('tooltip') && $this->getPropertyValue('tooltip_value')) {
			$attributes['title'] = $this->getPropertyValue('tooltip_value');
			$attributes['data-toggle'] = 'tooltip';
		};
		if (is_array($attributes) && count($attributes)) $this->setAttribute('attributes', array_filter($attributes));

		// Now initialize dependencies for this element
		//$this->setAttribute('dependencies', false);
		if (empty($this->dependencies)) $this->setAttribute('dependencies', false);
		else {
			$dependencies = unserialize($this->dependencies);
			if (is_array($dependencies) && count($dependencies)) {
				$this->initializeDependency($dependencies);
			}
		}

		return true;
	}

	public static function getByHandle($handle) {
		$db = Database::connection();
		$elementID = $db->fetchColumn("SELECT elementID FROM FormidableFormElements WHERE label_import = ?", array($handle));
		if (intval($elementID) == 0) return false;	
		$item = new Element();
		if ($item->load($elementID)) return $item;
		return false;
	}

	public function getElementID() {
		return is_numeric($this->elementID)?$this->elementID:false;
	}
	public function getLayoutID() {
		return is_numeric($this->layoutID)?$this->layoutID:false;
	}

	public function isLayout() {
		return !empty($this->is_layout)?$this->is_layout:false;
	}

	public function getProperty() {
		$key = func_get_args();
		if (!is_array($key)) return false; 
		if (count($key) == 1) return !empty($this->properties[$key[0]])?$this->properties[$key[0]]:false;
		if (count($key) == 2) return !empty($this->properties[$key[0]][$key[1]])?$this->properties[$key[0]][$key[1]]:false;
		if (count($key) == 3) return !empty($this->properties[$key[0]][$key[1]][$key[2]])?$this->properties[$key[0]][$key[1]][$key[2]]:false;
	}
	public function getPropertyValue($key) {
		return array_key_exists($key, (array)$this->property_value)?$this->property_value[$key]:false;
	}
	public function setPropertyValue($key, $value) {
		$this->property_value[$key] = $value;	
	}

	public function getErrorText($errorCode, $args) {
		if (intval($this->getPropertyValue('errors')) == 0) return false;
		$errors = $this->getPropertyValue('errors_values');
		if (empty($errors)) return false;
		$error = $errors[str_replace('error_', '', strtolower($errorCode))];
		if (empty($error)) return false; 		
		$count = preg_match_all('/%s/', $error, $matches);		
		if (!is_array($args) || $count > count($args)) {
			for ($i=count($args); $i<$count; $i++) $args[] = '%s';
		}
		return vsprintf($error, $args);
	}
	
	public function save($data) {
		$th = Core::make('helper/text');
		// Do some checking before saving
		if (is_array($data) && count($data)) {
			foreach ($data as $key => $value) {
				if (in_array($key, array('dependencies', 'params', 'attributes'))) continue;
				$data[$key] = $th->sanitize($value);
			}
		}
		if (!$this->getElementID()) return $this->add($data);
		else return $this->update($data);	 
	}
	
	private function add($data) {			
		if(!$data['sort']) $data['sort'] = self::getNext(intval($data['formID']));		
		$db = Database::connection();
		$db->insert('FormidableFormElements', $data);
		$elementID = $db->lastInsertId();		
		if (empty($elementID)) return false;

		// Remove current column listing.
		self::clearColumnSet($this->getFormID());
		
		// Now setup a new handle	
		$this->elementID = $elementID;
		return $this->update(array('label' => $data['label']));		
	}
	
	private function update($data)	{					
		$db = Database::connection();	
		$th = Core::make('helper/text');		
		if ($data['label']) $data['label_import'] = $th->sanitizeFileSystem($data['label'].'_'.$this->getElementID());
		$db->update('FormidableFormElements', $data, array('elementID' => $this->getElementID()));		
		$this->load($this->getElementID());		
		return true;
	}
	
	public function duplicate($formID = 0, $layoutID = 0) {
		
		$db = Database::connection();					
		$element = $db->fetchAssoc("SELECT * FROM FormidableFormElements WHERE elementID = ?", array($this->getElementID()));	
		if (!$element) return false;
		
		// Set new params	
		if (intval($formID) != 0) $element['formID'] = $formID;
		if (intval($layoutID) != 0) $element['layoutID'] = $layoutID;
		if (intval($layoutID) == 0 && intval($layoutID) == 0) {
			$th = Core::make('helper/text');	
			$label = t('%s (copy)', $element['label']);
			$element['label'] = $label;
			$element['label_import'] = $th->sanitizeFileSystem($label);
		}		
		// Why do a sort?! It has already a sort?
		//$element['sort'] = self::getNext($element['formID']);

		// Unset current elementID
		unset($element['elementID']);
				
		$ne = new Element();			
		if ($ne->add($element)) return $ne;
		return false;
	}
	
	public function delete() {
		$db = Database::connection();	
		$db->delete('FormidableFormElements', array('elementID' => $this->getElementID(), 'formID' => $this->getFormID()));
		
		// Reorder elements on form
		$this->orderElements();
		
		// Remove current column listing.
		self::clearColumnSet($this->getFormID());					
		return true;
	}
	
	public function validateResult() {
		$val = new ValidatorResult();
		$val->setElement($this);
		$val->setData($this->post());
		if ($this->getPropertyValue('required')) $val->required();
		if ($this->getPropertyValue('min_max')) $val->minMax();
		if ($this->getPropertyValue('option_other')) $val->other();
		if ($this->getPropertyValue('confirmation')) $val->confirmation();
		return $val->getList();	
	}

	public function validateProperty() {
		$val = new ValidatorProperty();
		$val->setData($this->post());
		if ($this->getProperty('label')) $val->label();
		if ($this->getProperty('placeholder')) $val->placeholder();
		if ($this->getProperty('default')) $val->defaultValue();
		if ($this->getProperty('mask')) $val->mask();
		if ($this->getProperty('min_max')) $val->minMax();
		if ($this->getProperty('tooltip')) $val->tooltip();
		if ($this->getProperty('tinymce')) $val->tinymce();
		if ($this->getProperty('html_code')) $val->htmlCode();
		if ($this->getProperty('options')) $val->options();
		if ($this->getProperty('option_other')) $val->other();
		if ($this->getProperty('appearance')) $val->appearance();
		if ($this->getProperty('format')) $val->format();
		if ($this->getProperty('advanced')) $val->advanced();
		if ($this->getProperty('allowed_extensions')) $val->allowedExtensions();
		if ($this->getProperty('fileset')) $val->fileset();
		if ($this->getProperty('css')) $val->css();
		if ($this->getProperty('submission_update')) $val->submissionUpdate();
		return $val->getList();	
	}
	
	public function validateDependency() {
		$val = new ValidatorDependency();
		$val->setElement($this);
		$val->setData($this->post('dependency'));
		$val->validate();			
		return $val->getList();		
	}

	public function getSerializedValue() {
		$value = $this->getValue();
		if (empty($value) || (is_array($value) && !count($value))) return ''; 
		$result['value'] = $value;
		$other = $this->getOtherValue();
		if (!empty($other)) $result['value_other'] = $other;
		return serialize($result);
	}

	public function setValue($value = '', $force = false) {
		
		$this->setAttribute('value', $value);
		if ($force) { 			
			// Weird code to split values and other sh*t...
			if (is_array($value) && array_key_exists('value_other', $value)) $this->setAttribute('other_value', $value['value_other']);
			if (is_array($value) && array_key_exists('value', $value)) $this->setAttribute('value', $value['value']);
			return true;
		}
		// First find a post
		if ($this->post()) {			
			$value = $this->post($this->getHandle());
			// Do some checking before saving
			if (is_array($value) && count($value)) {
				foreach ($value as $key => $v) {
					if (is_string($v) ) $value[$key] = h($v);
					else $value[$key] = array_map(function ($val) { return h($val); }, $v);
				}
			}
			// Now get other value (if there is)
			$other = $this->post($this->getHandle().'_other');
			if (!empty($other)) $this->setAttribute('other_value', h($other));

			$this->setAttribute('value', $value);
			return true;
		}
		
		// Find value based on result....
		$result = $this->getResult();
		if (!empty($result)) {
			$answer = $result->getAnswerByElementID($this->getElementID());
			if (!empty($answer)) {
				// Weird code to split values and other sh*t...
				if (is_array($answer) && array_key_exists('value_other', $answer)) $this->setAttribute('other_value', $answer['value_other']);
				if (is_array($answer) && array_key_exists('value', $answer)) $this->setAttribute('value', $answer['value']);
			}
			return true;
		}

		// If not found, set some default values
		$obj = false;
		$value = '';		
		if ($this->getPropertyValue('default_value_type') == 'value') $value = $this->getPropertyValue('default_value_value');
		if ($this->getPropertyValue('default_value_type') == 'request') $value = Request::request($this->getPropertyValue('default_value_value'));
		if ($this->getPropertyValue('default_value_type') == 'collection_attribute') $obj = $this->getCollection();	
		if ($this->getPropertyValue('default_value_type') == 'user_attribute') $obj = $this->getUser();	
		if (is_object($obj)) {
			if (strpos($this->getPropertyValue('default_value_value'), 'ak_') !== false) {
				$value = $obj->getAttribute(substr($this->getPropertyValue('default_value_value'), 3));
				if (is_object($value)) {
					if (get_class($value) == 'DateTime') $value = $value->format('Y-m-d H:i:s');
					else $value = (string)$value;
				}
			}
			else {
				$th = Core::make('helper/text');
				$class = 'get'.$th->camelcase($this->getPropertyValue('default_value_value'));
				if (method_exists($obj, $class)) $value = $obj->{$class}();	
			}	
		}
		$this->setAttribute('value', $value);
		return true;
	}

	public function getDisplayValue($seperator = ' ', $urlify = true) {
		$value = $this->getValue();	
		
		// Check if there is an other value
		if ($this->getProperty('options') && is_array($value) && @in_array('option_other', $value)) {
			$other = array_pop($value); 
			if (!empty($other)) array_push($value, $this->getPropertyValue('option_other_value').' '.$this->getDisplayOtherValue());
		}	

		if (is_array($value)) $value = @implode($seperator, $value); 		
		if (!$urlify) return h($value);
		return h($value);
	}

	public function getDisplayOtherValue($urlify = true) {
		$value = $this->getOtherValue();
		if (empty($value)) return '';
		if (!$urlify) return h($value);
		return h($value);
		//$lh = new LinkHelper(); 
		//return $lh->url_and_email_ify(h($this->other_value));
	}

	public function getDisplayValueExport($seperator = ' ', $urlify = true) {
		return $this->getDisplayValue($seperator, $urlify);
	}

	public function getDisplayResult() {
		return $this->getDisplayValue();
	}

	public function generateInput() {			
		$this->setAttribute('input', Core::make('helper/form')->text($this->getHandle(), $this->getValue(), $this->getAttributes()));
	}

	public function setFormat($format) {
		$this->format = $format;
		$input = $this->getInput();
		if (!empty($input)) $this->generateInput();
		return true;
	}

	public function getPagesForSelection($args = array()) {

		$list = new PageList();				
		$list->disableAutomaticSorting();
		
		if ($args['page_type']) {
			$list->filterByPageTypeID($args['page_type']);
		}

		if ($args['location'] == 'beneath' && $args['location_page']) {
			$list->filterByParentID($args['location_page']);
		}

		if ($args['featured']) {
			$cak = CollectionAttributeKey::getByHandle('is_featured');
			if (is_object($cak)) {
				$list->filterByIsFeatured(1);
			}
		}

		if ($args['alias']) {
			$list->includeAliases();
		}  

		$cak = CollectionAttributeKey::getByHandle('exclude_from_formidable');
		if (is_object($cak)) {
			$list->filterByAttribute('exclude_from_formidable', 1, '!=');
		}

		switch ($args['order']) {
			case 'display_asc': $list->sortByDisplayOrder(); break;
			case 'display_desc': $list->sortByDisplayOrderDescending(); break;
			case 'chrono_asc': $list->sortByPublicDate(); break;
			case 'modified_desc': $list->sortByDateModifiedDescending(); break;
			case 'random': $list->sortBy('RAND()'); break;
			case 'alpha_asc': $list->sortByName(); break;
			case 'alpha_desc': $list->sortByNameDescending(); break;
			default: $list->sortByPublicDateDescending(); break;
		}

		$pages = $list->getResults();
		if (count($pages)) {
			return $pages;
		}
		return [];
	}

	public function getUsersForSelection($args = array()) {

		$list = new UserList();	
		if (@count($args['group'])) {
			$groups = [];
			foreach ($args['group'] as $group) {
				$groups[] = Group::getByID($group);
			}
			$list->filterByInAnyGroup($groups);
		}

		if ($args['active']) {
			$list->filter(false, 'uIsActive = 1');
		}  

		if ($args['validated']) {
			$list->filter(false, 'uIsValidated = 1');
		}  

		$cak = UserAttributeKey::getByHandle('exclude_from_formidable');
		if (is_object($cak)) {
			$list->filterByAttribute('exclude_from_formidable', 1, '!=');
		}

		$list->sortBy($args['order_by'], $args['order_dir']);

		$users = $list->getResults();
		if (count($users)) {			
			foreach($users as $i => $user) {
				$value = $this->getMemberValueByHandle($user, $args['name']);				
				if ($args['skip_empty'] && empty($value)) {
					unset($users[$i]);
					continue;
				} 
				$users[$i]->select_name = !empty($value)?$value:t('(empty)');
			}
			return $users;
		}
		return [];
	}

	public function getMemberValueByHandle($user, $handle = '') {
		$method = 'getUserName';
		$val = '';
		if (strpos($handle, 'ak_') !== false) {
			$method = 'getAttribute';
			$val = substr($handle, 3);
		}
		else {
			switch ($handle) {				
				case 'u.uName': $method = 'getUserName'; break;
				case 'u.uEmail': $method = 'getUserEmail'; break;
				case 'u.uDateAdded': $method = 'getUserDateAdded'; break;
				case 'uStatus': $method = 'getUserName'; break;
				case 'u.uNumLogins': $method = '‪getUserTotalLogins'; break;
				case 'u.uID': $method = 'getUserID'; break;
				case 'u.uLastLogin': $method = '‪getUserLastLogin'; break;
			}
		}
		return $user->{$method}($val);
	}

	public function updateOnSubmission($cID = 0) {			
		if (!$this->getPropertyValue('submission_update')) return true;
		
		$value = $this->getDisplayValue();
		if (!$this->getPropertyValue('submission_empty') == 1 && empty($value)) return true;
							
		if ($this->getPropertyValue('submission_update_type') == 'user_attribute') $obj = Formidable::getUser();	
		elseif ($this->getPropertyValue('submission_update_type') == 'collection_attribute') $obj = Formidable::getCollection($cID);	
			
		if (is_object($obj)) {			
			if (strpos($this->getPropertyValue('submission_update_value'), 'ak_') !== false) $obj->setAttribute(substr($this->getPropertyValue('submission_update_value'), 3), $value);	
			else {
				switch ($this->getPropertyValue('submission_update_value')) {
					case 'user_name': $obj->update(array('uName' => $value)); break;
					case 'user_email': $obj->update(array('uEmail' => $value)); break;	
					case 'user_password': $obj->update(array('uPassword' => $value, 'uPasswordConfirm' => $value)); break;
					default:
						$th = Core::make('helper/text');
						$class = 'set'.$th->camelcase($this->getPropertyValue('submission_update_value'));
						if (method_exists($obj, $class)) $obj->{$class}($value);
					break;	
				}														
			}
		}
		return true;
	}

	public static function getNext($formID) {			
		return parent::getNextSort('element', $formID);
	}
	
	public function initializeDependency($deps = '') {
		
		if (empty($deps)) {
			$this->setAttribute('dependencies', false);
			return false;
		} 		

		$th = Core::make('helper/text');

		foreach ((array)$deps as $rule => $dep) {									
			$actions = $elements = $etmp = array();
			
			foreach ($dep['actions'] as $a) {
				if ($a['action'] == 'enable') $actions['enable'] = true;			
				if ($a['action'] == 'show') $actions['show'] = true;					
				if ($a['action'] == 'value') $actions['value'] = $a['action_value'].$a['action_select'];				
				if ($a['action'] == 'placeholder') $actions['placeholder'] = $a['action_value'];			
				if ($a['action'] == 'class') $actions['class'] = $a['action_value'];	
			}
			
			foreach ($dep['elements'] as $er => $ea) {

				$e = Element::getByID($ea['element']);
				if (!is_object($e)) continue;
				
				// Elements shouldn't be assigned to itself
				if ($e->getElementID() == $this->getElementID()) continue;
				
				$key = array_search($e->getHandle(), (array)$etmp);
				if ($key !== false) $er = $key;			
				
				$etmp[$er] = $elements[$er]['handle'] = $e->getHandle();
				$elements[$er]['elementID'] = $e->getElementID();
				$elements[$er]['type'] = $e->getElementType();

				// TODO
				// Recipient selector in this list?
				if (in_array($e->getElementType(), array('radio', 'checkbox', 'select'))) {
					
					$options_dynamic = $e->getPropertyValue('options_dynamic');
					$options_value = $e->getPropertyValue('options_dynamic_value');
					if ($options_dynamic == 'manual') {
						$options = @array_filter((array)$e->getPropertyValue('options'));
					}	
					elseif ($options_dynamic == 'pages') {
						$options = $e->getPagesForSelection($options_value['pages']);
					}
					elseif ($options_dynamic == 'members') {
						$options = $e->getUsersForSelection($options_value['members']);
					}					
					if (is_array($options) && count($options)) {	
						foreach ($options as $i => $o) {							
							if ($options_dynamic == 'manual') {
								if (empty($o['value'])) $o['value'] = $o['name'];
								if ($e->getElementType() == 'select') $elements[$er]['options'][html_entity_decode($o['value'])] = html_entity_decode($o['value']);
								else $elements[$er]['options'][html_entity_decode($o['value'])] = $th->sanitizeFileSystem($e->getHandle()).($i+1);
							}
							elseif ($options_dynamic == 'pages') {
								if (is_object($o)) $elements[$er]['options'][$o->getCollectionID()] = $o->getCollectionID();	
							}
							elseif ($options_dynamic == 'members') {
								if (is_object($o)) $elements[$er]['options'][$o->getUserID()] = $o->getUserID();
							}
						}
					}
				}

				if (!empty($ea['element_value']) && !in_array($ea['element_value'], (array)$elements[$er]['values'])) {
					$elements[$er]['values'][] = $ea['element_value'];			
				}
				if (!empty($ea['condition']) && in_array($ea['condition'], array('empty', 'not_empty', 'enabled', 'disabled'))) {					
					$elements[$er][$ea['condition']][] = 1;	
				}
				if (!empty($ea['condition']) && !empty($ea['condition_value']) && !in_array($ea['condition_value'], (array)$elements[$er]['values'])) {					
					if ($ea['condition'] == 'contains') $elements[$er]['match'][] = $ea['condition_value'];					
					if ($ea['condition'] == 'not_contains') $elements[$er]['not_match'][] = $ea['condition_value'];							
					if ($ea['condition'] == 'equals') $elements[$er]['values'][] = $ea['condition_value'];					
					if ($ea['condition'] == 'not_equals') $elements[$er]['not_values'][] = $ea['condition_value'];							
				}
				
				// inverse values when no_value is selected...
				$inverse = false;
				if (@in_array('no_value', (array)$elements[$er]['values'])) $inverse = true;
			}
			
			if (!empty($actions) && !empty($elements)) {
				$dependencies[] = array(
					'actions' => $actions,
					'elements' => $elements,
					'inverse' => $inverse
				);
			}
		}		
		
		$validate = array();
		if (!empty($dependencies)) {			
			// Setup dependencies for validation
			foreach ($dependencies as $dep) {
				if (is_array($dep['actions']) && (array_key_exists('show', $dep['actions']) || array_key_exists('enable', $dep['actions']))) {						
					$rule = array();
					foreach ($dep['elements'] as $e) {
						$value = (array)$e['values'];
						if (is_array($e['options']) && count($e['options'])) {						
							if (in_array('any_value', $value)) {
								$value = (array)$e['options'];							
								if (in_array($e['type'], array('radio', 'checkbox'))) $value = array_keys((array)$e['options']);		
							} 
							elseif (in_array('no_value', $value)) $value = array();	
						}
						$rule[] = array(
							'element' => $e['handle'],
							'elementID' => $e['elementID'],
							'value' => $value,
							'match' => $e['match']
						);
					}
				}
				if (!empty($rule)) $validate[] = $rule;
			}		
		}

		$dep = array(
			'raw' => $deps,
			'validate' => $validate,
			'initialized' => $dependencies
		);
		$this->setAttribute('dependencies', $dep);
	}

	public function javascriptDependency() {

		$dependencies = $this->getDependency('initialized');
		if (!$dependencies || !is_array($dependencies) || !count($dependencies)) return false;

		$th = Core::make('helper/text');

		$ifs = array();
		$variables = array();
		$conditions = array();
		$dont = array();	

		// Build action
		foreach ($dependencies as $rule => $dependency) {				
					
			foreach ($dependency['elements'] as $key => $element) {			
				
				// TODO
				// All checks in {}, just true or false?
				// First find elements and get there data
				if (!is_array($element['options']) || !count($element['options'])) {
					
					// This is a "normal" field. Return value depends on the condition

					switch ($element['type']) {

						case 'fullname':
						case 'address':
						case 'upload':
							if ($element['enabled']) $variables[] = 'var e_'.$rule.'_'.$key.' = !$(\'[name^="'.$element['handle'].'["]\', f).eq(0).is(\':disabled\');';
							if ($element['disabled']) $variables[] = 'var e_'.$rule.'_'.$key.' = $(\'[name^="'.$element['handle'].'["]\', f).eq(0).is(\':disabled\');';
							if ($element['empty']) $variables[] = 'var e_'.$rule.'_'.$key.' = $.trim($(\'[name^="'.$element['handle'].'["]\', f).map(function() { return $(this).val(); }).get().join(\' \')).length <= 0;';
							if ($element['not_empty']) $variables[] = 'var e_'.$rule.'_'.$key.' = $.trim($(\'[name^="'.$element['handle'].'["]\', f).map(function() { return $(this).val(); }).get().join(\' \')).length > 0;';								
							if ($element['values']) $variables[] = 'var e_'.$rule.'_'.$key.' = $.trim($(\'[name^="'.$element['handle'].'["]\', f).map(function() { return $(this).val(); }).get().join(\' \')) == \''.$element['values'][0].'\';';
							if ($element['not_values']) $variables[] = 'var e_'.$rule.'_'.$key.' = $.trim($(\'[name^="'.$element['handle'].'["]\', f).map(function() { return $(this).val(); }).get().join(\' \')) != \''.$element['values'][0].'\';';
							if ($element['match']) $variables[] = 'var e_'.$rule.'_'.$key.' = $.trim($(\'[name^="'.$element['handle'].'["]\', f).map(function() { return $(this).val(); }).get().join(\' \')).match(/'.$element['match'][0].'/gi) !== null;';
							if ($element['not_match']) $variables[] = 'var e_'.$rule.'_'.$key.' = $.trim($(\'[name^="'.$element['handle'].'["]\', f).map(function() { return $(this).val(); }).get().join(\' \')).match(/'.$element['not_match'][0].'/gi) == null;';
						break;

						case 'signature':
							if ($element['enabled']) $variables[] = 'var e_'.$rule.'_'.$key.' = !$(\'div[id="'.$element['handle'].'"]\', f).hasClass(\'disabled\');';
							if ($element['disabled']) $variables[] = 'var e_'.$rule.'_'.$key.' = $(\'div[id="'.$element['handle'].'"]\', f).hasClass(\'disabled\');';
							if ($element['empty']) $variables[] = 'var e_'.$rule.'_'.$key.' = $.trim($(\'[name="'.$element['handle'].'"]\', f).val()).length <= 0;';	
							if ($element['not_empty']) $variables[] = 'var e_'.$rule.'_'.$key.' = $.trim($(\'[name="'.$element['handle'].'"]\', f).val()).length > 0;';								
							if ($element['values']) $variables[] = 'var e_'.$rule.'_'.$key.' = $.trim($(\'[name="'.$element['handle'].'"]\', f).val()) == \''.$element['values'][0].'\';';
							if ($element['not_values']) $variables[] = 'var e_'.$rule.'_'.$key.' = $.trim($(\'[name="'.$element['handle'].'"]\', f).val()) != \''.$element['values'][0].'\';';
							if ($element['match']) $variables[] = 'var e_'.$rule.'_'.$key.' = $.trim($(\'[name="'.$element['handle'].'"]\', f).val()).match(/'.$element['match'][0].'/gi) !== null;';
							if ($element['not_match']) $variables[] = 'var e_'.$rule.'_'.$key.' = $.trim($(\'[name="'.$element['handle'].'"]\', f).val()).match(/'.$element['not_match'][0].'/gi) == null;';
						break;

						case 'gdpr':
							if ($element['enabled']) $variables[] = 'var e_'.$rule.'_'.$key.' = !$(\'[name="'.$element['handle'].'"]\', f).is(\':disabled\');';
							if ($element['disabled']) $variables[] = 'var e_'.$rule.'_'.$key.' = $(\'[name="'.$element['handle'].'"]\', f).is(\':disabled\');';
							if ($element['empty']) $variables[] = 'var e_'.$rule.'_'.$key.' = !$(\'[name="'.$element['handle'].'"]\', f).is(\':checked\');';	
							if ($element['not_empty']) $variables[] = 'var e_'.$rule.'_'.$key.' = $(\'[name="'.$element['handle'].'"]\', f).is(\':checked\');';
						break;

						default:
							if ($element['enabled']) $variables[] = 'var e_'.$rule.'_'.$key.' = !$(\'[name="'.$element['handle'].'"]\', f).is(\':disabled\');';
							if ($element['disabled']) $variables[] = 'var e_'.$rule.'_'.$key.' = $(\'[name="'.$element['handle'].'"]\', f).is(\':disabled\');';
							if ($element['empty']) $variables[] = 'var e_'.$rule.'_'.$key.' = $.trim($(\'[name="'.$element['handle'].'"]\', f).val()).length <= 0;';	
							if ($element['not_empty']) $variables[] = 'var e_'.$rule.'_'.$key.' = $.trim($(\'[name="'.$element['handle'].'"]\', f).val()).length > 0;';								
							if ($element['values']) $variables[] = 'var e_'.$rule.'_'.$key.' = $.trim($(\'[name="'.$element['handle'].'"]\', f).val()) == \''.$element['values'][0].'\';';
							if ($element['not_values']) $variables[] = 'var e_'.$rule.'_'.$key.' = $.trim($(\'[name="'.$element['handle'].'"]\', f).val()) != \''.$element['values'][0].'\';';
							if ($element['match']) $variables[] = 'var e_'.$rule.'_'.$key.' = $.trim($(\'[name="'.$element['handle'].'"]\', f).val()).match(/'.$element['match'][0].'/gi) !== null;';
							if ($element['not_match']) $variables[] = 'var e_'.$rule.'_'.$key.' = $.trim($(\'[name="'.$element['handle'].'"]\', f).val()).match(/'.$element['not_match'][0].'/gi) == null;';
						break;

					}			
				}
				else {
					if (@in_array('any_value', (array)$element['values'])) {
						if ($element['type'] == 'checkbox' || $element['type'] == 'radio') $variables[] = 'var e_'.$rule.'_'.$key.' = $(\'[name="'.$element['handle'].'[]"]:checked\', f).length > 0;';
						else $variables[] = 'var e_'.$rule.'_'.$key.' = $(\'[name="'.$element['handle'].'[]"]>option[value!=""]:selected\', f).length > 0;';
					}
					elseif (@in_array('no_value', (array)$element['values'])) {
						if ($element['type'] == 'checkbox' || $element['type'] == 'radio') $variables[] = 'var e_'.$rule.'_'.$key.' = $(\'[name="'.$element['handle'].'[]"]:checked\', f).length <= 0;';
						else $variables[] = 'var e_'.$rule.'_'.$key.' = $(\'[name="'.$element['handle'].'[]"]">option[value!=""]:selected\', f).length <= 0;';
					}
					else {
						$options = array();
						foreach ((array)$element['values'] as $value) {							
							$options[] = $element['options'][$value];								
						}
						if (is_array($options) && count($options)) {
							$j = array();
							foreach ($options as $opt) {
								if ($element['type'] == 'checkbox' || $element['type'] == 'radio') $j[] = '$(\'[id="'.$opt.'"]\', f).is(\':checked\')';
								else $j[] = '$(\'[name="'.$element['handle'].'[]"]>option[value="'.addslashes($opt).'"]\', f).is(\':selected\')';
							}
							$variables[] = 'var e_'.$rule.'_'.$key.' = ('.@implode(' && ', $j).');';
						}						
					}
				}
				$ifs[$rule][] = 'e_'.$rule.'_'.$key;
			}

			if (is_array($ifs[$rule]) && count($ifs[$rule])) {							
				$do = array();		
				foreach ($dependency['actions'] as $action => $value) {
					if ($action == 'enable') $do[] = '[\'enable\']';
					if ($action == 'show') $do[] = '[\'show\']';
					if ($action == 'value') $do[] = '[\'value\', \''.$value.'\']';
					if ($action == 'placeholder') $do[] = '[\'placeholder\', \''.$value.'\', \'add\']';
					if ($action == 'class') $do[] = '[\'class\', \''.$value.'\', \'add\']';				
				}
				$conditions[$rule][] = 'ff_'.$this->getFormID().'.do_dependency(\''.$this->getHandle().'\', ['.@implode(', ', $do).']);';
					
				foreach ($dependency['actions'] as $action => $value) {
					if ($action == 'enable') $do = '[\'disable\']';
					if ($action == 'show') $do = '[\'hide\']';
					if ($action == 'class') $do = '[\'class\', \''.$value.'\', \'delete\']';
					if ($action == 'placeholder') $do = '[\'placeholder\']';
					$dont[$this->getHandle()][] = $do;			
				}
			}
		}

		$javascript = array();
		$javascript[] = @implode('', $variables);

		foreach (array_keys($dependencies) as $rule) {			
			$javascript[] = 'if ((';
			$javascript[] = @implode(' && ', $ifs[$rule]);

			/*
			if (is_array($ifs[$rule]) && count($ifs[$rule]) > 1) {
				$nots = array();
				$does = array();
				foreach ($ifs as $r => $if) {
					if ($r == $rule) continue;
					$nots[] = '!('.@implode(' && ', $ifs[$r]).')';
					$does[] = @implode(' && ', $ifs[$r]);
				}
				if (is_array($nots) && count($nots)) $javascript[] = ' && '.@implode(' && ', $nots);
				
				$javascript[] = ') || ('.@implode(' && ', $ifs[$rule]);
				if (is_array($does) && count($does)) $javascript[] = ' && '.@implode(' && ', $does);
			}
			*/

			$javascript[] = ')) {';
			$javascript[] = @implode('', $conditions[$rule]);
			$javascript[] = '} else ';
		}	

		$javascript[] = '{';
		foreach ($dont as $handle => $donts) {
			$donts = array_unique($donts);
			$javascript[] = 'ff_'.$this->getFormID().'.do_dependency(\''.$this->getHandle().'\', ['.@implode(', ', $donts).']);';
		}
		$javascript[] = '}';

		$jquery = 'ff_'.$this->getFormID().'.add_dependency(function(f) {'.@implode('', $javascript).'});';
		return $jquery;
	}
}