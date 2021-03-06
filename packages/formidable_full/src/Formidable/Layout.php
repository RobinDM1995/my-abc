<?php
namespace Concrete\Package\FormidableFull\Src\Formidable;

use \Concrete\Package\FormidableFull\Src\Formidable;
use \Concrete\Package\FormidableFull\Src\Formidable\ValidatorProperty;
use \Concrete\Core\Support\Facade\Database;
use Core;

class Layout extends Formidable {

	private $element_has_label = false;

	public static function getByID($layoutID) {
		$item = new Layout();
		if ($item->load($layoutID)) return $item;
		return false;
	}

	public function load($layoutID) {
		if (intval($layoutID) == 0) return false;
		$db = Database::connection();
		$layout = $db->fetchAssoc("SELECT * FROM FormidableFormLayouts WHERE layoutID = ?", array($layoutID));
		if (!$layout) return false;

		$this->setAttributes($layout);

		$attributes = array();
		$attributes['class'] = 'formidable_row ';
		if ($this->getAppearance() == 'step') $attributes['class'] .= 'step ';
		if ($this->getCss()) $attributes['class'] .= $this->getCssValue();
		if (is_array($attributes) && count($attributes)) $this->setAttribute('attributes', $attributes);

		return true;
	}

	public function getElements() {
		if (is_array($this->elements) && count($this->elements)) return $this->elements;
		$db = Database::connection();
		$elements = $db->fetchAll("SELECT elementID, element_type FROM FormidableFormElements WHERE formID = ? AND layoutID = ? ORDER BY sort ASC", array($this->getFormID(), $this->getLayoutID()));
		if (is_array($elements) && count($elements) > 0) {
			foreach ($elements as $element) {
				$e = Element::getByID($element['elementID']);
				if (is_object($e)) $this->elements[$e->getElementID()] = $e;
			}
		}
		return $this->elements;
	}

	public function getLayoutID() {
		return is_numeric($this->layoutID)?$this->layoutID:false;
	}

	public function hasElementsWithLabels() {
		return $this->element_has_label;
	}

	public function getContainerStart() {

		$format = 'div';
		if ($this->getAppearance() == 'fieldset') $format = 'fieldset';

		$attribs = array();
		$attributes = $this->getAttributes();

		// Set step number
		if ($this->getAppearance() == 'step') $attributes['data-step'] = 0;

		if (is_array($attributes) && count($attributes)) {
			foreach ($attributes as $name => $value) {
				$attribs[] = $name.'="'.$value.'" ';
			}
		}

		$start = '<'.$format.' '.@implode(' ', $attribs).'>';
		if ($this->getAppearance() == 'fieldset') $start .= '<legend>'.$this->getLabel().'</legend>';
		return $start;
	}

	public function getContainerEnd() {
		if ($this->getAppearance() == 'step') {
			$text_prev = t('Previous');
			$class_prev = "btn btn-success previous ";

			$text_next = t('Next');
			$class_next = "btn btn-success next ";
			if ($this->isLastStep()) {
				$text_next = t('Submit');
				$class_next .= "last ";
			}

			if ($this->getCustomButtons() == 1) {
				$prev = $this->getBtnPrev();
				$text_prev = !empty($prev)?$prev:$text_prev;
				if ($this->getBtnPrevCss())	$class_prev .= $this->getBtnPrevCssValue();

				$next = $this->getBtnNext();
				$text_next = !empty($next)?$next:$text_next;
				if ($this->getBtnNextCss())	$class_next .= $this->getBtnNextCssValue();
			}

			$html[] = '<div class="formidable_row">';
			$html[] = '<div class="formidable_column width-12">';
			$html[] = '<div class="formidable_column_inner">';
			$html[] = '<div class="element">';
			$html[] = '<input type="button" class="'.$class_prev.'" id="previous" value="'.$text_prev.'" />';
			$html[] = '<div class="pull-right">';	
			$html[] = '<div class="please_wait_loader"><img src="'.BASE_URL.DIR_REL.'/packages/formidable_full/images/loader.gif" alt="'.t('Please wait...').'"></div>';
			$html[] = '<input type="submit" class="'.$class_next.'" id="next" value="'.$text_next.'" />';
			$html[] = '</div>';
			$html[] = '</div>';
			$html[] = '</div>';
			$html[] = '</div>';
			$html[] = '</div>';
			$html[] = '</div>';

			return @implode(PHP_EOL, $html);
		}
		$format = 'div';
		if ($this->getAppearance() == 'fieldset') $format = 'fieldset';
		return '</'.$format.'>';
	}

	public function isLastStep() {
		$db = Database::connection();
		$layout = $db->fetchAssoc("SELECT * FROM FormidableFormLayouts WHERE formID = ? AND layoutID = ?", array($this->getFormID(), $this->getLayoutID()));
		if (!$layout) return false;
		return false;
	}

	public function generate() {
		$has_label = false;
		$elements = $this->getElements();
		if (is_array($elements) && count($this->elements)) {
			$result = $this->getResult();
			foreach ($this->elements as $eID => $e) {
				// Setting values and generate output
				if ($result !== false) $e->setResult($result);
				$e->setValue();
				$e->generateInput();

				// Overwrite current element;
				$this->elements[$eID] = $e;

				// Findout if element needs label
				if (!$e->getPropertyValue('label_hide')) $this->element_has_label = true;
			}
		}
	}

	public function save($data)	{
		if (!$this->getLayoutID()) return $this->add($data);
		else return $this->update($data);
	}

	private function add($data) {
		if(!$data['sort']) $data['sort'] = $this->getNext(intval($data['formID']));
		$db = Database::connection();
		$db->insert('FormidableFormLayouts', $data);
		$layoutID = $db->lastInsertId();
		if (empty($layoutID)) return false;
		$this->load($layoutID);
		return true;
	}

	private function update($data) {
		$db = Database::connection();
		$db->update('FormidableFormLayouts', $data, array('layoutID' => $this->getLayoutID()));
		$this->load($this->getFormID());
		return true;
	}

	public function duplicate($formID = 0, $rowID = '') {
		$db = Database::connection();
		$layout = $db->fetchAssoc("SELECT * FROM FormidableFormLayouts WHERE formID = ? AND layoutID = ?", array($this->getFormID(), $this->getLayoutID()));
		if (!$layout) return false;

		unset($layout['layoutID']);

		if (!empty($formID)) $layout['formID'] = $formID;
		if (!empty($layout['label']) && empty($formID))	$layout['label'] .= ' ('.t('copy').')';
		if (!empty($rowID)) $layout['rowID'] = $rowID;
		$layout['sort'] = $this->getNext(intval($layout['formID']));

		$nfl = new Layout();
		$nfl->add($layout);
		return $nfl;
	}

	public function delete() {
		$db = Database::connection();
		$db->delete('FormidableFormLayouts', array('layoutID' => $this->layoutID, 'formID' => $this->formID));
		$this->orderLayout();
	}

	public function validateProperties()
	{
		$validator = new ValidatorProperty();

		if ($this->properties['label'])
			$validator->label($this->request_post('label'));

		if ($this->properties['css'])
			$validator->css($this->request_post('css'), $this->request_post('css_value'));

		return $validator->getList();
	}

	public function getNext($formID) {
		return parent::getNextSort('layout', $formID);
	}
}
