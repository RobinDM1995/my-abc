<?php
  class EsengoWebservice{
    public function webserviceOffer($orderID){
      // Haalt gegevens uit de webservice op
      // Geeft opgehaalde gegevens terug
      $customerData = file_get_contents('http://vpn.abcparts.be/bridge/services/orderCustomerData/' . $orderID);
      $customer = json_decode($customerData);

      $itemData = file_get_contents('http://vpn.abcparts.be/bridge/services/rpItemData/' . $orderID);
      $item = json_decode($itemData);

      $offerData = file_get_contents('http://vpn.abcparts.be/bridge/services/repairOffer/' . $orderID);
      $offer = json_decode($offerData);

      $priority = $item[0]->customerPriority;
      switch($priority){
        case 'INL':
        $priorityFull = 'Low';
        break;

        case 'INM':
        $priorityFull = 'Medium';
        break;

        case 'INH':
        $priorityFull = 'High';
        break;

        default:
        $priorityFull = '';
        break;
      }


      $data = array();

      //CUSTOMER
      $data['contactName'] = $customer[0]->contactName;
      $data['compName'] = $customer[0]->customerDeliveryAddress->address->name1;
      $data['address'] = $customer[0]->customerDeliveryAddress->address->address1;
      $data['zipCode'] = $customer[0]->customerDeliveryAddress->address->zipCode;
      $data['city'] = $customer[0]->customerDeliveryAddress->address->city;
      $data['vat'] = $customer[0]->customer->party->identification->value;

      //ITEM
      $data['itemName'] = $item[0]->item->name;
      $data['itemParCategory'] = $item[0]->item->category->parentCategory->description;
      $data['itemCategory'] = $item[0]->item->category->description;
      $data['serial'] = $item[0]->inventoryRecord->serialNumber;
      $data['custRef'] = $item[0]->customerReference;
      $data['priority'] = $priorityFull;
      $data['brand'] = $item[0]->item->brand;
      $data['itemCode'] = $item[0]->item->code;
      $data['itemImgUrl'] = $item[0]->imageUrl;

      //OFFER
      $data['offerStatus'] = $offer[0]->status;
      $data['extraInfo'] = $offer[0];
      $data['deliveryPeriod'] = $offer[0]->deliveryPeriod;
      $data['analysis'] = $offer[0]->analysisComment;

      return $data;
    }
  }
?>
