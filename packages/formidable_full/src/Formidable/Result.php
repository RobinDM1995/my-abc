<?php
namespace Concrete\Package\FormidableFull\Src\Formidable;

use \Concrete\Package\FormidableFull\Src\Formidable;
use \Concrete\Core\Page\Page;
use \Concrete\Core\User\User;
use Core;
use \Concrete\Core\Support\Facade\Database;
use Localization;
use \Symfony\Component\EventDispatcher\GenericEvent as Event;
use \Events;

class Result extends Formidable {

	public $answers = array();

	public static function getByID($answerSetID) {
		$item = new Result();
		if ($item->load($answerSetID)) return $item;
		return false;
	}

	public function load($answerSetID) {
		if (intval($answerSetID) == 0) return false;
		$db = Database::connection();
		$result = $db->fetchAssoc("SELECT * FROM FormidableAnswerSets WHERE answerSetID = ?", array($answerSetID));
		if (!$result) return false;
		$this->setAttributes($result);
		return true;
	}

	public function getAnswers() {
		if (is_array($this->answers) && count($this->answers)) return $this->answers;
		$db = Database::connection();
		$answers = $db->fetchAll("SELECT * FROM FormidableAnswers WHERE formID = ? AND answerSetID = ?", array($this->getFormID(), $this->getAnswerSetID()));
		if (is_array($answers) && count($answers) > 0) {
			foreach ($answers as $answer) {
				$this->answers[$answer['elementID']] = $answer;
			}
		}
		return $this->answers;
	}

	public function getAnswersByID($formID, $answerID){
		$db = Database::connection();
		$results = $db->fetchAll("SELECT * FROM FormidableAnswers WHERE formID = ? AND answerSetID = ?", array($formID, $answerID));

		return $results;
	}

	public function getAnswerByElementID($elementID) {
		$answers = $this->getAnswers();
		if (array_key_exists($elementID, $answers)) return unserialize($answers[$elementID]['answer_unformated']);
		return false;
	}

	public function save($params) {
		// Fetch answers from params...
		$answers = false;
		if (array_key_exists('answers', $params)) {
			$answers = $params['answers'];
			unset($params['answers']);
		}
		// Temp could be true, change to 1 if so.
		$params['temp'] = $params['temp']===true||$params['temp']==1?1:0;
		if (!$this->getAnswerSetID()) $result = $this->add($params);
		else $result = $this->update($params);
		if (!$result) return false;
		if ($answers && is_array($answers) && count($answers)) return $this->saveAnswers($answers);
		return true;
	}

	private function add($params) {
		$db = Database::connection();
		$db->insert('FormidableAnswerSets', $params);
		$answerSetID = $db->lastInsertId();
		if (empty($answerSetID)) return false;
		$this->load($answerSetID);
		return true;
	}

	private function update($params) {
		$db = Database::connection();
		$db->update('FormidableAnswerSets', $params, ['answerSetID' => $this->getAnswerSetID()]);
		$this->load($this->getAnswerSetID());
		return true;
	}

	public function delete() {
		$db = Database::connection();
		$db->delete('FormidableAnswers', ['answerSetID' => $this->getAnswerSetID()]);
		$db->delete('FormidableAnswerSets', ['answerSetID' => $this->getAnswerSetID()]);

		// Fire event
		$event = new Event();
		$event->setArgument('answerSetID', $this->getAnswerSetID());
		Events::dispatch('on_formidable_result_delete', $event);

		return true;
	}

	private function saveAnswers($answers){
		$db = Database::connection();
		if (!empty($answers) && is_array($answers) && count($answers)) {
			$db->delete('FormidableAnswers', ['answerSetID' => $this->getAnswerSetID()]);
			foreach ($answers as $answer) {
				$answer['answerSetID'] = $this->getAnswerSetID();
				$db->insert('FormidableAnswers', $answer);
				$answerID = $db->lastInsertId();
			}
		}
		return true;
	}

	public function updateAnswer($elementID, $answer = '') {
		$db = Database::connection();
		if (empty($elementID)) return false;
		$db->update('FormidableAnswers', $answer, ['answerSetID' => $this->getAnswerSetID(), 'elementID' => $elementID, 'formID' => $this->getFormID()]);
		return true;
	}

	public function clearAnswer($elementID) {
		$db = Database::connection();
		if (empty($elementID)) return false;
		$db->delete('FormidableAnswers', ['answerSetID' => $this->getAnswerSetID(), 'elementID' => $elementID, 'formID' => $this->getFormID()]);
		return true;
	}

	public function getIPAddress() {
		return $this->ip;
	}
	public function getBrowser() {
		return !empty($this->browser)?$this->browser:t('Unknown');
	}
	public function getPlatform() {
		return !empty($this->platform)?$this->platform:t('Unknown');
	}
	public function getResolution() {
		return !empty($this->resolution)?$this->resolution:t('Unknown');
	}
	public function getSubmissionDate() {
		return \Core::make('helper/date')->formatPrettyDateTime(strtotime($this->submitted));
	}

	public function getPageData($handle) {
		$p = $this->getCollection($this->collectionID);
		if (!is_object($p)) {
			return in_array($handle, ['collection_name', 'collection_url'])?t('Unknown or deleted page'):'';
		}
		if (strpos($handle, 'collection_ak_') !== false) {
			return $p->getAttribute(substr($handle, 14), 'display');
		}
		switch ($handle) {
			case 'collection_id':
				return $p->getCollectionID();
			break;
			case 'collection_name':
				return $p->getCollectionName();
			break;
			case 'collection_url':
				return t('<a href="%s" target="_blank">%s</a> (Page ID: %s)', \Core::make('helper/navigation')->getLinkToCollection($p), $p->getCollectionName(), $p->getCollectionID());
			break;
			case 'collection_handle':
				return $p->getCollectionHandle();
			break;
			case 'collection_added':
				return $p->getCollectionDateAdded();
			break;
			case 'collection_modified':
				return $p->getCollectionDateLastModified();
			break;
		}
		return '';
	}

	public function getUserData($handle) {
		$u = $this->getUser($this->userID);
		if (!is_object($u)) {
			return in_array($handle, array('user_name', 'user_url'))?t('Unknown or deleted user'):'';
		}
		if (strpos($handle, 'user_ak_') !== false) {
			return $u->getAttribute(substr($handle, 8), 'display');
		}
		switch ($handle) {
			case 'user_id':
				return $u->getUserID;
			break;
			case 'user_name':
				return $u->getUserName();
			break;
			case 'user_url':
				return t('<a href="%s?uID=%s" target="_blank">%s</a> (User ID: %s)', \Core::make('helper/navigation')->getLinkToCollection(Page::getByPath('/dashboard/users/search')), $u->getUserID(), $u->getUserName(), $u->getUserID());
			break;
		}
		return '';
	}

	public function getCurrentLocale() {
		return !empty($this->locale)?Localization::getLanguageDescription($this->locale):t('Unknown');
	}
}
