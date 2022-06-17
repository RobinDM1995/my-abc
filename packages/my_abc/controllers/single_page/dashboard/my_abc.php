<?php

namespace Concrete\Package\MyABC\Controller\SinglePage\Dashboard;

use \Concrete\Core\Page\Controller\DashboardPageController;

defined('C5_EXECUTE') or die(_("Access Denied."));

/**
 * This is the controller for the package which controls the functionality for /dashboard/database/ route.
 *
 * @author AN 05/16/2016
 */
class MyAbc extends DashboardPageController
{

    /**
     * Function to set the variables for view.
     *
     * @param void
     * @author AN 05/16/2016
     */
    public function view()
    {
      $this->redirect('dashboard/my_abc/view');
    }
}
