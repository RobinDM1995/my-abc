<?php
  defined('C5_EXECUTE') or die(_("Access Denied."));
  Loader::library('3rdparty/fpdf', 'my_abc');

  class PDF extends FPDF{
    function Header(){
      $p = Page::getCurrentPage()
      $db = Loader::db();
      $pkg = Package::getByHandle('my_abc');
      $pkgpath = $pkg->getPackagePath();
      $rpnumber = $_GET['rpnumber'];

      $result = $db->getAll('SELECT * FROM WebformOfferte WHERE offertenr=?', array($rpnumber));

      $transport = $result['transport'];
      $transportdienst = $result['transportdienst'];
      $accnr = $result['accnr'];
      $repair = $result['repair'];

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

      $this->SetFont('Source Sans Pro', 'B', 12);
      $this->Cell(0, 15, t('Repair quote') . $rpnumber, 0,1,'L');

      
    }
  }
?>
