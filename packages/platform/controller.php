<?php

namespace Concrete\Package\Platform;

use Package;

/**
 * This is the main controller for the package which controls the  functionality like Install/Uninstall etc.
 *
 * @author AN 05/16/2016
 */
class Controller extends Package
{

    /**
     * Protected data members for controlling the instance of the package
     */
    protected $pkgHandle = 'sample_package';
    protected $appVersionRequired = '5.7.5.6';
    protected $pkgVersion = '0.0.1';

    /**
     * This function returns the functionality description ofthe package.
     *
     * @param void
     * @return string $description
     * @author AN 05/16/2016
     */
    public function getPackageDescription()
    {
        return t("A package to demo of package development.");
    }

    /**
     * This function returns the name of the package.
     *
     * @param void
     * @return string $name
     * @author AN 05/16/2016
     */
    public function getPackageName()
    {
        return t("sample package");
    }

    /**
     * This function is executed during initial installation of the package.
     *
     * @param void
     * @return void
     * @author AN 05/16/2016
     */
    public function install()
    {
        $pkg = parent::install();

        // Install Single Pages
        $this->install_single_pages($pkg);
    }

    /**
     * This function is executed during uninstallation of the package.
     *
     * @param void
     * @return void
     * @author AN 05/16/2016
     */
    public function uninstall()
    {
        $pkg = parent::uninstall();
    }

    /**
     * This function is used to install single pages.
     *
     * @param type $pkg
     * @return void
     * @author AN 05/16/2016
     */
    function install_single_pages($pkg)
    {
        $directoryDefault = SinglePage::add('/dashboard/sample_package/', $pkg);
        $directoryDefault->update(array('cName' => t('Sample Package'), 'cDescription' => t('Sample Package')));
    }

}
