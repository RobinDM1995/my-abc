<?php
namespace Concrete\Package\MyABC\Src;

  use Loader;

  class DatabaseCall{
    public function getDbData($orderID){
      // Haalt indien mogelijk de klantgegevens uit de databank op
      $db = Loader::db();

      $results = $db->getAll('SELECT * FROM myAbcOffers WHERE offertenr = ?', array($orderID));

      $dbData = array();

      if($results){
        $dbData['name'] = $results[0]['name'];
        $dbData['saletype'] = $results[0]['saletype'];
        $dbData['transport'] = $results[0]['transport'];
        $dbData['transportdienst'] = $results[0]['transportdienst'];
        $dbData['accnr'] = $results[0]['accnr'];
        $dbData['repair'] = $results[0]['repair'];
        $dbData['totalprice'] = $results[0]['totalprice'];
        $dbData['acceptdate'] = $results[0]['acceptdate'];
      }

      return $dbData;
    }

    public function setDbData($postData){
      //Schrijft klantgegevens in de databank weg
      $db = Loader::db();

      $insert = $db->prepare('INSERT into myAbcOffers (offertenr, name, saletype, transport, transportdienst, accnr, repair, totalprice, acceptdate) VALUES (:orderID, :name, :saletype, :transport, :transportdienst, :accnr, :repair, :totalprice, now())');
      $insert->execute(array(
        ':orderID' => $orderID,
        ':name' => $_POST['name'],
        ':saletype' => $saletype,
        ':transport' => $_POST['trans'],
        ':transportdienst' => $_POST['transporter'],
        ':accnr' => $_POST['accNr'],
        ':repair' => $repair,
        ':totalprice' => $totaalprijs
      ));

    }
  }
?>
