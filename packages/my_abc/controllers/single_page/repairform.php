<?php
namespace Concrete\Package\MyAbc\Controller\SinglePage;
use Concrete\Core\Page\Controller\PageController;

use Concrete\Package\MyAbc\Src\Functions;

use Localization;
use Database;
use Events;

class repairform extends Pagecontroller{
  public function view($lang = null){
    // $service = $this->getServices();
    // $this->set('service', $service);
    $this->set('lang', $lang);
    Functions::setLang($lang);
  }

  // public function getServices(){
  //   $db = Database::connection();
  //
  //   $servicecode = 'ANM' . date('Y');
  //
  //   $result = $db->getRow('SELECT * FROM esengoServices WHERE servicecode = ' . $servicecode);
  //
  //   if($result){
  //     return $result;
  //   }else{
  //     $servicecode = 'ANM' . date('Y', strtotime('- 1 year'));
  //     $result = $db->getRow('SELECT * FROM esengoServices WHERE servicecode = ' . $servicecode);
  //   }

  }
}
?>
