<?php
namespace AbcStart\Utilities;

use Package;
use Core;
use Config;
use Events;
use Route;
use User;
use View;
use URL;
use Page;
use Controller;

defined('C5_EXECUTE') or die("Access Denied.");

class Starter extends Controller {

  protected $pkgHandle = 'abcstart';

  public static function registerEvents() {
    //List of events: http://documentation.concrete5.org/developers/appendix/full-event-list

    Events::addListener('on_page_version_approve', function(){

    });
  }

  public static function registerGooglePreview() {

    $pkgHandle = 'abcstart';
    $u = new User();
    $pkg = Package::getByHandle($pkgHandle);

    if($u->isLoggedin()){
      Route::register('/google_preview/analyse', '\Concrete\Package\Abcstart\Controller\Dialog\GooglePreview::view');
      Route::register('/google_preview/edit_page', '\AbcStart\Utilities\Starter::editPage');

      $yourIcon = array(
          'icon' => 'google',
          'label' => '&nbsp;Google Preview&nbsp;',
          'position' => 'left',
          'href' => "#",
          'linkAttributes' => array('title'=>'Google Preview', 'data-button'=>'google-preview')
      );
      $menuHelper = Core::make('helper/concrete/ui/menu');
      $menuHelper->addPageHeaderMenuItem('GooglePreview', 'Abcstart', $yourIcon);

      Events::addListener('on_before_render', function(){
        $content = "<script type='text/javascript'>
                      $('a[data-button=google-preview]').on('click', function() {
                          $.fn.dialog.open({
                              href: '".URL::to('/google_preview/analyse')."',
                              title: 'Google Preview',
                              width: '800',
                              height: '600',
                              modal: true
                          });
                          return false;
                      });
                  </script>";
        $view = View::getInstance();
        $view->addFooterItem($content);
      });

      require $pkg->getPackagePath() . '/vendor/multi_keyword_density.php';
    }
  }

  public static function editPage() {
    $args = $_POST;
    $errorArray = array();

    if(!empty($args)) {
      $errors = self::validateEditPage($args);

      if(!$errors->has()) {
        $page = Page::getByID($args['cID']);

        $metaTitle = trim($args['metaTitle']) . ' - ' . Config::get('concrete.site');
        $metaDescription = $args['metaDescr'];

        $page->setAttribute('meta_title', $metaTitle);
        $page->setAttribute('meta_description', $metaDescription);

      } else {
        $errorArray = $errors->getList();
      }
    }

    echo json_encode($errorArray, true);
    exit;
  }

  protected static function validateEditPage($args) {
    $e = Core::make('helper/validation/error');

    if(empty($args['cID']) || $args['cID'] == ""){
        $e->add(t('The current page was not found. Contact a supervisor.'));
    }
    if(empty($args['metaTitle']) || $args['metaTitle'] == ""){
        $e->add(t('Title is required'));
    }
    if(empty($args['metaDescr']) || $args['metaDescr'] == ""){
        $e->add(t('Description is required'));
    }

    return $e;
  }
}
?>
