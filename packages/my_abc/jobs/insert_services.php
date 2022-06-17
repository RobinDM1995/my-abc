<?php
namespace Concrete\Package\MyAbc\Job;
use \Concrete\Core\Job\Job;

use Loader;
use Core;
use Database;

class insertServices extends Job{
  public function getJobName() {
    return t("Insert esengo services");
  }

  public function getJobDescription() {
    return t("Insert services into myabc table");
  }

  public function run(){
    $esengoData = $this->getEsengoData(46461); // template id myabc-services api
    $db = Database::connection();
    echo '<pre>';
    print_r($esengoData);
    exit;
    foreach($esengoData as $data){
      $result = $db->getRow('SELECT * FROM esengoServices WHERE servicecode = "' .$data['code'] . '"');
      if($result){
        $q = 'UPDATE esengoServices SET servicename = :servicename, baseprice = :baseprice WHERE servicecode = :servicecode';
        $this->processService($q, $data);
      }else{
        $q = 'INSERT INTO esengoServices (servicecode, servicename, baseprice) VALUES (:servicecode, :servicename, :baseprice)';
        $this->processService($q, $data);
      }
    }
  }

  public function processService($q, $data){
    $db = Database::connection();

    $stmt = $db->prepare($q);
    $stmt->execute(array(
      ':servicename' => $data['name'],
      ':baseprice' => $data['basePrice'],
      ':servicecode' => $data['code']
    ));
  }

  public function getEsengoData($templateID){
    $autoToken = 'sTBhCYUbKyr1CxL%2FUy7qP0aI8hOu1xMVA8l7wnOpC0u1TGrkMsfm9XjyQfgYxuybgb3dQU2JmwjvFoeFm0YAJtCsp3atxHwOoWN08XR133h1YhnqQdxS5bcu6TE078XwiTKiBcWTi1GKSgRPXdImpw%3D%3D';

    $url = "https://vpn.abcparts.be/bridge/services/template?id=".$templateID."&auth_token=".$autoToken;

    $results = file_get_contents($url);

    $esengoData = json_decode($results, true);

    return $esengoData;
  }
}
?>
