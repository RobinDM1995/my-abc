<?php
namespace AbcStart\Utilities;

use Package;
use Core;
use Concrete\Core\File\Importer as FileImporter;

class Importer {

  public static function importFiles($pkg) {
    $fi = new FileImporter;
    $files = scandir($pkg->getPackagePath().'/images/upload');
    foreach($files as $local_file){
      if($local_file != '.' && $local_file != '..' && $local_file != ''){
        $server_file = $local_file;
        $newFile = $fi->import($pkg->getPackagePath().'/images/'.$local_file,$server_file);
      }
    }
  }

}
