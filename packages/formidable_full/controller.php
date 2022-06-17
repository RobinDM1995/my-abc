<?php
namespace Concrete\Package\FormidableFull;

use \Concrete\Core\Package\Package;
use BlockType;
use SinglePage;
use \Concrete\Core\Job\Job as Job;
use Concrete\Package\FormidableFull\Src\Formidable as Formidable;
use AssetList;
use Concrete\Core\Attribute\Type as AttributeType;
use Concrete\Core\Attribute\Set as AttributeSet;
use Concrete\Core\Entity\Attribute\Key\PageKey;
use Concrete\Core\Entity\Attribute\Key\UserKey;
use CollectionAttributeKey;
use UserAttributeKey;
use \Concrete\Core\Page\Page;
use Route;
use \Concrete\Core\Support\Facade\Database;
use \Concrete\Core\Support\Facade\Url;
use Request;
use Core;
use Symfony\Component\ClassLoader\Psr4ClassLoader as SymfonyClassLoader;
use Events;
use \Concrete\Core\Support\Facade\Config;

class Controller extends Package {

    protected $pkgHandle = 'formidable_full';
    protected $appVersionRequired = '8.4.0';
    protected $pkgVersion = '2.1.5';

    protected $singlePages = array(
        array('/dashboard/formidable'),
        array('/dashboard/formidable/forms', false),
        array('/dashboard/formidable/forms/elements', true),
        array('/dashboard/formidable/forms/mailings', true),
        array('/dashboard/formidable/results', false),
        array('/dashboard/formidable/templates', false),
        array('/dashboard/reports/formidable', false)
    );

    protected $jobs = array(
        'clean_formidable',
    );

    protected $blocks = array(
        'formidable'
    );

    protected $configs = array(
        'permissions.add_form' => false,
        // Add groupID's here
        // If no groupID's are added all groups will be blocked
        'permissions.add_form.groups' => array(
            0, // All users
            //3, // Administrators
        )
    );

    public function getPackageDescription() {
        return t('Create awesome forms with a few clicks!');
    }

    public function getPackageName() {
        return t('Formidable (Full Version)');
    }

    public function on_start() {

        $strictLoader = new SymfonyClassLoader();
        $strictLoader->addPrefix('\Concrete\Package\FormidableFull\Src', DIR_PACKAGES . '/formidable_full/src');
        $strictLoader->register();

        $register = array(
            '/formidable/dialog/dashboard/forms/preview' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Forms\Preview::view',
            '/formidable/dialog/dashboard/forms/preview/result' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Forms\Preview::result',
            '/formidable/dialog/dashboard/forms/form_list' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Forms\FormList::view',
            '/formidable/dialog/dashboard/forms/element_list' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Forms\ElementList::view',
            '/formidable/dialog/dashboard/forms/mailing_list' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Forms\MailingList::view',
            '/formidable/dialog/dashboard/forms/dialog/delete' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Forms\Dialog::delete',

            '/formidable/dialog/dashboard/forms/tools/duplicate' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Forms\Tools::duplicate',
            '/formidable/dialog/dashboard/forms/tools/delete' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Forms\Tools::delete',
            '/formidable/dialog/dashboard/forms/tools/handle' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Forms\Tools::handle',

            '/formidable/dialog/dashboard/elements/dialog' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Elements\Dialog::view',
            '/formidable/dialog/dashboard/elements/dialog/select' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Elements\Dialog::select',
            '/formidable/dialog/dashboard/elements/dialog/delete' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Elements\Dialog::delete',
            '/formidable/dialog/dashboard/elements/dialog/bulk' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Elements\Dialog::bulk',

            '/formidable/dialog/dashboard/elements/tools/save' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Elements\Tools::save',
            '/formidable/dialog/dashboard/elements/tools/delete' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Elements\Tools::delete',
            '/formidable/dialog/dashboard/elements/tools/duplicate' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Elements\Tools::duplicate',
            '/formidable/dialog/dashboard/elements/tools/order' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Elements\Tools::order',
            '/formidable/dialog/dashboard/elements/tools/validate' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Elements\Tools::validate',
            '/formidable/dialog/dashboard/elements/tools/options' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Elements\Tools::options',
            '/formidable/dialog/dashboard/elements/tools/bulk' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Elements\Tools::bulk',

            '/formidable/dialog/dashboard/elements/dependency/add' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Elements\Dependency::add',
            '/formidable/dialog/dashboard/elements/dependency/action' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Elements\Dependency::action',
            '/formidable/dialog/dashboard/elements/dependency/element' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Elements\Dependency::element',
            '/formidable/dialog/dashboard/elements/dependency/delete' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Elements\Dependency::delete',

            '/formidable/dialog/dashboard/layout/dialog' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Layouts\Dialog::view',
            '/formidable/dialog/dashboard/layout/dialog/select' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Layouts\Dialog::select',
            '/formidable/dialog/dashboard/layout/dialog/delete' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Layouts\Dialog::delete',
            '/formidable/dialog/dashboard/layout/dialog/duplicate' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Layouts\Dialog::duplicate',

            '/formidable/dialog/dashboard/layout/tools/save' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Layouts\Tools::save',
            '/formidable/dialog/dashboard/layout/tools/list' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Layouts\Tools::list',
            '/formidable/dialog/dashboard/layout/tools/order' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Layouts\Tools::order',
            '/formidable/dialog/dashboard/layout/tools/delete' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Layouts\Tools::delete',
            '/formidable/dialog/dashboard/layout/tools/duplicate' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Layouts\Tools::duplicate',

            '/formidable/dialog/dashboard/results/delete' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Results\Dialog::delete',
            '/formidable/dialog/dashboard/results/delete/submit' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Results\Tools::delete',
            '/formidable/dialog/dashboard/results/resend' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Results\Dialog::resend',
            '/formidable/dialog/dashboard/results/resend/submit' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Results\Tools::resend',
            '/formidable/dialog/dashboard/results/customize' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Results\Customize::view',
            '/formidable/dialog/dashboard/results/customize/submit' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Results\Customize::submit',
            '/formidable/dialog/dashboard/results/csv' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Results\Tools::csv',
            '/formidable/dialog/dashboard/results/xls' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Results\Tools::xls',
            '/formidable/dialog/dashboard/results/delete_all' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Results\Dialog::delete_all',
            '/formidable/dialog/dashboard/results/delete_all/submit' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Results\Tools::delete_all',
            '/formidable/dialog/dashboard/results/tools/reload' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Results\Tools::reload',

            '/formidable/dialog/dashboard/results/search/basic' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Results\Search::searchBasic',
            '/formidable/dialog/dashboard/results/search/current' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Results\Search::searchCurrent',
            '/formidable/dialog/dashboard/results/search/preset/{presetID}' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Results\Search::searchPreset',
            '/formidable/dialog/dashboard/results/search/clear' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Results\Search::clearSearch',

            '/formidable/dialog/dashboard/results/search/advanced_search' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Results\AdvancedSearch::View',
            '/formidable/dialog/dashboard/results/search/advanced_search/add_field' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Results\AdvancedSearch::addField',
            '/formidable/dialog/dashboard/results/search/advanced_search/submit' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Results\AdvancedSearch::submit',
            '/formidable/dialog/dashboard/results/search/advanced_search/save_preset' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Results\AdvancedSearch::savePreset',

            '/formidable/dialog/dashboard/mailings/tools/save' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Mailings\Tools::save',
            '/formidable/dialog/dashboard/mailings/tools/duplicate' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Mailings\Tools::duplicate',
            '/formidable/dialog/dashboard/mailings/tools/validate' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Mailings\Tools::validate',
            '/formidable/dialog/dashboard/mailings/tools/delete' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Mailings\Tools::delete',

            '/formidable/dialog/dashboard/mailings/dependency/add' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Mailings\Dependency::add',
            '/formidable/dialog/dashboard/mailings/dependency/action' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Mailings\Dependency::action',
            '/formidable/dialog/dashboard/mailings/dependency/element' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Mailings\Dependency::element',
            '/formidable/dialog/dashboard/mailings/dependency/delete' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Mailings\Dependency::delete',

            '/formidable/dialog/dashboard/mailings/dialog' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Mailings\Dialog::view',
            '/formidable/dialog/dashboard/mailings/dialog/delete' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Mailings\Dialog::delete',

            '/formidable/dialog/dashboard/templates/preview' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Templates\Preview::view',
            '/formidable/dialog/dashboard/templates/template_list' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Templates\TemplateList::view',
            '/formidable/dialog/dashboard/templates/dialog/delete' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Templates\Dialog::delete',

            '/formidable/dialog/dashboard/templates/tools/duplicate' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Templates\Tools::duplicate',
            '/formidable/dialog/dashboard/templates/tools/delete' => '\Concrete\Package\FormidableFull\Controller\Dialog\Dashboard\Templates\Tools::delete',

            '/formidable/dialog/formidable' => '\Concrete\Package\FormidableFull\Controller\Dialog\Formidable::view',

            '/formidable/dialog/formidable/topjs' => '\Concrete\Package\FormidableFull\Controller\Dialog\Formidable::topJS',
            '/formidable/dialog/formidable/bottomjs/{formID}' => '\Concrete\Package\FormidableFull\Controller\Dialog\Formidable::bottomJS'
        );

        if (is_array($register) && count($register)) {
            foreach ($register as $path => $controller) {
                Route::register($path, $controller);
            }
        }

        $al = AssetList::getInstance();
        $token = Core::make('token');

        $slash = '/';
        if (Config::get('concrete.seo.trailing_slash')) $slash = '';

        $script = "var edit_content = '".t('Edit content')."';
        var add_content = '".t('Add content')."';
        var changed_values = '".t('You have made some changes to the Form Properties. Are you sure you want to discard these changes?')."';
        var formidable_security_token_form = '".$token->generate('formidable_form')."';
        var list_url = '".URL::to('/formidable/dialog/dashboard/forms/form_list').$slash."';
        var dialog_url = '".URL::to('/formidable/dialog/dashboard/forms/dialog').$slash."';
        var tools_url = '".URL::to('/formidable/dialog/dashboard/forms/tools').$slash."';
        var title_message_delete = '".t('Delete Formidable Form')."'
        $(function() {
            ccmFormidableLoadForms();
        });";
        $al->register('javascript-inline', 'formidable/inline/dashboard/forms/top', $script, array('minify' => true, 'combine' => true));

        $script = "var formidable_security_token_element = '".$token->generate('formidable_element')."';
        var formidable_security_token_mailing = '".$token->generate('formidable_mailing')."';
        var formidable_security_token_dependency = '".$token->generate('formidable_dependency')."';
        var formidable_security_token_layout = '".$token->generate('formidable_layout')."';
        var option_counter = 10000;
        var list_url = '".URL::to('/formidable/dialog/dashboard/forms/element_list').$slash."';
        var dialog_url = '".URL::to('/formidable/dialog/dashboard/elements/dialog').$slash."';
        var tools_url = '".URL::to('/formidable/dialog/dashboard/elements/tools').$slash."';
        var dependency_url = '".URL::to('/formidable/dialog/dashboard/elements/dependency').$slash."';
        var layout_dialog_url = '".URL::to('/formidable/dialog/dashboard/layout/dialog').$slash."';
        var layout_tools_url = '".URL::to('/formidable/dialog/dashboard/layout/tools').$slash."';
        var placeholder_value = '".t('Value')."';
        var placeholder_option = '".t('Name')."';
        var element_message_add = '".t('Add element to Formidable Form')."';
        var element_message_edit = '".t('Edit element on Formidable Form')."';
        var element_message_delete = '".t('Delete element from Formidable Form')."';
        var element_message_bulk = '".t('Add multiple options for Formidable Element')."';
        var layout_message_add = '".t('Add layout on Formidable Form')."';
        var layout_message_edit = '".t('Edit layout on Formidable Form')."';
        var layout_message_delete = '".t('Delete layout from Formidable Form')."';
        var layout_message_duplicate = '".t('Duplicate layout from Formidable Form')."';
        var dependency_action_placeholder_class = '".t('Classname to toggle')."';
        var dependency_action_placeholder_value = '".t('Value to set')."';
        var dependency_action_placeholder_placeholder = '".t('Placeholder to set')."';
        var dependency_values = [['any_value', '".t('any value')."'], ['no_value', '".t('no value')."']];
        var dependency_condition_placeholder = '".t('Value')."';
        var dependency_message_delete = '".t('Delete dependency from Formidable Element')."'
        var condition_values = [['empty', '".t('is empty')."'], ['not_empty', '".t('is not empty')."'], ['equals', '".t('equals')."'], ['not_equals', '".t('not equal to')."'], ['contains', '".t('contains')."'], ['not_contains', '".t('does not contain')."']];
        $(function() {
            ccmFormidableLoadElements();
        });";
        $al->register('javascript-inline', 'formidable/inline/dashboard/elements/top', $script, array('minify' => true, 'combine' => true));

        $script = "var formidable_security_token_element = '".$token->generate('formidable_element')."';
        var formidable_security_token_mailing = '".$token->generate('formidable_mailing')."';
        var formidable_security_token_dependency = '".$token->generate('formidable_dependency')."';
        var attachment_counter = 10000;
        var list_url = '".URL::to('/formidable/dialog/dashboard/forms/mailing_list').$slash."';
        var dialog_url = '".URL::to('/formidable/dialog/dashboard/mailings/dialog').$slash."';
        var tools_url = '".URL::to('/formidable/dialog/dashboard/mailings/tools').$slash."';
        var dependency_url = '".URL::to('/formidable/dialog/dashboard/mailings/dependency').$slash."';
        var element_dialog_url = '".URL::to('/formidable/dialog/dashboard/elements/dialog/select').$slash."';
        var element_tools_url = '".URL::to('/formidable/dialog/dashboard/elements/tools').$slash."';
        var choose_element = '".t('Choose an Element')."';
        var title_element_overlay = '".t('Choose an element')."';
        var title_sitemap_overlay = '".t('Choose a page')."';
        var title_message_add = '".t('Add mailing to Formidable Form')."';
        var title_message_edit = '".t('Edit mailing from Formidable Form')."';
        var title_message_delete = '".t('Delete mailing from Formidable Form')."';
        var dependency_values = [['any_value', '".t('any value')."'], ['no_value', '".t('no value')."']];
        var dependency_condition_placeholder = '".t('Value')."';
        var dependency_message_delete = '".t('Delete dependency from Formidable Mailing')."'
        var condition_values = [['empty', '".t('is empty')."'], ['not_empty', '".t('is not empty')."'], ['equals', '".t('equals')."'], ['not_equals', '".t('not equal to')."'], ['contains', '".t('contains')."'], ['not_contains', '".t('does not contain')."']];
        $(function() {
            ccmFormidableLoadMailings();
            ccmFormidableCreateMenu();
        });";
        $al->register('javascript-inline', 'formidable/inline/dashboard/mailings/top', $script, array('minify' => true, 'combine' => true));

        $script = "var formidable_security_token_form = '".$token->generate('formidable_form')."';
        var list_url = '".URL::to('/formidable/dialog/dashboard/templates/template_list').$slash."';
        var dialog_url = '".URL::to('/formidable/dialog/dashboard/templates/dialog').$slash."';
        var tools_url = '".URL::to('/formidable/dialog/dashboard/templates/tools').$slash."';
        var title_message_delete = '".t('Delete Formidable Template')."'
        $(function() {
            ccmFormidableLoadTemplates();
        });";
        $al->register('javascript-inline', 'formidable/inline/dashboard/templates/top', $script, array('minify' => true, 'combine' => true));

        $al->register('javascript', 'formidable/top', URL::to('/formidable/dialog/formidable/topjs').$slash, array('local' => false, 'minify' => true, 'combine' => true));

        $al->register('javascript', 'formidable/dashboard/common', 'js/dashboard/common_functions.js', array('minify' => true, 'combine' => true), $this->pkgHandle);
        $al->register('javascript', 'formidable/dashboard/forms', 'js/dashboard/forms.js', array('minify' => true, 'combine' => true), $this->pkgHandle);
        $al->register('javascript', 'formidable/dashboard/layouts', 'js/dashboard/layouts.js', array('minify' => true, 'combine' => true), $this->pkgHandle);
        $al->register('javascript', 'formidable/dashboard/elements', 'js/dashboard/elements.js', array('minify' => true, 'combine' => true), $this->pkgHandle);
        $al->register('javascript', 'formidable/dashboard/mailings', 'js/dashboard/mailings.js', array('minify' => true, 'combine' => true), $this->pkgHandle);
        $al->register('javascript', 'formidable/dashboard/results', 'js/dashboard/results.js', array('minify' => true, 'combine' => true), $this->pkgHandle);
        $al->register('javascript', 'formidable/dashboard/templates', 'js/dashboard/templates.js', array('minify' => true, 'combine' => true), $this->pkgHandle);

        $al->register('javascript', 'formidable/timepicker', 'js/plugins/timepicker.min.js', array('minify' => false, 'combine' => true), $this->pkgHandle);
        $al->register('javascript', 'formidable/placeholder', 'js/plugins/placeholder.min.js', array('minify' => false, 'combine' => true), $this->pkgHandle);
        $al->register('javascript', 'formidable/mask', 'js/plugins/mask.min.js', array('minify' => false, 'combine' => true), $this->pkgHandle);
        $al->register('javascript', 'formidable/countable', 'js/plugins/simplycountable.min.js', array('minify' => false, 'combine' => true), $this->pkgHandle);
        $al->register('javascript', 'formidable/dropzone', 'js/plugins/dropzone.js', array('minify' => true, 'combine' => true), $this->pkgHandle);
        $al->register('javascript', 'formidable/slider', 'js/plugins/slider.js', array('minify' => true, 'combine' => true), $this->pkgHandle);
        $al->register('javascript', 'formidable/rating', 'js/plugins/rating.min.js', array('minify' => false, 'combine' => true), $this->pkgHandle);
        $al->register('javascript', 'formidable/signature', 'js/plugins/signature.min.js', array('minify' => false, 'combine' => true), $this->pkgHandle);
        $al->register('javascript', 'formidable/easing', 'js/plugins/easing.min.js', array('minify' => false, 'combine' => true), $this->pkgHandle);

        $al->register('javascript', 'formidable/editor', 'js/editor.js', array('minify' => true, 'combine' => true), $this->pkgHandle);
        $al->register('javascript', 'formidable/template', 'js/template.js', array('minify' => true, 'combine' => true), $this->pkgHandle);

        $al->register('javascript', 'formidable', 'js/formidable.js', array('minify' => true, 'combine' => true), $this->pkgHandle);

        $al->register('css', 'formidable/dashboard', 'css/dashboard/formidable.css', array('minify' => true, 'combine' => true), $this->pkgHandle);

        // Load event listeners!
        $this->events();
    }

    public function install() {
        $pkg = parent::install();
        $this->checkCreateBlocks();
        $this->checkCreateJobs();
        $this->checkCreatePages();
        $this->checkConfig();
        $this->checkPageAttribute();
        $this->checkUserAttribute();
    }

    public function upgrade() {

        $pkg = parent::upgrade();

        $this->checkCreateBlocks();
        $this->checkCreateJobs();
        $this->checkCreatePages();
        $this->checkConfig();
        $this->checkPageAttribute();
        $this->checkUserAttribute();
        $this->checkMailingLabels();

        // Clear all columns...
        $forms = Formidable::getAllForms();
        if (is_array($forms) && count($forms)) {
            foreach ($forms as $formID => $name) {
                Formidable::clearColumnSet($formID);
            }
        }

        // Update form handles.
        $db = Database::connection();
        $forms = $db->getAll("SELECT formID, label FROM FormidableForms WHERE handle IS NULL OR handle = ''");
        if (count($forms)) {
            foreach ($forms as $f) {
                $db->query("UPDATE FormidableForms SET handle = ? WHERE formID = ?", [\Core::make('helper/text')->handle($f['label']), $f['formID']]);
            }
        }
    }

    public function uninstall() {
        parent::uninstall();
        $r = Request::getInstance();
        if ($r->request->get('removeContent')) {
            $db = Database::connection();
            $db->executeQuery('DROP TABLE IF EXISTS FormidableForms');
            $db->executeQuery('DROP TABLE IF EXISTS FormidableFormElements');
            $db->executeQuery('DROP TABLE IF EXISTS FormidableFormMailings');
            $db->executeQuery('DROP TABLE IF EXISTS FormidableAnswerSets');
            $db->executeQuery('DROP TABLE IF EXISTS FormidableAnswers');
            $db->executeQuery('DROP TABLE IF EXISTS FormidableFormLayouts');
            $db->executeQuery('DROP TABLE IF EXISTS FormidableTemplates');
            $db->executeQuery('DROP TABLE IF EXISTS btFormidable');
        }
    }

    private function checkCreateBlocks() {
        if(is_array($this->blocks) && count($this->blocks)) {
            $pkg = Package::getByHandle($this->pkgHandle);
            foreach($this->blocks as $block) {
                $blockType = BlockType::getByHandle($block, $pkg);
                if(!is_object($blockType)) {
                    BlockType::installBlockType($block, $pkg);
                }
            }
        }
    }

    private function checkCreateJobs() {
        if(is_array($this->jobs) && count($this->jobs)) {
            $pkg = Package::getByHandle($this->pkgHandle);
            foreach($this->jobs as $job) {
                $jb = Job::getByHandle($job);
                if(!is_object($jb)) {
                    Job::installByPackage($job, $pkg);
                }
            }
        }
    }

    private function checkCreatePages() {
        if(is_array($this->singlePages) && count($this->singlePages)) {
            $pkg = Package::getByHandle($this->pkgHandle);
            foreach($this->singlePages as $sp) {
                $page = Page::getByPath($sp[0]);
                if ($page->getCollectionID() <= 0) {
                    SinglePage::add($sp[0], $pkg);
                    $page = Page::getByPath($sp[0]);
                }
                if ($sp[1] === true) {
                    $page->setAttribute('exclude_nav', $sp[1]);
                }
            }
        }
    }

    private function checkConfig() {
        if(is_array($this->configs) && count($this->configs)) {
            $pkg = Package::getByHandle($this->pkgHandle);
            foreach($this->configs as $key => $val) {
                if (!$pkg->getFileConfig()->get($key)) $pkg->getFileConfig()->save($key, $val);
            }
        }
    }

    private function checkPageAttribute() {

        $cak = CollectionAttributeKey::getByHandle('exclude_from_formidable');
		if (is_object($cak)) return false;

        $pkg = Package::getByHandle($this->pkgHandle);

        $set = AttributeSet::getByHandle('navigation');
        if (!is_object($set)) $set = false;

        $service = $this->app->make('Concrete\Core\Attribute\Category\CategoryService');
        $categoryEntity = $service->getByHandle('collection');
        $category = $categoryEntity->getController();

        $key = $category->getByHandle('exclude_from_formidable');
        if (!is_object($key)) {
            $key = new PageKey();
            $key->setAttributeKeyHandle('exclude_from_formidable');
            $key->setAttributeKeyName(t('Exclude from Formidable'));
            $key->setAttributeSet($set);
            $key = $category->add('boolean', $key, null, $pkg);
        }
    }

    private function checkUserAttribute() {

        $cak = UserAttributeKey::getByHandle('exclude_from_formidable');
		if (is_object($cak)) return false;

        $pkg = Package::getByHandle($this->pkgHandle);

        //$set = AttributeSet::getByHandle('formidable');
        //if (!is_object($set)) $set = false;

        $service = $this->app->make('Concrete\Core\Attribute\Category\CategoryService');
        $categoryEntity = $service->getByHandle('user');
        $category = $categoryEntity->getController();

        $key = $category->getByHandle('exclude_from_formidable');
        if (!is_object($key)) {
            $key = new UserKey();
            $key->setAttributeKeyHandle('exclude_from_formidable');
            $key->setAttributeKeyName(t('Exclude from Formidable'));
            //$key->setAttributeSet($set);
            $key = $category->add('boolean', $key, null, $pkg);
        }
    }

    public function checkMailingLabels() {
        $db = Database::connection();
        $db->executeQuery("UPDATE FormidableFormMailings SET label = subject WHERE (label IS NULL OR label = '')");
    }

    private function events() {

        // Event: on_formidable_load
        // Params: form (FormidableForm Object)
        // When: Is fired on successfull submission of the form
        Events::addListener('on_formidable_load', function($event) {
            // FormidableForm Object
            $form = $event->getArgument('form');
            //if (is_object($form)) Log::info(t('Formidable Full "%s" succesfully loaded', $form->getLabel()));
        });

        // Event: on_formidable_step
        // Params: form (FormidableForm Object)
        // Params: data (array)
        // When: Is fired on submission of as step in a multistep form
        Events::addListener('on_formidable_step', function($event) {
            // FormidableForm Object
            $form = $event->getArgument('form');
            //if (is_object($form)) Log::info(t('Formidable Full "%s" succesful step', $form->getLabel()));
            // Posted data
            $data = $event->getArgument('data');
        });

        // Event: on_formidable_submit
        // Params: form (FormidableForm Object)
        // Params: data (array)
        // When: Is fired on successfull submission of the form
        Events::addListener('on_formidable_submit', function($event) {
            // FormidableForm Object
            $form = $event->getArgument('form');
            //if (is_object($form)) Log::info(t('Formidable Full "%s" succesful submitted', $form->getLabel()));
            // Posted data
            $data = $event->getArgument('data');
            // Result Object
            $result = $event->getArgument('result');
            $_SESSION['asID'] = $result->getAnswerSetID();
            \Log::addEntry('Answerset ID submitted form: '.$result->getAnswerSetID());
            //if (is_object($form)) Log::info(t('New result (answerSetID) created: %s', $result->getAnswerSetID()));

            // Example code for sending registration to MailChimp
            // NOTE! This code isn't tested and is just for here as example code
            // To get this to work, you need to add your own code!
            /*

            $apiKey = 'your api key';
            $listId = 'your list id';

            $memberId = md5(strtolower($data['email']));
            $dataCenter = substr($apiKey,strpos($apiKey,'-')+1);
            $url = 'https://' . $dataCenter . '.api.mailchimp.com/3.0/lists/' . $listId . '/members/' . $memberId;

            $json = json_encode([
                'email_address' => $data['email'],
                'status'        => $data['status'], // "subscribed","unsubscribed","cleaned","pending"
                'merge_fields'  => [
                    'FNAME'     => $data['firstname'],
                    'LNAME'     => $data['lastname']
                ]
            ]);

            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $apiKey);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            */
        });

        // Event: on_formidable_result_delete
        // Params: answerSetID (integer)
        // When: Is fired on successfull removal of result
        Events::addListener('on_formidable_result_delete', function($event) {
            // FormidableForm Object
            $answerSetID = $event->getArgument('answerSetID');
            //if ($answerSetID) Log::info(t('Formidable Full Result with ID "%s" succesfully deleted', $answerSetID));
        });



    }
}
