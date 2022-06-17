<?php
namespace Concrete\Package\Abcstart\Controller\Dialog;

use Concrete\Core\Controller\Controller;

class GooglePreview extends Controller
{
  protected $viewPath = 'dialogs/google_preview';

  public function view()
  {

  }

  function action_edit_page()
  {
      $args = $this->post();
      print_r($args);
      exit; die;
  }
}

?>
