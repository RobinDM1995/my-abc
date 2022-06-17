<?php
namespace Concrete\Package\MyAbc\Controller\SinglePage;
use Concrete\Core\Page\Controller\PageController;
// use Concrete\Package\MyAbc\Controller\SinglePage\Area;
use Concrete\Package\MyAbc\Src\EsengoWebservice as EsengoWebserviceObject;
use Concrete\Package\MyAbc\Src\DatabaseCall as DatabaseObject;
use Concrete\Package\MyAbc\Src\Functions;

  use Localization;

class Offer extends PageController{


  public function view($variables = null){


    //View haalt gegevens op en veranderd de form naargelang de offerte voor een verkoop of repair is
    // $a = new \Area('Main');
    // $a->display($c);
    //Beginpagina, haalt gegevens op uit models en geeft door aan single page
    $variablesArr = explode('--', $variables);
    $orderID = $variablesArr[0];

    $token = $variablesArr[1];

    $custData = EsengoWebserviceObject::webserviceOffer($orderID);
    // $exchangebudget = file_get_contents('https://dev.abcruilbudgetten.be/api/getBudgetSummary/' . $custData['custCode']);
    $url = 'https://www.abcruilbudgetten.be/api/getBudgetSummary/' . $custData['custCode'];
    $postFields = 'user=abcitweb&password=vero17Nikon;';
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POSTFIELDS => $postFields,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
        'Cookie: CONCRETE5=d39cd62dbc9c51c94e2d638ef40ea258'
      ),
    ));

    $exchangebudget = curl_exec($curl);

    curl_close($curl);

    $budget = json_decode($exchangebudget);
    // // $dbCustData = DatabaseObject::getDbData($orderID);
    // echo '<pre>';
    // print_r($budget);
    // exit;
    $this->set('orderID', $orderID);
    $this->set('exchangebudget', $exchangebudget);
    functions::setLang($custData['language']);

    // $linkCon  = '/confirm/' . $orderID . '--TestToken987654321';
    // $linkFeed = '/feedback/' . $orderID . '--TestToken987654321';

    // $linkCon = "https://my.abcparts.be/offer/confirm/" . $orderID . "--TestToken987654321";
    // $this->set('linkCon', $linkCon);
    // $linkFeed = "https://my.abcparts.be/offer/feedback/" . $orderID . "--TestToken987654321";
    // $this->set('linkFeed', $linkFeed);


    $this->set('orderID', $orderID);

    //WEBSERVICE customer
    $this->set('custName', $custData[0]->contactName);
    $this->set('compName', $custData[0]->customer->party->name);
    $this->set('address', $custData[0]->customerDeliveryAddress->address->address1);
    $this->set('zipCode', $custData[0]->customerDeliveryAddress->address->zipCode);
    $this->set('city', $custData[0]->customerDeliveryAddress->address->city);
    // $this->set('vat', $custData['vat']);
    //
    // //WEBSERVICE item
    $this->set('itemName', $custData[0]->item->_string);
    // $this->set('itemParCategory', $custData['itemParCategory']);
    $this->set('itemCategory', $custData[0]->item->category);
    $this->set('serial', $custData[0]->serialNumber);
    $this->set('custRef', $custData[0]->customerReference);
    $this->set('priority', $custData[0]->customerPriority);
    $this->set('brand', $custData[0]->item->brand);
    if($custData[0]->itemImgUrl){
      $this->set('itemImg', base64_encode(file_get_contents(str_replace('esengo', 'vpn', $custData[0]->itemImgUrl))));
    }
    //
    // //WEBSERVICE offer
    $this->set('offerStatus', $custData[0]->status);
    // $this->set('extraInfo', $custData['extraInfo']);
    $this->set('deliveryPeriod', $custData[0]->deliveryPeriod);
    $this->set('repairPrice', $custData['offerAmount']);

    // $this->set('analysis', $custData['analysis']);
    //

    // echo '<pre>';
    // print_r($custData);
    // exit;
    $this->set('optionArr', $custData['optionArr']);
    $this->set('transdata', $custData['transdata']);
    //
    // //Database data
    // if($dbCustData){
    //   $this->set('filled', 'filled');
    //   $this->set('name', $dbCustData['name']);
    //   $this->set('saletype', $dbCustData['saletype']);
    //   $this->set('trans', $dbCustData['transport']);
    //   $transporter = $dbCustData['transportdienst'];
    //   $accnr = $dbCustData['accnr'];
    //   $this->set('repair', $dbCustData['repair']);
    //   $this->set('datum', $dbCustData['acceptdate']);
    //   $this->set('totaalprijs', $dbCustData['totalprice']);
    // }
    //
    // if(substr($orderID,0,2) == "RP"){
    //   $this->set('offerTitle', t('Repair quote'));
    //   $this->set('priceDemand', 'Thanks for your price demand. You can find the repair price below. <br> Please mention our reference %1$s on all your documents');
    //   $this->set('transportTitle', '<i class="fa fa-truck"></i><strong>Transport</strong> (' . t('not included in the above price') . ') <br>');
    // }else{
    //   $this->set('offerTitle', t('Sales Quote'));
    //   $this->set('priceDemand', 'Thanks for your price demand. <br> Please mention our reference %1$s on all your documents');
    //   $this->set('transportTitle', '<i class="fa fa-truck"></i><strong>Transport & ' . t('Optional service') .'</strong> (' . t('not included in our best offer') . ') <br>');
    // }
    //
  }

  public function confirm(){
    // Wordt aangeroepen wanneer webform aanvaard wordt
    // roept setDbData() aan
    // maakt mailbody en geeft door aan Sendmail();
    $postData = array();

    if($_POST['repair'] == 'on'){
      $repair = 1;
    }else{
      $repair = 0;
    }
  }

  public function feedback(){
    // maakt mailbody en geeft door aan sendmail wanneer aanpassing aangevraagd is
  }

  public function sendMail(){
    // maakt mail header en footer
    // verstuurd email naar offer@abcparts.be
  }
}
?>
