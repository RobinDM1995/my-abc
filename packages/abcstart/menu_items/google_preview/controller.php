<?php
namespace Concrete\Package\Abcstart\MenuItem\GooglePreview;

use Page;
use Permissions;
use User;
use Group;

use \Concrete\Core\Application\UserInterface\Menu\Item\Controller as MenuController;

class Controller extends MenuController {

  public function displayItem(){
    $canView = false;
    $u = new User();
    $p = Page::getCurrentPage();
    $gAdministrators = Group::getByName("Administrators");
    $gModerators = Group::getByName("Moderators");
    if ($u->isLoggedIn() && !$p->isEditMode() && !$p->isSystemPage()) {
      if($u->inGroup($gAdministrators) || $u->inGroup($gModerators) || $u->isSuperUser()) {
        $canView = true;
      }
    }
    return $canView;
  }

  public function getMenuItemLinkElement()
  {
    $a = parent::getMenuItemLinkElement();
    // override if you like
    // check \concrete\src\Application\UserInterface\Menu\Item\Controller.php
    return $a;
  }
}

?>
