<?php
namespace Concrete\Package\Abcstart;

use Package;
//DIT ZIJN LEGACY NAMESPACES ... KAN NIET GEBRUIKT WORDEN IN PACKAGES VOOR v8 EN HOGER
//lees meer : http://documentation.concrete5.org/developers/packages/adding-custom-code-to-packages
// we zouden een v8 specifieke package moeten vinden om de code om te zetten.
// ik heb nu appversionrequired op 5.7 versie gezet, zodat de legacy namespaces terug kunnen gebruikt worden... (niet echt ok, maar het werkt voor de moment)
// use Concrete\Package\Abcstart\Src\Utilities\Installer;
// use Concrete\Package\Abcstart\Src\Utilities\Permissions;
// use Concrete\Package\Abcstart\Src\Utilities\Importer;
// use Concrete\Package\Abcstart\Src\Utilities\Starter;
// use Concrete\Package\Abcstart\Src\Utilities\Uninstall\Uninstaller;
use AbcStart\Utilities\Installer as Installer;
use AbcStart\Utilities\Permissions as Permissions;
use AbcStart\Utilities\Importer as Importer;
use AbcStart\Utilities\Starter as Starter;
use AbcStart\Utilities\Uninstall\Uninstaller as Uninstaller;
use Asset;
use Request;
use Concrete\Core\Asset\AssetList;

class Controller extends Package
{
  protected $pkgHandle = 'abcstart';
  protected $appVersionRequired = '8.0.0';
  protected $pkgVersion = '2.0.0';
  protected $pkgAutoloaderRegistries = array(
    'src/Utilities' => '\AbcStart\Utilities'
  );

  public function getPackageDescription(){
      return t("Adds a new theme, a new user group, new blocks and a google preview. Sets permissions correctly for the user groups.");
  }

  public function getPackageName(){
      return t("ABC Start");
  }

  public function on_start(){
    $al = AssetList::getInstance();
    $al->register(
        'javascript', 'maintheme', 'themes/abcbasic/js/main.js',
        array('version' => '1.1.1', 'minify' => true, 'combine' => true), 'abcstart'
    );
    $al->register(
        'javascript', 'bootstraptheme', 'themes/abcbasic/js/bootstrap.min.js',
        array('position' => Asset::ASSET_POSITION_HEADER, 'version' => '1.1.1', 'minify' => true, 'combine' => true), 'abcstart'
    );
    $al->register(
        'css', 'bootstraptheme', 'themes/abcbasic/css/bootstrap.min.css',
        array('version' => '1.1.1', 'minify' => true, 'combine' => true), 'abcstart'
    );
    $al->register(
        'css', 'awesometheme', 'themes/abcbasic/css/font-awesome.min.css',
        array('version' => '1.1.1', 'minify' => true, 'combine' => true), 'abcstart'
    );

    $al->registerGroup('mediaelements', array(
        array('javascript', 'bootstraptheme'),
        array('javascript', 'maintheme'),
        array('css', 'bootstraptheme'),
        array('css', 'awesometheme')
    ));

    Starter::registerEvents();
    Starter::registerGooglePreview();
  }

  public function install(){
    $pkg = parent::install();

    Installer::installTheme($pkg);
    Installer::installUserGroups($pkg);
    Installer::installUsers();
    Installer::installBlocks($pkg);
    Installer::installThumbnails();
    Installer::installAutoResizeImages();

    $includeModerator = true;
    Permissions::setPagePermissions($includeModerator);
    Permissions::setPageTypePermissions($includeModerator);
    Permissions::setFilePermissions($includeModerator);
    Permissions::setUserPermissions($includeModerator);
    Permissions::setUserGroupPermissions($includeModerator);

    $r = \Request::getInstance();
    if ($moderatorBlocks = $r->request->get('moderatorBlocks')) {
      Permissions::setUserGroupBlockPermissions($moderatorBlocks);
    }

    Importer::importFiles($pkg);
  }

  public function upgrade(){
    parent::upgrade();

    Installer::installUserGroups();
    Installer::installUsers();
    Installer::installBlocks();
    Installer::installPermissions();
    Installer::installThumbnails();
    Installer::installAutoResizeImages();
  }

  public function uninstall()
  {
    $includeModerator = false;
    Permissions::setPagePermissions($includeModerator);
    Permissions::setPageTypePermissions($includeModerator);
    Permissions::setFilePermissions($includeModerator);
    Permissions::setUserPermissions($includeModerator);
    Permissions::setUserGroupPermissions($includeModerator);

    Uninstaller::uninstallUsers();
    Uninstaller::uninstallUserGroups();

    parent::uninstall();
  }
}

?>
