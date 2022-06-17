<?php
namespace Concrete\Package\MyAbc\Controller\SinglePage;
use Concrete\Core\Page\Controller\PageController;

use Concrete\Package\MyAbc\Src\Functions;

use Localization;
use Database;
use Events;

class repairform extends Pagecontroller{
  public function view($lang = null){
    $this->set('lang', $lang);
    Functions::setLang($lang);
  }
}
?>
