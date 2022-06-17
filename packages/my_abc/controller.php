<?php
namespace Concrete\Package\MyAbc;

use Config;
use Core;
use Database;
use Package;
use SinglePage;
use Page;
use Loader;
use Job;
use CollectionAttributeKey;
use \Concrete\Core\Block\BlockType\BlockType;


require_once __DIR__ . '/libraries/fpdf/fpdf.php';

class Controller extends Package
{
    protected $pkgHandle = 'my_abc';
    protected $appVersionRequired = '5.7.5.6';
    protected $pkgVersion = '0.1.66';

    public function getPackageDescription()
    {
        return t("Client portal ABC");
    }

    public function getPackageName()
    {
        return t("My ABC");
    }

    public function upgrade(){
      $pkg = parent::upgrade();
      $this->install_single_pages($pkg);
    }

    public function install()
    {
        $pkg = parent::install();
        $this->install_single_pages($pkg);
    }

    public function uninstall()
    {
        $pkg = parent::uninstall();
    }

    function install_single_pages($pkg)
    {
      $pkg = Package::getByHandle('my_abc');

      Loader::model('collection_types');
      Loader::model('collection_attributes');
      Loader::model('attribute/categories/collection');
      Loader::model("job");


        $offer=Page::getByPath('/offer');
		    if( !is_object($offer) || !intval($offer->getCollectionID()) ){
			       $offer=SinglePage::add('/offer', $pkg);
        }

        $agreementfs=Page::getByPath('/agreementfs');
		    if( !is_object($agreementfs) || !intval($agreementfs->getCollectionID()) ){
			       $agreementfs=SinglePage::add('/agreementfs', $pkg);
        }

        $receipt=Page::getByPath('/receipt');
        if( !is_object($receipt) || !intval($receipt->getCollectionID()) ){
             $receipt=SinglePage::add('/receipt', $pkg);
        }

        if(!Job::getByHandle('check_logs')){
          Job::installByPackage('check_logs', $pkg);
        }

        if(!Job::getByHandle('insert_services')){
          Job::installByPackage('insert_services', $pkg);
        }

        if(!Job::getByHandle('sendmail_agreements')){
          Job::installByPackage('sendmail_agreements', $pkg);
        }
    }

}
