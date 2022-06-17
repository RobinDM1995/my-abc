<?php
namespace Concrete\Package\FormidableFull\Src;

use \Concrete\Package\FormidableFull\Src\Formidable\Form;
use \Concrete\Package\FormidableFull\Src\Formidable\Element;
use \Concrete\Package\FormidableFull\Src\Formidable\Search\SearchProvider;
use \Concrete\Core\File\Service\File;
use \Concrete\Core\Package\Package;
use \Concrete\Core\User\User;
use \Concrete\Core\User\UserInfo;
use \Concrete\Core\Support\Facade\Url;
use \Concrete\Core\Page\Page;
use \Concrete\Core\Support\Facade\Database;
use Core;
use Localization;
use Symfony\Component\HttpFoundation\Session\Session;
use CollectionAttributeKey;
use UserAttributeKey;
use Request;

class Formidable {

	private $pkgHandle = 'formidable_full';

	private $javascript = [];
	private $jquery = [];

	private $result = [];

	public function setAttribute($key, $value, $add = false) {
		if ($key == 'label_import') $key = 'handle';
		if (!is_array($value)) $value = stripslashes($value);
		if ($add) $this->{$key}[] = $value;
		else $this->{$key} = $value;
	}

	public function setAttributes($attributes) {
		if (is_array($attributes) && count($attributes)) {
			foreach ($attributes as $key => $value) {
				$this->setAttribute($key, $value);
			}
		}
	}

	public function addJavascript($script, $jquery = true) {
		$javascript = $this->jquery;
		if (!$jquery) $javascript = $this->javascript;

		// Block double javascript content...
		foreach ((array)$javascript as $js) {
			if (md5($js) == md5($script)) return false;
		}

		if (!$jquery) $this->javascript[] = $script;
		else $this->jquery[] = $script;
	}

	public function getJavascript() {
       	return is_array($this->javascript) && count($this->javascript)?\JShrink\Minifier::minify(@implode(PHP_EOL, $this->javascript)):false;
	}
	public function getJquery() {
       	return is_array($this->jquery) && count($this->jquery)?\JShrink\Minifier::minify(@implode(PHP_EOL, $this->jquery)):false;
	}

	public static function getFirstForm() {
		$db = Database::connection();
		$data = $db->fetchColumn("SELECT formID FROM FormidableForms ORDER BY label ASC LIMIT 1");
		if ($data) {
			$form = Form::getByID($data);
			return $form;
		}
		return false;
	}

	public static function getAllForms() {
		$db = Database::connection();
		$r = $db->fetchAll("SELECT formID FROM FormidableForms ORDER BY label ASC");
		foreach((array)$r as $form) {
			$f = Form::getByID($form['formID']);
			if ($f && $f->hasPermissions('result')) $forms[$f->getFormID()] = $f->getLabel();
		}
		return $forms;
	}

	public static function getAdvancedElements() {
		$advanced = [
			['handle' => 'form_name', 'label' => 'Form name', 'type' => 'Text', 'callback' => 'getLabel'],
			['handle' => 'answerset_id', 'label' => 'AnswersetID', 'comment' => '(unique ID)', 'type' => 'Integer', 'callback' => 'getAnswerSetID'],
			['handle' => 'user_id', 'label' => 'Username', 'comment' => '(link)', 'type' => 'URL', 'callback' => 'getUserName'],
			['handle' => 'ip', 'label' => 'IP Address', 'type' => 'Text', 'callback' => 'getIPAddress'],
			['handle' => 'browser', 'label' => 'Browser', 'type' => 'Text', 'callback' => 'getBrowser'],
			['handle' => 'platform', 'label' => 'Platform', 'type' => 'Text', 'callback' => 'getPlatform'],
			['handle' => 'resolution', 'label' => 'Screen resolution', 'type' => 'Text', 'callback' => 'getResolution'],
			['handle' => 'locale', 'label' => 'Localization', 'type' => 'Text', 'callback' => 'getLocale'],
			['handle' => 'submitted', 'label' => 'Submitted on', 'comment' => '(mm/dd/yyyy hh:mm:ss)', 'type' => 'Date/Time', 'callback' => 'getSubmissionDate']
		];
		return $advanced;
	}

	public static function getPageVariable() {
		$attributes = [
			['handle' => 'collection_id', 'label' => 'ID', 'type' => 'Integer', 'callback' => 'getPageData'],
			['handle' => 'collection_url', 'label' => 'URL', 'comment' => '(link)', 'type' => 'URL', 'callback' => 'getPageData'],
			['handle' => 'collection_name', 'label' => 'Name', 'type' => 'Text', 'callback' => 'getPageData'],
			['handle' => 'collection_added', 'label' => 'Date Added', 'type' => 'Date', 'callback' => 'getPageData'],
			['handle' => 'collection_modified', 'label' => 'Date Modified', 'type' => 'Date', 'callback' => 'getPageData'],
		];
		$attribs = CollectionAttributeKey::getList();
		if (is_array($attribs) && count($attribs)) {
			foreach ($attribs as $at) {
				$attributes[] = ['handle' => 'collection_ak_'.$at->getAttributeKeyHandle(), 'label' => $at->getAttributeKeyName(), 'type' => $at->getAttributeTypeHandle(), 'callback' => 'getPageData'];
			}
		}
		return $attributes;
	}

	public static function getUserVariable() {
		$attributes = [
			['handle' => 'user_id', 'label' => 'ID', 'type' => 'Integer', 'callback' => 'getUserData'],
			['handle' => 'user_url', 'label' => 'URL', 'comment' => '(link)', 'type' => 'URL', 'callback' => 'getUserData'],
			['handle' => 'user_name', 'label' => 'Name', 'type' => 'Text', 'callback' => 'getUserData'],
		];
		$attribs = UserAttributeKey::getList();
		if (is_array($attribs) && count($attribs)) {
			foreach ($attribs as $at) {
				$attributes[] = ['handle' => 'user_ak_'.$at->getAttributeKeyHandle(), 'label' => $at->getAttributeKeyName(), 'type' => $at->getAttributeTypeHandle(), 'callback' => 'getUserData'];
			}
		}
		return $attributes;
	}

	public function post($key = null) {
		$post = Request::post();
		if ($key == null) return $post;
		if (array_key_exists($key, (array)($post))) return (is_string($post[$key])) ? trim($post[$key]) : $post[$key];
	}

	public function getFormID() {
		return is_numeric($this->formID)?$this->formID:false;
	}
	public function getAnswerSetID() {
		if (!empty($this->answerSetID)) return $this->answerSetID;
		$session = Core::make('app')->make('session');
		$answerSetID = $session->get('answerSetID'.$this->getFormID());
		if (!empty($answerSetID)) $this->answerSetID = $answerSetID;
		return !empty($this->answerSetID)?intval($this->answerSetID):false;
	}

	public function getElementByID($elementID) {
		if (isset($this->elements) && is_object($this->elements[$elementID])) return $this->elements[$elementID];
		else {
			$element = Element::getByID($elementID);
			if (is_object($element) && $element->getFormID() == $this->getFormID()) return $element;
		}
		return false;
	}
	public function getAttributes() {
		return isset($this->attributes)?array_filter($this->attributes):[];
	}
	public function getAttribute($key) {
		return array_key_exists($key, (array)$this->attributes)?$this->attributes[$key]:false;
	}

	public function getDependencyProperty($key) {
		return array_key_exists($key, (array)$this->dependency)?$this->dependency[$key]:false;
	}

	public function getDependency($key = null) {
		if ($key == null) return $this->dependencies;
		if (array_key_exists($key, (array)($this->dependencies))) return $this->dependencies[$key];
		return false;
	}
	public function getDependencyRule($rule) {
		$dependencies = $this->getDependency('raw');
		if (array_key_exists($rule, (array)($dependencies))) return $dependencies[$rule];
		return false;
	}

	public function setResult($result) {
		$this->result = $result;
	}
	public function getResult() {
		if (!is_object($this->result)) return false;
		$answers = $this->result->getAnswers();
		return is_array($answers)&&count($answers)?$this->result:false;
	}

	public static function getNextSort($type, $formID) {
		switch ($type) {
			case 'layout': 	$table = 'FormidableFormLayouts'; 	break;
			case 'element': $table = 'FormidableFormElements'; 	break;
			default: 		$table = false; 					break;
		}
		if (!$table) return 0;

		$db = Database::connection();
		$sort = 0;
		$r = $db->getOne("SELECT MAX(sort) AS sort FROM `".$table."` WHERE formID = ?", array($formID));
		$sort = intval($r) + 1;
		return $sort;
	}

	public function getAvailableElements() {
		$pkg = Package::getByHandle($this->pkgHandle);
		$elements = [];
		$files = File::getDirectoryContents($pkg->getPackagePath().'/src/Formidable/Element/');
		if (is_array($files) && count($files)) {
			foreach($files as $file) {
				$element = $this->loadElement(pathinfo($file, PATHINFO_FILENAME));
				if(is_object($element)) {
					$group = $element->getElementGroup();
					if (!$group) $group = t('Custom Elements');
					$elements[$group][$element->getElementText()] = $element;
				}
			}
		}
		return $elements;
	}

	public function loadElement($type, $id = 0) {
		$type = ucfirst($type);
		$pkg = Package::getByHandle($this->pkgHandle);
		if(!file_exists($pkg->getPackagePath().'/src/Formidable/Element/'.$type.'.php')) return t('Type of element not supported');

		// Let's all hate PRCS4 classnaming:
		$class = "\Concrete\Package\FormidableFull\Src\Formidable\Element\\".$type;
		$element = new $class();
		if ($id != 0) $element->load($id);
		return $element;
	}

	public static function hasAddPermissions() {
		$ff = new Formidable();
		$add = $ff->getConfig('permissions.add_form');
		if ($add) {
			$permissions = $ff->getConfig('permissions.add_form.groups');
			if (!is_array($permissions) || !count($permissions)) return false;
			elseif (in_array(0, $permissions)) return true;
			else {
				$groups = [];
				$ui = Formidable::getUser();
				if ($ui) {
					if ($ui->getUserID() == USER_SUPER_ID) return true;
					else $groups = $ui->getUserObject()->getUserGroups();
				}
				if (is_array($groups) && count($groups)) return count(array_intersect($groups, (array)$permissions))>0;
			}
		}
		return true;
	}

	public function getConfig($key) {
		$pkg = Package::getByHandle($this->pkgHandle);
		return $pkg->getFileConfig()->get($key);
	}

	public static function clearColumnSet($formID) {
		$provider = new SearchProvider($formID, new Session());
		$provider->clearSessionCurrentQuery();

		$u = new User();
		$fldc = $u->config('FORMIDABLE_LIST_DEFAULT_COLUMNS_'.$formID);
		if ($fldc != '') {
			$u->saveConfig('FORMIDABLE_LIST_DEFAULT_COLUMNS_'.$formID, '');
		}
	}

	public static function getCollection($cID = 0) {
		$c = Page::getByID($cID);
		if (!is_object($c) || intval($c->getCollectionID()) == 0) $c = Page::getCurrentPage();
		if (!is_object($c) || intval($c->getCollectionID()) == 0) return false;
		return $c;
	}

	public static function getUser($userID = 0) {
		if (empty($userID)) {
			$u = new User();
			if (!is_object($u)) return false;
			$userID = $u->getUserID();
		}
		$ui = UserInfo::getByID($userID);
		if (!is_object($ui)) return false;
		return $ui;
	}

	public static function getIP() {
		$ip = $_SERVER['REMOTE_ADDR'];
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		return $ip;
	}

	public static function getBrowserInfo() {

		$u_agent	= $_SERVER['HTTP_USER_AGENT'];
		$bname		= t('Unknown');
		$platform 	= t('Unknown');
		$version	= "";

		if (preg_match('/linux/i', $u_agent)) $platform = 'Linux';
		elseif (preg_match('/iPad/i', $u_agent)) $platform = 'iPad';
		elseif (preg_match('/iPod/i', $u_agent)) $platform = 'iPod';
		elseif (preg_match('/iPhone/i', $u_agent)) $platform = 'iPhone';
		elseif (preg_match('/android/i', $u_agent)) $platform = 'android';
		elseif (preg_match('/macintosh|mac os x/i', $u_agent)) $platform = 'Mac';
		elseif (preg_match('/windows|win32/i', $u_agent)) $platform = 'Windows';

		if ($platform == 'Windows') {
			if (preg_match('/Win16/i', $u_agent)) $platform .= ' 3.11';
			elseif (preg_match('/(Windows 95)|(Win95)|(Windows_95)/i', $u_agent)) $platform .= ' 95';
			elseif (preg_match('/(Windows 98)|(Win98)/i', $u_agent)) $platform .= ' 98';
			elseif (preg_match('/(Windows NT 5.0)|(Windows 2000)/i', $u_agent)) $platform .= ' 2000';
			elseif (preg_match('/(Windows NT 5.1)|(Windows XP)/i', $u_agent)) $platform .= ' XP';
			elseif (preg_match('/Windows NT 5.2/i', $u_agent)) $platform .= ' Server 2003';
			elseif (preg_match('/Windows NT 6.0/i', $u_agent)) $platform .= ' Vista';
			elseif (preg_match('/Windows NT 6.1/i', $u_agent)) $platform .= ' 7';
			elseif (preg_match('/Windows NT 6.2/i', $u_agent)) $platform .= ' 8';
			elseif (preg_match('/Windows NT 10.0/i', $u_agent)) $platform .= ' 10';
			elseif (preg_match('/(Windows NT 4.0)|(WinNT4.0)|(WinNT)|(Windows NT)/i', $u_agent)) $platform .= ' NT 4.0';
			elseif (preg_match('/Windows ME/i', $u_agent)) $platform .= ' ME';
		}

		if (preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) {
			$bname = 'Internet Explorer';
			$ub = "MSIE";
		}
		elseif(preg_match('/Edge/i',$u_agent)) {
			$bname = 'Microsoft Edge';
			$ub = "Edge";
		}
		elseif(preg_match('/Firefox/i',$u_agent)) {
			$bname = 'Mozilla Firefox';
			$ub = "Firefox";
		}
		elseif(preg_match('/Chrome/i',$u_agent)) {
			$bname = 'Google Chrome';
			$ub = "Chrome";
		}
		elseif(preg_match('/Safari/i',$u_agent)) {
			$bname = 'Apple Safari';
			$ub = "Safari";
		}
		elseif(preg_match('/Opera/i',$u_agent)) {
			$bname = 'Opera';
			$ub = "Opera";
		}
		elseif(preg_match('/Netscape/i',$u_agent)) {
			$bname = 'Netscape';
			$ub = "Netscape";
		}

		// finally get the correct version number
		$known = ['Version', $ub, 'other'];
		$pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
		if (!@preg_match_all($pattern, $u_agent, $matches)) {
			// we have no matching number just continue
		}

		$i = is_array($matches['browser']) && count($matches['browser']);
		if ($i != 1) {
			if (strripos($u_agent,"Version") < strripos($u_agent,$ub)) $version= $matches['version'][0];
			else $version= $matches['version'][1];
		}
		else $version= $matches['version'][0];
		if ($version==null || $version=="") $version="?";

		return [
			'userAgent' => $u_agent,
			'name'      => $bname,
			'version'   => $version,
			'platform'  => $platform,
			'pattern'    => $pattern
		];
	}

	public static function setLocale($locale) {
		$l = Localization::getInstance();
		$l->setLocale($locale);
		return true;
	}

	public static function getLocale() {
		return Localization::activeLocale();
	}

	public function __call($nm, $a) {
		if (substr($nm, 0, 3) == 'get' && substr($nm, 0, 5) != 'getBy') {
			if (!method_exists($this, $nm)) {
		    	$txt = Core::make('helper/text');
		    	$variable = $txt->uncamelcase(substr($nm, 3));
		    	if (isset($this->{$variable})) return $this->{$variable};
		    	if (substr($variable, -4) == '_i_d') return $this->{substr($variable, 0, -4).'_id'};
		    	return false;
	    	}
	    }
    }
}
