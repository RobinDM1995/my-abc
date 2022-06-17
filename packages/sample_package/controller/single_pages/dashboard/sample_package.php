<?php

namespace Concrete\Package\SamplePackage\Controller\SinglePage\Dashboard;

use \Concrete\Core\Page\Controller\DashboardPageController;

defined('C5_EXECUTE') or die(_("Access Denied."));

/**
 * This is the controller for the package which controls the functionality for /dashboard/database/ route.
 *
 * @author AN 05/16/2016
 */
class SamplePackage extends DashboardPageController
{

    /**
     * Function to set the variables for view.
     *
     * @param void
     * @author AN 05/16/2016
     */
    public function view()
    {
        // Here you can set variable for view $this->set('nameForView',$variable);
    }
}
