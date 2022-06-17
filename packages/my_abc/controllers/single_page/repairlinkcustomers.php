<?php
namespace Concrete\Package\MyABC\Controller\SinglePage;
use Concrete\Core\Page\Controller\PageController;

use Database;
use EsengoWebservice;
// binnen komen via link op eSengo, met RP nr meegegeven
// eSengo gegevens ophalen voor die RP
// de gegevens laten controleren en eventuaal laten aanpassen
// wanneer de gegevens goed gekeurd worden zullen deze in een databank opgeslagen worden
// knop "link maken" als hierop geklikt wordt gaan de gegevens uit de db opgehaald worden en meegegeven aan een link met een unieke referentie
// de link wordt in de databank gestoken en de datum van aanmaak wordt bij gehouden


class RepairLinkCustomers extends PageController{
  public function view(){
    $rpNr = $_GET['rpnumber'];

    $esengoCustomerData = $this->getOrderCustomerData($rpNr);
    $esengoItemData = $this->getRpItemData($rpNr);

    $formData = $this->prepareFormData($esengoCustomerData, $esengoItemData);

    $this->set('formData', $formData);
  }

  public function prepareFormData($customer, $item){
    $data = array();
    $contactName = explode(' ', $customer['contactName']);

    //array_shift->removes first element from array
    $firstname = array_shift($contactName);
    $lastname = implode(' ', $contactName);

    //customer data
    $data['compName'] = $customer['customerDeliveryAddress']['address']['name1'];
    $data['firstname'] = $firstname;
    $data['lastname'] = $lastname;
    $data['email'] = $customer['contactEmail'];
    $data['phone'] = $customer['contactPhone'];
    $data['gsm'] = $customer['contactGsm'];
    $data['lang'] = $customer['language'];

    //item data
    $data['brand'] = $item['item']['brand'];
    $data['code'] = $item['item']['code'];
    $data['desc'] = $item['description'];
    $data['priority'] = $item['customerPriority'];
    $data['ref'] = $item['customerReference'];
    $data['offer'] = $item['offerRequired'];

    $data['hash'] = bin2hex(random_bytes(12));

    return $data;
  }

  public function sendLink(){
    $db = Database::connection();
    extract($_POST);

    $lang = $this->getLang($language);

    $link = '/' . $lang . '/repairform/?hash=' . $hash;

    $stmt = $db->prepare('INSERT INTO prefillrepairlinks (link, hash, jsondata) VALUES (:link, :hash, :jsondata)');
    $stmt->execute(array(
      ":link" => $link,
      ":hash" => $hash,
      ":jsondata" => $jsonData
    ));

    echo 'https://dev.my.abcparts.be' . $link;
    exit;
  }

  public function testLink(){
    $db = Database::connection();
    extract($_POST);

    $lang = $this->getLang($language);

    $link = '/' . $lang . '/repairform/?hash=' . $hash;

    $stmt = $db->prepare('INSERT INTO prefillrepairlinks (link, hash, jsondata) VALUES (:link, :hash, :jsondata)');
    $stmt->execute(array(
      ":link" => $link,
      ":hash" => $hash,
      ":jsondata" => $jsonData
    ));

    Header('Location: https://dev.my.abcparts.be' . $link);
    die();
  }

  public function getLang($language){
    switch($language){
      case 'Dutch':
      $lang = 'nl';
      break;

      case 'French':
      $lang = 'fr';
      break;

      case 'English':
      $lang = 'en';
      break;

      default:
      $lang = 'en';
    }

    return $lang;
  }

  public function getOrderCustomerData($orderID){
      $customerData = file_get_contents('http://esengo.stg.abc.isencia.com/api/orderCustomerData/' . $orderID);


      $customer = json_decode($customerData, true);

      return $customer[0];
  }

  public function getRpItemData($orderID){
    $itemData = file_get_contents('https://esengo.stg.abc.isencia.com/api/rpItemData/' . $orderID);

    $item = json_decode($itemData, true);
    // echo '<pre>';
    // print_r($item);
    // exit;
    return $item[0];
  }
}

?>
