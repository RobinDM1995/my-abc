<?php
namespace Concrete\Package\FormidableFull\Controller\SinglePage\Dashboard\Formidable;

use \Concrete\Package\FormidableFull\Controller\Element\Search\Header;
use \Concrete\Core\Page\Controller\DashboardPageController;
use \Concrete\Package\FormidableFull\Src\Formidable;
use \Concrete\Package\FormidableFull\Src\Formidable\Form;
use \Concrete\Core\Support\Facade\Url;
use Core;

class Results extends DashboardPageController
{
    public $helpers = array('form');

    private $form = null;

    public function __construct($c) {
		parent::__construct($c);

		$session = Core::make('app')->make('session');
		$formID = $session->get('formidableFormID');

		if (intval($this->get('formID')) != 0) $formID = intval($this->get('formID'));

        $f = Form::getByID($formID);
		if (!is_object($f)) $f = Formidable::getFirstForm();
        if (!is_object($f)) return false;

        $this->form = $f;

		$_REQUEST['formID'] = $f->getFormID();
		$session->set('formidableFormID', $f->getFormID());
    }

    public function view()
    {
        $header = new Header();
        $this->set('headerMenu', $header);

        $this->requireAsset('javascript', 'formidable/dashboard/results');
        $this->requireAsset('css', 'formidable/dashboard');

        if (!is_object($this->form)) return false;

        // Check permissions now!
        if (!$this->form->hasPermissions('results'))  $this->set('no_permissions', true);
        else {
            $search = $this->app->make('\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Results\Search');
            $result = $search->getCurrentSearchObject();
            $list = $result->getItemListObject();
            $this->set('result', $result);

            $script = "";
            $result = json_encode($result->getJSONObject());
            $script .=  "function FormidableResultWaitWhileLoading() {
                            if ($.isFunction(ConcreteAjaxSearch) && $.isFunction(FormidableResultLoader)) {
                                FormidableResultLoader();
                                formidable_results_list = $('#ccm-dashboard-content').concreteFormidableResult({result: " . $result . "});
                            }
                            else setTimeout(function() { FormidableResultWaitWhileLoading(); }, 50);
                        };
                        FormidableResultWaitWhileLoading();";
            $script .= "$('.ccm-header-search-form-select select').on('change', function() { window.location.href = '".URL::to('/dashboard/formidable/results/')."?formID='+$(this).val(); });";

            if (!empty($script)) $this->addFooterItem("<script type=\"text/javascript\">var formidable_results_list = null; $(function() {".$script."});</script>");
        }
    }
}
