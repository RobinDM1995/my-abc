<?php

namespace Concrete\Package\SamplePackage;

use Package;
use SinglePage;

/**
 * This is the main controller for the package which controls the  functionality like Install/Uninstall etc.
 *
 * @author AN 05/16/2016
 */
class Controller extends Package
{
    protected $pkgHandle = 'sample_package';
    protected $appVersionRequired = '5.7.5.6';
    protected $pkgVersion = '0.0.4';

    public function getPackageDescription()
    {
        return t("A package to demo of package development.");
    }

    public function getPackageName()
    {
        return t("sample package");
    }

    public function install()
    {
        $pkg = parent::install();

        // Install Single Pages
        $this->install_single_pages($pkg);
    }

    public function uninstall()
    {
        $pkg = parent::uninstall();
    }

    function install_single_pages($pkg)
    {
        $directoryDefault = SinglePage::add('/dashboard/sample_package/', $pkg);
        $directoryDefault->update(array('cName' => t('Sample Package'), 'cDescription' => t('Sample Package')));

        $sample_singlepage = SinglePage::add('/sample_singlepage/', $pkg);
        $sample_singlepage = update(array('cName' => t('Sample single page'), 'cDescription' => t('Sample single page')));
		}
}
