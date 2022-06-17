<?php
namespace Concrete\Package\MyABC\Src;

use Package;
use Core;

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

      $optionArr = array(
        array(
          'option' => 'Trade',
          'quantity' => 1,
          'price' => 1648.13,
          'deltime' => '+/- 1'
        ),
        array(
          'option' => 'Refurbished',
          'quantity' => 1,
          'price' => 2060.16,
          'deltime' => '+/- 1'
        ),
        array(
          'option' => 'New',
          'quantity' => 1,
          'price' => 2131.20,
          'deltime' => '+/- 1'
        ));

        $transdata = array(
          array(
            'title' => t('Pickup organised by customer'),
            'value' => 'pickup',
            'desc1' => '',
            'desc2' => ''
          ),
          array(
            'title' => t('Delivery by transport service'),
            'value' => 'transserv',
            'desc1' => t('< 20kg Netherlands: €35 - Belgium: €25'),
            'desc2' => t('> 20kg or other countries: price on request')
          ),
          array(
            'title' => t('Delivery on customers account'),
            'value' => 'custacc',
            'desc1' => t('transporter: ') . '<input type="text" name="transporter" value="' . $transporter . '" '. $disabled .' class="inputtrans"> ' .
            t('N° account: ') .'<input type="text" name="accnr" value="' . $accNr .'" '. $disabled .' class="inputtrans">',
            'desc2' => ''
          ),
          array(
            'title' => t('Standard delivery in Belgium'),
            'value' => 'delbel',
            'desc1' => '€' . 25.00,
            'desc2' => t('Maximum weight 20kg')
          )
        );


      $data = array();

      //CUSTOMER
      $data['contactName'] = $customer[0]->contactName;
      $data['compName'] = $customer[0]->customerDeliveryAddress->address->name1;
      $data['address'] = $customer[0]->customerDeliveryAddress->address->address1;
      $data['zipCode'] = $customer[0]->customerDeliveryAddress->address->zipCode;
      $data['city'] = $customer[0]->customerDeliveryAddress->address->city;
      $data['vat'] = $customer[0]->customer->party->identification->value;
      $data['language'] = $customer[0]->language;

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

      $data['offerAmount'] = 1000; // placeholder value
      $data['custCode'] = 'A72'; // placeholder value
      $data['optionArr'] = $optionArr;
      $data['transdata'] = $transdata;

      return $data;
    }

    public function getDataFromTemplate($tempID){
      $autoToken = 'sTBhCYUbKyr1CxL%2FUy7qP0aI8hOu1xMVA8l7wnOpC0u1TGrkMsfm9XjyQfgYxuybgb3dQU2JmwjvFoeFm0YAJtCsp3atxHwOoWN08XR133h1YhnqQdxS5bcu6TE078XwiTKiBcWTi1GKSgRPXdImpw%3D%3D';

      // $tempID = 42351;
      $url = 'https://vpn.abcparts.be/bridge/services/template?id=' . $tempID. "&auth_token=" . $autoToken;
      $offerData = file_get_contents($url);

      $offer = json_decode($offerData);
      return $offer;
    }

    public function esengoOrderCustomerData($orderdID){
        $customerData = file_get_contents('http://vpn.abcparts.be/bridge/services/orderCustomerData/' . $orderID);
        $customer = json_decode($customerData);

        return $customer[0];
    }

    public function esengoRpItemData(){

    }
  }
?>
