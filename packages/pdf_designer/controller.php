<?php

/**
 * @project:   PDFDesigner (concrete5 add-on)
 *
 * @author     Fabian Bitter
 * @copyright  (C) 2016 Fabian Bitter (www.bitter.de)
 * @version    1.2.1
 */

namespace Concrete\Package\PdfDesigner;

defined('C5_EXECUTE') or die('Access denied');

use Concrete\Package\PdfDesigner\Src\PDFDesigner;
use Package;
use SinglePage;
use AssetList;
use Route;
use Request;
use Core;

class Controller extends Package
{
    protected $pkgHandle = 'pdf_designer';
    protected $pkgVersion = '1.2.4';
    protected $appVersionRequired = '5.7.0.4';

    public function getPackageDescription()
    {
        return t('Design PDF files with the ease of use that you are accustomed from concrete5.');
    }

    public function getPackageName()
    {
        return t('PDF Designer');
    }

    private function addReminderRoute()
    {
        Route::register("/bitter/" . $this->pkgHandle . "/reminder/hide", function () {
            $this->getConfig()->save('reminder.hide', true);
            $app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
            /** @var $responseFactory \Concrete\Core\Http\ResponseFactory */
            $responseFactory = $app->make(\Concrete\Core\Http\ResponseFactory::class);
            $responseFactory->create("", \Concrete\Core\Http\Response::HTTP_OK)->send();
            $app->shutdown();
        });

        Route::register("/bitter/" . $this->pkgHandle . "/did_you_know/hide", function () {
            $this->getConfig()->save('did_you_know.hide', true);
            $app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
            /** @var $responseFactory \Concrete\Core\Http\ResponseFactory */
            $responseFactory = $app->make(\Concrete\Core\Http\ResponseFactory::class);
            $responseFactory->create("", \Concrete\Core\Http\Response::HTTP_OK)->send();
            $app->shutdown();
        });

        Route::register("/bitter/" . $this->pkgHandle . "/license_check/hide", function () {
            $this->getConfig()->save('license_check.hide', true);
            $app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
            /** @var $responseFactory \Concrete\Core\Http\ResponseFactory */
            $responseFactory = $app->make(\Concrete\Core\Http\ResponseFactory::class);
            $responseFactory->create("", \Concrete\Core\Http\Response::HTTP_OK)->send();
            $app->shutdown();
        });
    }

    public function on_start()
    {
        $this->initComponents();
        $this->addReminderRoute();
    }

    public function initComponents($basicRequirementsOnly = false)
    {
        $this->loadComposerDependencies();

        if ($basicRequirementsOnly === false) {
            $this->registerAssets();
            $this->registerRoutes();
            $this->registerBoxEditors();
            $this->bindCoreClasses();
        }
    }

    public function bindCoreClasses()
    {
        Core::bind('PDFDesigner', function () {
            return \Concrete\Package\PdfDesigner\Src\PDFDesigner::getInstance();
        });
    }

    private function loadComposerDependencies()
    {
        // load composer packages
        require $this->getPackagePath() . '/vendor/autoload.php';
    }

    private function registerBoxEditors()
    {
        PDFDesigner::getInstance()->registerBoxEditor("\Concrete\Package\PdfDesigner\Src\BoxEditor\Image", t("Image"));
        PDFDesigner::getInstance()->registerBoxEditor("\Concrete\Package\PdfDesigner\Src\BoxEditor\TextArea", t("Text Area"));
        PDFDesigner::getInstance()->registerBoxEditor("\Concrete\Package\PdfDesigner\Src\BoxEditor\Table", t("Table"));
        PDFDesigner::getInstance()->registerBoxEditor("\Concrete\Package\PdfDesigner\Src\BoxEditor\Qrcode", t("QR Code"));
        PDFDesigner::getInstance()->registerBoxEditor("\Concrete\Package\PdfDesigner\Src\BoxEditor\BorderBox", t("Border Box"));
    }

    private function registerAssets()
    {
        // register custom assets
        AssetList::getInstance()->register('javascript', 'pdf_designer', "/js/pdf_designer.js", array(), $this->pkgHandle);
        AssetList::getInstance()->register('css', 'pdf_designer', "/css/pdf_designer.css", array(), $this->pkgHandle);

        AssetList::getInstance()->registerGroup("pdf_designer", array(
            array("javascript", "pdf_designer"),
            array("css", "pdf_designer")
        ));


        // register bower assets
        AssetList::getInstance()->register('javascript', 'mustache', "bower_components/mustache.js/mustache.js", array(), $this->pkgHandle);
        AssetList::getInstance()->register('javascript', 'jquery-loading-overlay', "/bower_components/gasparesganga-jquery-loading-overlay/src/loadingoverlay.js", array(), $this->pkgHandle);

        AssetList::getInstance()->register('javascript', 'jsonmate', "bower_components/jsonmate/jquery.jsoneditor.js", array(), $this->pkgHandle);
        AssetList::getInstance()->register('css', 'jsonmate', "bower_components/jsonmate/jsoneditor.css", array(), $this->pkgHandle);

        AssetList::getInstance()->registerGroup("jsonmate", array(
            array("javascript", "jsonmate"),
            array("css", "jsonmate")
        ));

        AssetList::getInstance()->register('javascript', 'colorpicker', "bower_components/colorpicker/jquery.colorpicker.js", array(), $this->pkgHandle);
        AssetList::getInstance()->register('javascript', 'colorpicker-cmyk', "bower_components/colorpicker/parsers/jquery.ui.colorpicker-cmyk-parser.js", array(), $this->pkgHandle);
        AssetList::getInstance()->register('javascript', 'colorpicker-de', "bower_components/colorpicker/i18n/jquery.ui.colorpicker-de.js", array(), $this->pkgHandle);
        AssetList::getInstance()->register('javascript', 'colorpicker-el', "bower_components/colorpicker/i18n/jquery.ui.colorpicker-el.js", array(), $this->pkgHandle);
        AssetList::getInstance()->register('javascript', 'colorpicker-en', "bower_components/colorpicker/i18n/jquery.ui.colorpicker-en.js", array(), $this->pkgHandle);
        AssetList::getInstance()->register('javascript', 'colorpicker-fr', "bower_components/colorpicker/i18n/jquery.ui.colorpicker-fr.js", array(), $this->pkgHandle);
        AssetList::getInstance()->register('javascript', 'colorpicker-nl', "bower_components/colorpicker/i18n/jquery.ui.colorpicker-nl.js", array(), $this->pkgHandle);
        AssetList::getInstance()->register('javascript', 'colorpicker-pl', "bower_components/colorpicker/i18n/jquery.ui.colorpicker-pl.js", array(), $this->pkgHandle);
        AssetList::getInstance()->register('javascript', 'colorpicker-pt-BR', "bower_components/colorpicker/i18n/jquery.ui.colorpicker-pt-BR.js", array(), $this->pkgHandle);
        AssetList::getInstance()->register('javascript', 'colorpicker-ru', "bower_components/colorpicker/i18n/jquery.ui.colorpicker-ru.js", array(), $this->pkgHandle);
        AssetList::getInstance()->register('javascript', 'colorpicker-sr', "bower_components/colorpicker/i18n/jquery.ui.colorpicker-sr.js", array(), $this->pkgHandle);
        AssetList::getInstance()->register('javascript', 'colorpicker-uk', "bower_components/colorpicker/i18n/jquery.ui.colorpicker-uk.js", array(), $this->pkgHandle);
        AssetList::getInstance()->register('css', 'colorpicker', "bower_components/colorpicker/jquery.colorpicker.css", array(), $this->pkgHandle);

        AssetList::getInstance()->registerGroup("colorpicker", array(
            array("javascript", "colorpicker"),
            array("javascript", "colorpicker-cmyk"),
            array("javascript", "colorpicker-de"),
            array("javascript", "colorpicker-el"),
            array("javascript", "colorpicker-en"),
            array("javascript", "colorpicker-fr"),
            array("javascript", "colorpicker-nl"),
            array("javascript", "colorpicker-pl"),
            array("javascript", "colorpicker-pt-BR"),
            array("javascript", "colorpicker-ru"),
            array("javascript", "colorpicker-sr"),
            array("javascript", "colorpicker-uk"),
            array("css", "colorpicker")
        ));
    }

    private function registerRoutes()
    {
        // register custom routes
        Route::register("/dashboard/pdf_designer/dialogs/edit_box", "\Concrete\Package\PdfDesigner\Controller\Dialog\EditBox::view");
        Route::register("/dashboard/pdf_designer/dialogs/edit_box/submit", "\Concrete\Package\PdfDesigner\Controller\Dialog\EditBox::submit");

        Route::register("/dashboard/pdf_designer/dialogs/change_box_type", "\Concrete\Package\PdfDesigner\Controller\Dialog\ChangeBoxType::view");
        Route::register("/dashboard/pdf_designer/dialogs/change_box_type/submit", "\Concrete\Package\PdfDesigner\Controller\Dialog\ChangeBoxType::submit");

        Route::register("/dashboard/pdf_designer/dialogs/change_position", "\Concrete\Package\PdfDesigner\Controller\Dialog\ChangePosition::view");
        Route::register("/dashboard/pdf_designer/dialogs/change_position/submit", "\Concrete\Package\PdfDesigner\Controller\Dialog\ChangePosition::submit");

        Route::register("/dashboard/pdf_designer/dialogs/add_box", "\Concrete\Package\PdfDesigner\Controller\Dialog\AddBox::view");
        Route::register("/dashboard/pdf_designer/dialogs/add_box/submit", "\Concrete\Package\PdfDesigner\Controller\Dialog\AddBox::submit");

        Route::register("/dashboard/pdf_designer/dialogs/import_templates", "\Concrete\Package\PdfDesigner\Controller\Dialog\ImportTemplates::view");
        Route::register("/dashboard/pdf_designer/dialogs/import_templates/import", "\Concrete\Package\PdfDesigner\Controller\Dialog\ImportTemplates::import");

        Route::register("/dashboard/pdf_designer/dialogs/import_google_font", "\Concrete\Package\PdfDesigner\Controller\Dialog\ImportGoogleFont::view");
        Route::register("/dashboard/pdf_designer/dialogs/import_google_font/submit", "\Concrete\Package\PdfDesigner\Controller\Dialog\ImportGoogleFont::submit");
    }

    /**
     *
     * @param type $pathToCheck
     * @return boolean
     *
     */
    private function pageExists($pathToCheck)
    {
        $pkg = Package::getByHandle($this->pkgHandle);

        $pages = SinglePage::getListByPackage($pkg);

        foreach ($pages as $page) {
            if ($page->getCollectionPath() === $pathToCheck) {
                return true;
            }
        }

        return false;
    }

    private function addPageIfNotExists($path, $name, $excludeNav = false)
    {
        $pkg = Package::getByHandle($this->pkgHandle);

        if ($this->pageExists($path) === false) {
            $singlePage = SinglePage::add($path, $pkg);

            if ($singlePage) {
                $singlePage->update(
                    array(
                        'cName' => $name
                    )
                );

                $singlePage->setAttribute('exclude_nav', $excludeNav);
            }
        }
    }

    private function installOrUpdatePages()
    {
        $this->addPageIfNotExists("/dashboard/pdf_designer", t("PDF Designer"));
    }

    private function installOrUpdate()
    {
        $this->installOrUpdatePages();
    }

    public function upgrade()
    {
        $this->initComponents(true);

        $this->installOrUpdate();

        parent::upgrade();
    }

    public function install()
    {
        $this->initComponents(true);

        parent::install();

        $this->installOrUpdate();

        $r = Request::getInstance();

        if (intval($r->request->get('installSampleData')) === 1) {
            PDFDesigner::getInstance()->installSampleData();
        }
    }

    public function uninstall()
    {
        $r = Request::getInstance();

        if (intval($r->request->get('uninstallTemplates')) === 1) {
            PDFDesigner::getInstance()->removeAllTemplates();
        }

        parent::uninstall();
    }
}
