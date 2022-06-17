<?php
namespace Concrete\Package\FormidableFull\Block\Formidable;

use \Concrete\Core\Block\BlockController;
use \Concrete\Core\Support\Facade\Url;
use \Concrete\Core\Page\Page;
use Events;
use Core;
use Localization;
use \Concrete\Core\Asset\AssetList as AssetList;
use \Concrete\Core\Validation\CSRF\Token;
use \Concrete\Package\FormidableFull\Src\Formidable;
use \Concrete\Package\FormidableFull\Src\Formidable\Form;

class Controller extends BlockController {

	protected $pkgHandle = 'formidable_full';

	protected $btInterfaceWidth = 500;
	protected $btInterfaceHeight = 500;
	protected $btTable = 'btFormidable';

	protected $btCacheBlockRecord = false;
	protected $btCacheBlockOutput = false;
	protected $btCacheBlockOutputOnPost = false;
	protected $btCacheBlockOutputForRegisteredUsers = false;
	protected $btCacheBlockOutputLifetime = 300;

	protected $btDefaultSet = 'form';

	public $view_type = '';

	public function getBlockTypeDescription() {
		return t("Adds a Formidable Form to your page.");
	}

	public function getBlockTypeName() {
		return t("Formidable");
	}

	public function getJavaScriptStrings() {
		return array(
			'form-required' => t('You must select a form.')
		);
	}

	public function on_start() {
	    parent::on_start();
		$this->set('forms', Formidable::getAllForms());
	}

	public function add() {
		$this->requireAsset('ace');
	}

	public function edit() {
		$this->options = unserialize($this->options);
		$this->requireAsset('ace');
	}

	public function save($args) {
		$args = array(
			'formID' => intval($this->post('formID')),
			'options' => serialize($this->post('options'))
		);
		parent::save($args);
	}

	public function view() {

		$this->options = unserialize((string)$this->options);

		$this->requireAsset('javascript', 'jquery');
		$this->requireAsset('javascript', 'jquery/ui');
		$this->requireAsset('javascript', 'bootstrap/tooltip');
		$this->requireAsset('css', 'bootstrap/*');
		$this->requireAsset('css', 'jquery/ui');
		$this->requireAsset('css', 'core/frontend/errors');

		$this->requireAsset('javascript', 'formidable/top');
		$this->requireAsset('javascript', 'formidable/placeholder');
		$this->requireAsset('javascript', 'formidable/mask');
		$this->requireAsset('javascript', 'formidable/countable');
		$this->requireAsset('javascript', 'formidable/timepicker');
		$this->requireAsset('javascript', 'formidable/dropzone');
		$this->requireAsset('javascript', 'formidable/slider');
		$this->requireAsset('javascript', 'formidable/rating');
		$this->requireAsset('javascript', 'formidable/signature');
		$this->requireAsset('javascript', 'formidable/easing');
		$this->requireAsset('javascript', 'formidable');

		$this->requireAsset('selectize');

		$c = Page::getCurrentPage();

		if (is_object($this->form)) $form = $this->form;
		else {
			if (!$this->formID) return false;
			$form = Form::getByID($this->formID);
		}
		if (!is_object($form)) return false;

		if (isset($this->view_type)) $this->set('view_type', $this->view_type);

		// When view_type is set, skip limits and scheduling...
		if (empty($this->view_type)) {
			if ($form->checkLimits()) {
				if ($form->getAttribute('limits_redirect')) {
					$p = Page::getByID($form->getAttribute('limits_redirect_page'));
					if (is_object($p)) {
						$this->redirect($p->getCollectionPath());
						exit();
					}
				}
				$this->set('limits', $form->getAttribute('limits_redirect_content'));
			}

			if ($form->checkSchedule()) {
				if ($form->getAttribute('schedule_redirect')) {
					$p = Page::getByID($form->getAttribute('schedule_redirect_page'));
					if (is_object($p)) {
						$this->redirect($p->getCollectionPath());
						exit();
					}
				}
				$this->set('schedule', $form->getAttribute('schedule_redirect_content'));
			}
		}

		$form->setAttribute('block_id', $this->bID);

		$cID = $this->post('cID');
		if (is_object($c)) $cID = $c->getCollectionID();
		$form->setAttribute('collection_id', $cID);

		$valt = new Token();
		$form->setAttribute('token', $valt->generate('formidable_form'));

		// Generate form layout and elements
		$form->generate();

		// Initialize jQuery and Javascript
		// The jquery is currently loaded in the view.php or \templates\*\view.php
		$al = AssetList::getInstance();
		$al->register('javascript', 'formidable/bottom'.$form->getFormID(), URL::to('/formidable/dialog/formidable/bottomjs', $form->getFormID()), array('local' => false, 'minify' => true, 'combine' => true));
		$this->requireAsset('javascript', 'formidable/bottom'.$form->getFormID());

		$options = [];
		if (isset($this->options['error_messages_on_top'])) $options[] = "'error_messages_on_top': ".(intval($this->options['error_messages_on_top'])==1?'true':'false');
		if (!empty($this->options['error_messages_on_top_class'])) $options[] = "'error_messages_on_top_class': '".$this->options['error_messages_on_top_class']."'";
		if (!empty($this->options['warning_messages_class'])) $options[] = "'warning_messages_class': '".$this->options['warning_messages_class']."'";
		if (isset($this->options['error_messages_beneath_field'])) $options[] = "'error_messages_beneath_field': ".(intval($this->options['error_messages_beneath_field'])==1?'true':'false');
		if (!empty($this->options['error_messages_beneath_field_class'])) $options[] = "'error_messages_beneath_field_class': '".$this->options['error_messages_beneath_field_class']."'";
		if (!empty($this->options['success_messages_class'])) $options[] = "'success_messages_class': '".$this->options['success_messages_class']."'";
		if (isset($this->options['remove_form_on_success'])) $options[] = "'remove_form_on_success': ".(intval($this->options['remove_form_on_success'])==1?'true':'false');
		if (isset($this->options['step_progress_bar'])) $options[] = "'step_progress_bar': ".(intval($this->options['step_progress_bar'])==1?'true':'false');
		if (!empty($this->options['step_progress_bar_selector'])) $options[] = "'step_progress_bar_selector': '".$this->options['step_progress_bar_selector']."'";
		if (isset($this->options['animate_step'])) $options[] = "'animate_step': ".(intval($this->options['animate_step'])==1?'true':'false');
		if (!empty($this->options['animate_step_easing'])) $options[] = "'animate_step_easing': '".$this->options['animate_step_easing']."'";
		if (!empty($this->options['animate_step_duration'])) $options[] = "'animate_step_duration': '".$this->options['animate_step_duration']."'";
		if (isset($this->options['hide_steps_after_submission'])) $options[] = "'hide_steps_after_submission': ".(intval($this->options['hide_steps_after_submission'])==1?'true':'false');
		if (!empty($this->options['errorCallback'])) $options[] = "'errorCallback': '".$this->options['errorCallback']."'";
		if (!empty($this->options['successCallback'])) $options[] = "'successCallback': '".$this->options['successCallback']."'";

		$script  = "var formObj".$form->getFormID()." = $('form[id=\"ff_".$form->getFormID()."\"]');";
		$script .= "var ff_".$form->getFormID()." = formObj".$form->getFormID().".formidable({ ".@implode(', ', $options)." });";
		$script .= $form->getJquery();
		$script .= "ff_".$form->getFormID().".check_depencencies();";

		$al->register('javascript-inline', 'formidable/init'.$form->getFormID(), $script);
		$this->requireAsset('javascript-inline', 'formidable/init'.$form->getFormID());


		// Fire event
		$event = new \Symfony\Component\EventDispatcher\GenericEvent();
		$event->setArgument('form', $form);
		Events::dispatch('on_formidable_load', $event);

		$this->set('f', $form);
		if (isset($this->view_type)) $this->set('view_type', $this->view_type);

	}
}
