<?php
  namespace Concrete\Package\MyABC\Controller\SinglePage;
  use Concrete\Core\Page\Controller\PageController;

  use Localization;
  use Loader;
  use Database;

  class Offers extends PageController{
    public function view(){

    }

    public function approval(){

      $db = \Database::connection();

      $rpnumber = $_GET['rpnumber'];
      $token = $_GET['token'];

      $customerData = file_get_contents("http://vpn.abcparts.be/bridge/services/rpCustomerData/". $rpnumber);
      $itemData = file_get_contents("http://vpn.abcparts.be/bridge/services/rpItemData/". $rpnumber);
      $offerData = file_get_contents("http://vpn.abcparts.be/bridge/services/repairOffer/". $rpnumber);

      $customer = json_decode($customerData);
      $item = json_decode($itemData);
      $offer = json_decode($offerData);

      $language = $customer[0]->language;
      switch($language){
        case 'English':
        $locale = 'en_GB';
        $localeTrustpilot = 'en-GB';
        $langCode = 'en';
        break;

        case 'French':
        $locale = 'fr_FR';
        $localeTrustpilot = 'fr-FR';
        $langCode = 'fr';
        break;

        case 'Dutch':
        default:
        $locale = 'nl_BE';
        $localeTrustpilot = 'nl-BE';
        $langCode = 'nl';
        break;
      }
      Localization::changeLocale($locale);

      $priority = $item[0]->customerPriority;
      switch($priority){
        case 'INL':
        $priorityFull = 'Low';
        break;

        case 'INM':
        $priorityFull = 'Medium';
        break;

        case 'INH':
        $priorityFull;
        break;

        default:
        $priorityFull = '';
        break;
      }
      
      $this->set('rpnumber', strtoupper($rpnumber));
      $this->set('token', $token);

      $link = "https://my.abcparts.be/offers/approval?rpnumber=" . $rpnumber . "&token=TestToken987654321&action=submit";
      $this->set('link', $link);

      $this->set('custName', $customer[0]->contactName);
      $this->set('compName', $customer[0]->customerDeliveryAddress->address->name1);
      $this->set('address', $customer[0]->customerDeliveryAddress->address->address1);
      $this->set('zipCode', $customer[0]->customerDeliveryAddress->address->zipCode);
      $this->set('city', $customer[0]->customerDeliveryAddress->address->city);
      $this->set('langCode', $langCode);

      $this->set('itemName', $item[0]->item->name);
      $this->set('itemParCategory', $item[0]->item->category->parentCategory->description);
      $this->set('itemCategory', $item[0]->item->category->description);
      $this->set('serial', $item[0]->inventoryRecord->serialNumber);
      $this->set('custRef', $item[0]->customerReference);
      $this->set('priority', $priorityFull);

      $this->set('offerStatus', $offer[0]->status);
      $this->set('extraInfo', $offer[0]);

        if($_POST['repair'] == 'on'){
          $repair = 1;
        }else{
          $repair = 0;
        }

        //just for testing
        // $this->set('name', $_POST['name']);
        // $this->set('trans', $_POST['trans']);
        // $this->set('transporter', $_POST['transporter']);
        // $this->set('accnr', $_POST['accNr']);
        // $this->set('repair', $repair);


        $result = $db->getRow('SELECT * from WebformOfferte WHERE offertenr=?', $rpnumber);
        if($result){
          $this->set('filled', 'filled');
          $this->set('disable', 'disable');
          $this->set('repair', $result['repair']);
          $this->set('trans', $result['transport']);
          if($result['transport'] == 'custacc'){
            $this->set('transporter', $result['transportdienst']);
            $this->set('accnr', $result['accnr']);
          }
          $this->set('name', $result['name']);
          $this->set('datum', $result['acceptdate']);

        }else{
          if(isset($_GET['action']) && $_GET['action'] == 'submit'){
            $insert = $db->prepare('INSERT into WebformOfferte (offertenr, name, transport, transportdienst, accnr, repair, acceptdate) VALUES (:rpnumber, :name, :transport, :transportdienst, :accnr, :repair, now())');
            $insert->execute(array(
              ':rpnumber' => $rpnumber,
              ':name' => $_POST['name'],
              ':transport' => $_POST['trans'],
              ':transportdienst' => $_POST['transporter'],
              ':accnr' => $_POST['accNr'],
              ':repair' => $repair
            ));
            header('Location: /approval?rpnumber=' . $rpnumber . '&token=TestToken987654321');
            }
        }

        // if(isset($_GET['action']) && $_GET['action'] == 'submit'){
        //   $to = 'rdm@abcparts.be';
        //   $subject = 'Offerte ' . $rpnumber . ' is aanvaard';
        //   $headers[] = 'MIME-Version: 1.0';
        //   $headers[] = 'Content-type: text/html; charset=iso-8859-1';
        //   $headers[] = 'From: '
        // }

      //MISSING
      //Receiver
      //Ontvangen op
      //btw nummer
      //brand
      //itemcode
      // order comments
      // description
      // tot net prijs
    }
  }
?>
