<?php
namespace Concrete\Package\FormidableFull\Src\Formidable;

use \Concrete\Package\FormidableFull\Src\Formidable;
use \Concrete\Package\FormidableFull\Src\Formidable\Element;
use \Concrete\Package\FormidableFull\Src\Formidable\Step;
use \Concrete\Package\FormidableFull\Src\Formidable\Layout;
use \Concrete\Package\FormidableFull\Src\Formidable\Result;
use \Concrete\Package\FormidableFull\Src\Formidable\Mailing;
use \Concrete\Core\Http\Service\Json;
use Core;
use \Concrete\Core\Support\Facade\Database;
use \Concrete\Core\Support\Facade\Url;
use \Concrete\Core\User\User;

class Form extends Formidable {

	public $layouts = array();
	public $steps = array();
	public $mailings = array();

	public static function getByID($formID) {
		$item = new Form();
		if ($item->load($formID)) return $item;
		return false;
	}

	public function load($formID) {
		if (intval($formID) == 0) return false;
		$db = Database::connection();
		$form = $db->fetchAssoc("SELECT * FROM FormidableForms WHERE formID = ?", array($formID));
		if (!$form) return false;

		$params = array(
			'formID' => $form['formID'],
			'label' => $form['label'],
			'handle' => $form['handle']
		);
		$this->setAttributes($params);

		if ($form['css'] && !empty($form['css_value'])) $form['class'] = $form['css_value'];
		if ($form['permission']) {
			if (!empty($form['permission_form'])) $form['permission_form'] = @explode(',',$form['permission_form']);
			if (!empty($form['permission_results'])) $form['permission_results'] = @explode(',',$form['permission_results']);
		}

		$this->setAttributes(array('attributes' => $form));
		return true;
	}

	public function setAnswerSetID($answerSetID, $saveInSession = true) {
		$this->answerSetID = $answerSetID;
		if ($saveInSession) {
			$session = Core::make('app')->make('session');
			$session->set('answerSetID'.$this->getFormID(), $this->getAnswerSetID());
		}
	}

	public function getLayout() {
		if (is_array($this->layouts) && count($this->layouts)) return $this->layouts;
		$db = Database::connection();
		$layouts = $db->fetchAll("SELECT layoutID, rowID FROM FormidableFormLayouts WHERE formID = ? ORDER BY rowID ASC, sort ASC", array($this->getFormID()));
		if (is_array($layouts) && count($layouts)) {
			$result = $this->getResult();
			foreach ($layouts as $l) {
				$nfl = Layout::getByID($l['layoutID']);
				if (is_object($nfl)) {
					if ($nfl->appearance == 'step') $this->layouts[$l['rowID']] = $nfl;
					else {
						if ($result !== false) $nfl->setResult($result);
						$this->layouts[$l['rowID']][$l['layoutID']] = $nfl;
					}
				}
			}
			return $this->layouts;
		}

		// If there are no rows or colums, create a sinlge one and return
		$nfl = new Layout();
		if ($nfl->save(array('formID' => $this->getFormID(), 'rowID' => 0))) {
			$this->layouts[0][$nfl->getLayoutID()] = $nfl;

			// Move already existing elements to this layout. (just in case something went wrong when removing)
			$db->executeQuery("UPDATE FormidableFormElements SET layoutID = ? WHERE formID = ?", array($nfl->getLayoutID(), $this->getFormID()));
		}
		return $this->layouts;
	}

	public function getLayoutByRow($row) {
		if (isset($this->layouts) && is_object($this->layouts[$row])) return $this->layouts[$row];
		return false;
	}

	public function getNextRowID() {
		$db = Database::connection();
		return $db->getOne("SELECT MAX(rowID) + 1 FROM FormidableFormLayouts WHERE formID = ?", array($this->getFormID()));
	}

	public function getElements($types = 'all') {
		$elements = array();

		// Get elements from DB
		$q = "SELECT elementID, element_type FROM FormidableFormElements WHERE formID = ?";
		switch ($types) {
			case 'upload': $q .= "AND (element_type = 'upload')"; break;
			case 'send_to': $q .= "AND (element_type = 'emailaddress' OR element_type = 'recipientselector')"; break;
		}
		$q .= " ORDER BY sort ASC";

		$db = Database::connection();
		$results = $db->fetchAll($q, array($this->getFormID()));
		foreach ((array)$results as $result) {
			$el = Element::getByID($result['elementID']);
			if (is_object($el)) $elements[$el->getElementID()] = $el;
		}
		return $elements;
	}

	public function getElementsByStep($step) {
		$elements = array();
		$layout = $this->getLayout();
		if (is_array($layout) && count($layout)) {
			$step_nr = -1;
			$current_step = false;
			foreach($layout as $rowID => $row) {
				if (is_object($row)) {
					$step_nr++;
					continue;
				}
				// Only add elements of current step
				if ($step == $step_nr) {
					foreach($row as $column) {
						$elmts = $column->getElements();
						if (is_array($elmts) && count($elmts)) $elements += $elmts;
					}
				}
			}
		}
		return $elements;
	}

	public function getSteps() {
		if (is_array($this->steps) && count($this->steps)) return $this->steps;
		$db = Database::connection();
		$steps = $db->fetchAll("SELECT * FROM FormidableFormLayouts WHERE formID = ? AND appearance = 'step' ORDER BY rowID ASC", array($this->getFormID()));
		if (is_array($steps) && count($steps) > 0) $this->steps = $steps;
		return $this->steps;
	}

	public function getMailings() {
		if (is_array($this->mailings) && count($this->mailings)) return $this->mailings;
		$db = Database::connection();
		$results = $db->fetchAll("SELECT mailingID FROM FormidableFormMailings WHERE formID = ?", array($this->getFormID()));
		if (is_array($results) && count($results) > 0) {

			// Get result if there is one...
			$result = $this->getResult();
			if (is_object($result)) $result->getAnswers();

			$elements = $this->getElements();
			foreach ($results as $m) {
				$mailing = Mailing::getByID($m['mailingID']);
				if (is_object($mailing)) {
					if ($elements !== false) $mailing->setElements($elements);
					if ($result !== false) $mailing->setResult($result, true);
					$this->mailings[] = $mailing;
				}
			}
		}
		return $this->mailings;
	}

	public function getResult() {
		$result = parent::getResult();
		if ($result !== false) return $result;
		$db = Database::connection();
		$answerSetID = $db->fetchColumn("SELECT answerSetID FROM FormidableAnswerSets WHERE answerSetID = ? AND formID = ?", array($this->getAnswerSetID(), $this->getFormID()));
		if (!$answerSetID) $this->answerSetID = 0;
		$result = Result::getByID($this->getAnswerSetID());
		if (!is_object($result)) $result = new Result();
		parent::setResult($result);
		return $result;
	}

	public function getResults() {
		if (is_array($this->results) && count($this->results)) return $this->results;
		$db = Database::connection();
		$results = $db->fetchAll("SELECT answerSetID FROM FormidableAnswerSets WHERE formID = ?", array($this->getFormID()));
		if (is_array($results) && count($results) > 0) {
			foreach ($results as $r) {
				$result = Result::getByID($r['answerSetID']);
				if (is_object($result)) {
					$this->results[] = $result;
				}
			}
		}
		return $this->results;
	}

	public function generate() {

		// Load results if there should be any
		$result = $this->getResult();

		// Now create the layout with her elements
		$rows = $this->getLayout();

		if (!$rows || !is_array($rows) || !count($rows)) return false;

		foreach ($rows as $r => $columns) {
			if (!$columns || !is_array($columns) || !count($columns)) continue;
			foreach ($columns as $c => $column) {
				if (!is_object($column)) continue;
				$column->generate();
				$elements = $column->getElements();
				if (!$elements || !is_array($elements) || !count($elements)) continue;
				foreach ($elements as $element) {
					$this->addJavascript($element->getJavascript(), false);
					$this->addJavascript($element->getJQuery(), true);
					$this->addJavascript($element->javascriptDependency(), true);
				}
			}
		}
	}

	public function getSubmissionCount($query = '', $value = '') {
		$db = Database::connection();
		$q = "SELECT COUNT(answerSetID) AS total FROM FormidableAnswerSets WHERE formID = ? AND temp != 1";
		$p = array($this->getFormID());
		if (!empty($query) && !empty($value)) {
			$q .= $query;
			$p[] = $value;
		}
		$data = $db->fetchColumn($q, $p);
		return intval($data);
	}

	public function getLastSubmissionID(){
		$db = Database::connection();
		$data = $db->fetchColumn("SELECT answerSetID FROM FormidableAnswerSets WHERE formID = 1 AND temp != 1 ORDER BY submitted DESC", array($this->getFormID()));
		if ($data) return $data;
		return t('Never');
	}

	public function getLastSubmissionDate() {
		$db = Database::connection();
		$data = $db->fetchColumn("SELECT submitted FROM FormidableAnswerSets WHERE formID = 1 AND temp != 1 ORDER BY submitted DESC", array($this->getFormID()));
		if ($data) return Core::make('helper/date')->formatPrettyDateTime(strtotime($data));
		return t('Never');
	}

	public function hasCaptcha() {
		$db = Database::connection();
		$results = $db->fetchColumn("SELECT elementID FROM FormidableFormElements WHERE formID = ? AND element_type = 'captcha'", array($this->getFormID()));
		return $results;
	}

	public function hasButtons() {
		$db = Database::connection();
		$results = $db->fetchColumn("SELECT elementID FROM FormidableFormElements WHERE formID = ? AND element_type = 'buttons'", array($this->getFormID()));
		return $results;
	}

	public function hasSteps() {
		$db = Database::connection();
		$results = $db->fetchColumn("SELECT layoutID FROM FormidableFormLayouts WHERE formID = ? AND appearance = 'step'", array($this->getFormID()));
		return $results;
	}

	public function isGDPR() {
		return intval($this->getAttribute('gdpr'))==1;
	}
	public function registerIP() {
		return !$this->isGDPR() || ($this->isGDPR() && intval($this->getAttribute('gdpr_ip'))!=1);
	}
	public function registerBrowser() {
		return !$this->isGDPR() || ($this->isGDPR() && intval($this->getAttribute('gdpr_browser'))!=1);
	}

	public function hasPermissions($type = 'form', $groups = array()) {
		if (intval($this->getAttribute('permission')) == 0) return true;
		if (empty($groups)) {
			$ui = Formidable::getUser();
			if ($ui) {
				$groups = $ui->getUserObject()->getUserGroups();
				if ($ui->getUserID() == USER_SUPER_ID) return true;
			}
		}
		if (is_array($groups) && !count($groups)) return true;

		$permissions = array();
		if ($type == 'form') $permissions = $this->getAttribute('permission_form');
		if ($type == 'results') $permissions = $this->getAttribute('permission_results');

		if (!is_array($groups) && intval($groups) != 0) return in_array(intval($groups), (array)$permissions);
		if (is_array($groups) && count($groups)) return count(array_intersect($groups, (array)$permissions))>0;
	}

	public function checkLimits() {
		if (!$this->getAttribute('limits')) return false;
		switch ($this->getAttribute('limits_type')) {
			case 'total':
				if ($this->getSubmissionCount() >= intval($this->getAttribute('limits_value'))) return true;
			break;
			case 'ip':
				if ($this->getSubmissionCount(' and ip = ?', Formidable::getIP()) >= intval($this->getAttribute('limits_value'))) return true;
			break;
			case 'user':
				$u = new User();
				if ($u->isLoggedIn() && $this->countResults('and userID = ?', $u->getUserID()) >= intval($this->getAttribute('limits_value'))) return true;
			break;
		}
		return false;
	}

	public function checkSchedule() {
		if (!$this->getAttribute('schedule')) return false;
		if (strtotime($this->getAttribute('schedule_start')) > 0 && strtotime("now") <= strtotime($this->getAttribute('schedule_start'))) return true;
		if (strtotime($this->getAttribute('schedule_end')) > 0 && strtotime("now") > strtotime($this->getAttribute('schedule_end'))) return true;
		return false;
	}

	public function getAvailableElements() {
		$available = parent::getAvailableElements();
		return $available;
	}

	public function orderElements($elements = array(), $layout = array()) {
		$els = array();
		$db = Database::connection();
		if (!count($elements) || !count($layout)) $els = $db->fetchAll("SELECT elementID, layoutID FROM FormidableFormElements WHERE formID = ? ORDER BY sort ASC", array($this->getFormID()));
		else {
			foreach ($elements as $i => $element) {
				$els[$i] = array(
					'elementID' => $element,
					'layoutID' => $layout[$i]
				);
			}
		}
		if (is_array($els) && count($els)) {
			foreach ($els as $i => $e) {
				if (intval($e['layoutID']) != 0 && intval($e['elementID']) != 0) {
					$r = $db->executeQuery("UPDATE FormidableFormElements SET sort = ?, layoutID = ? WHERE elementID = ? AND formID = ?", array($i, $e['layoutID'], $e['elementID'], $this->getFormID()));
					if (!$r) return false;
				}
			}
		}
		return true;
	}

	public function orderLayout($layout = array()) {
		$layouts = array();
		$db = Database::connection();
		if (!count($layout)) $layout = $db->fetchAll("SELECT layoutID FROM FormidableFormLayouts WHERE formID = ? ORDER BY sort ASC", array($this->getFormID()));
		if (is_array($layout) && count($layout)) {
			for ($i=0; $i<count($l); $i++) {
				$layouts[$i] = array('layoutID' => $l[$i]);
			}
		}
		if (is_array($layout) && count($layouts)) {
			for ($i=0; $i<count($layouts); $i++) {
				if (intval($layouts[$i]['layoutID']) != 0) {
					$r = $db->executeQuery("UPDATE FormidableFormLayouts SET sort = ? WHERE layoutID = ? AND formID = ?", array($i, $layouts[$i]['layoutID'], $this->getFormID()));
					if (!$r) return false;
				}
			}
		}
		return true;
	}

	public function save($data) {
		if (!$this->getFormID()) return $this->add($data);
		return $this->update($data);
	}

	private function add($data) {
		$db = Database::connection();
		$db->insert('FormidableForms', $data);
		$formID = $db->lastInsertId();
		if (empty($formID)) return false;
		$this->load($formID);
		return true;
	}

	private function update($data) {
		$db = Database::connection();
		$db->update('FormidableForms', $data, array('formID' => $this->getFormID()));
		$this->load($this->getFormID());
		return true;
	}

	public function duplicate() {

		$new_elements = array();

		$layout = $this->getLayout();
		$mailings = $this->getMailings();

		$db = Database::connection();
		$form = $db->fetchAssoc("SELECT * FROM FormidableForms WHERE formID = ?", array($this->getFormID()));
		if (!$form) return false;

		// Set new params
		$form['label'] = t('%s (copy)', $form['label']);

		// Unset current formID
		unset($form['formID']);

		$nf = new Form();
		if (!$nf->add($form)) return false;

		if (is_array($layout) && count($layout)) {
			foreach($layout as $rowID => $row) {
				// Duplicate step
				if (is_object($row)) $nl = $row->duplicate($nf->getFormID());
				else {
					// Duplicate layout and elements
					foreach($row as $column) {
						$nl = $column->duplicate($nf->getFormID());
						foreach ((array)$column->getElements() as $e) {
							$nfe = $e->duplicate($nf->getFormID(), $nl->getLayoutID());
							$new_elements[$e->getElementID()] = $nfe->getElementID();
						}
					}

				}
			}
		}

		// Convert dependecies to new elements
		foreach ($new_elements as $oldElementID => $newElementID) {

			$nfe = Element::getByID($newElementID);
			if (!is_object($nfe)) continue;

			$depends = $nfe->getDependency('raw');
			if (empty($depends)) continue;

			$dependencies = array();
			foreach ((array)$depends as $dep) {
				$dependencyActions = $dependencyElements = array();
				foreach ((array)$dep['actions'] as $action) {
					$dependencyActions[] = array_filter(array(
						'action' => (string)$action['action'],
						'action_value' => (string)$action['action_value'],
						'action_select' => (string)$action['action_select']
					));
				}
				foreach ((array)$dep['elements'] as $element) {
					$dependencyElements[] = array_filter(array(
						'element' => $new_elements[$element['element']],
						'element_value' => (string)$element['element_value'],
						'condition' => (string)$element['condition'],
						'condition_value' => (string)$element['condition_value']
					));
				}
				$dependencies[] = array(
					'actions' => $dependencyActions,
					'elements' => $dependencyElements
				);
			}
			$nfe->save(array('dependencies' => serialize($dependencies)));
		}

		// Duplicate mailings
		foreach ((array)$mailings as $mailing) {
			// Set new elements
			$mailing->setElements($new_elements, true);
			$nfm = $mailing->duplicate($nf->getFormID());
		}

		return $nf;
	}

	public function delete() {

		// Remove elements
		$elements = $this->getElements();
		if (is_array($elements) && count($elements)) {
			foreach ((array)$elements as $e) {
				if (is_object($e)) $e->delete();
			}
		}

		// Remove layout
		$rows = $this->getLayout();
		if (is_array($rows) && count($rows)) {
			foreach ((array)$rows as $layouts) {
				if (is_array($layouts) && count($layouts)) {
					foreach ((array)$layouts as $l) {
						if (is_object($l)) $l->delete();
					}
				}
			}
		}

		//Remove mailings
		$mailings = $this->getMailings();
		if (is_array($mailings) && count($mailings)) {
			foreach ((array)$mailings as $m) {
				if (is_object($m)) $m->delete();
			}
		}

		// Remove results
		$results = $this->getResults();
		if (is_array($results) && count($results)) {
			foreach ((array)$results as $r) {
				if (is_object($r)) $r->delete();
			}
		}

		// Remove form
		$db = Database::connection();
		$db->delete('FormidableForms', array('formID' => $this->getFormID()));

		return true;
	}
}
