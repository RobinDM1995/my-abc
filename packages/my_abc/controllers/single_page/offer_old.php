<?php
  namespace Concrete\Package\MyABC\Controller\SinglePage;
  use Concrete\Core\Page\Controller\PageController;

  use Localization;
  use Loader;
  use Database;

  class PDF extends \FPDF{
    // Page header
    function Header()
    {
      $customerData = file_get_contents("http://vpn.abcparts.be/bridge/services/orderCustomerData/". $GLOBALS['orderID']);
      $customer = json_decode($customerData);

      switch($customer[0]->language){
        case 'English':
        $locale = 'en_GB';
        $localeTrustpilot = 'en-GB';
        break;

        case 'French':
        $locale = 'fr_FR';
        $localeTrustpilot = 'fr-FR';
        break;

        case 'Dutch':
        default:
        $locale = 'nl_BE';
        $localeTrustpilot = 'nl-BE';
        break;
      }
      Localization::changeLocale($locale);

      $compName = $customer[0]->customerDeliveryAddress->address->name1;
      $contactName = $customer[0]->contactName;

      $this->Image('https://my.abcparts.be/application/img/ABC_Industrial_Parts1000.png', 10,6,60);
      $this->SetFont('Arial', 'B', 18);
      $this->Cell(95, 8, '',0,0, 'L');
      if(strtoupper(substr($GLOBALS['orderID'],0,2)) == 'RP'){
        if($customer[0]->language == 'French'){
          $this->Cell(60,8, utf8_decode(t('Repair quote')),0,0,'L');
        }else{
          $this->Cell(43,8, utf8_decode(t('Repair quote')),0,0,'L');

        }
      }else{
        if($customer[0]->language == 'French'){
          $this->Cell(60,8, utf8_decode(t('Sales Quote')),0,0,'L');
        }else{
          $this->Cell(43,8, utf8_decode(t('Sales Quote')),0,0,'L');

        }
      }
      $this->SetTextColor(139,178,59);
      $this->Cell(55, 8,strtoupper($GLOBALS['orderID']), 0,0,'L');
      $this->ln(17);
      $this->SetFont('Arial', 'B', 10);
      $this->SetTextColor(0,0,0);
      $this->Cell(95,8,'',0,0,'L');
      $this->Cell(50, 8, utf8_decode($compName),0,0,'L');
      $this->ln(6);
      $this->SetFont('Arial', '', 10);
      $this->Cell(95,8,'',0,0,'L');
      $this->Cell(40,8, utf8_decode($contactName), 0,0,'L');
      $this->ln(20);
    }


    // Page footer
    function Footer()
    {
      $this->SetX(1);
      $this->Image('https://my.abcparts.be/application/img/footer_800.jpg',0,228);
    }

    function transportLine($data) {
      if($data['checked'] == 1) {
        $check = '4';
      } else {
        $check = '';
      }
      $checkbox_size = 4;

      $this->Cell(190, 1, '','L R',1,'L'); // witruimte boven checkbox
      $this->Cell(5, $checkbox_size, '', 'L', 0, 'L');
      $this->SetFont('ZapfDingbats','', 8);
      $this->Cell($checkbox_size, $checkbox_size, $check, 1, 0, 'C');
      $this->Cell(1, $checkbox_size, '', 0, 0, 'L');
      $this->Cell(2, $checkbox_size, '', 0, 0, 'L');
      $this->SetFont('Arial', '', 10);
      $this->Cell(80,$checkbox_size,t($data['title']),0,0,'L');

      $this->Cell(98,$checkbox_size, iconv('UTF-8', 'windows-1252', $data['description']),'R',1,'L');
      if(isset($data['description2'])) {
        $this->Cell(92,$checkbox_size,'','L',0,'L');
        $this->Cell(98,$checkbox_size, iconv('UTF-8', 'windows-1252', $data['description2']),'R',1,'L');
      }
      $this->Cell(190, 1, '','L R',1,'L'); // witruimte onder checkbox


    }

  }


  class Offer extends PageController{
    public function view(){
    }

    public function pdf($variables = null){
        $variablesArr = explode('--',$variables);
        $db = Loader::db();
        $orderID = $variablesArr[0];
        $token = $variablesArr[1];

        define('EURO', chr(128));

        $GLOBALS['orderID'] = $orderID;

        if($orderID != null && $token = 'TestToken987654321'){

        $result = $db->getRow('SELECT * FROM myAbcOffers WHERE offertenr=?', array($orderID));
        $name = $result['name'];
        $acceptdate = $result['acceptdate'];
        $transport = $result['transport'];
        $transportDienst = $result['transportdienst'];
        $accnr = $result['accnr'];
        $repair = $result['repair'];
        $saletype = $result['saletype'];
        $totalprice = $result['totalprice'];

        $customerData = file_get_contents("http://vpn.abcparts.be/bridge/services/orderCustomerData/". $orderID);
        $itemData = file_get_contents("http://vpn.abcparts.be/bridge/services/rpItemData/". $orderID);
        $offerData = file_get_contents("http://vpn.abcparts.be/bridge/services/repairOffer/". $orderID);

        $customer = json_decode($customerData);
        $item = json_decode($itemData);
        $offer = json_decode($offerData);

        $itemName = $item[0]->item->name;
        $itemParCategory = $item[0]->item->category->parentCategory->description;
        $itemCategory = $item[0]->item->category->description;
        $serialNr = $item[0]->inventoryRecord->serialNumber;
        $custRef = $item[0]->customerReference;
        $itemUrl = str_replace('esengo', 'vpn', $item[0]->imageUrl);
        $deliveryPeriod = $offer[0]->deliveryPeriod;
        $analysis = $offer[0]->analysisComment;


        switch($customer[0]->language){
          case 'English':
          $locale = 'en_GB';
          $localeTrustpilot = 'en-GB';
          break;

          case 'French':
          $locale = 'fr_FR';
          $localeTrustpilot = 'fr-FR';
          break;

          case 'Dutch':
          default:
          $locale = 'nl_BE';
          $localeTrustpilot = 'nl-BE';
          break;
        }
        Localization::changeLocale($locale);


        switch($item[0]->customerPriority){
          case 'INL':
          $priority = 'Low';
          break;

          case 'INM':
          $priority = 'Medium';
          break;

          case 'INH':
          $priority = 'High';
          break;

          default:
          $priority = '';
          break;
        }

        $cellHeight = 5;

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
          )
        );

        $bestOffer = min(array_column($optionArr, 'price'));

        if(strtoupper(substr($orderID,0,2)) == 'SO'){
          $transArr = array('delbel', 'pickup', 'custacc');
        }else{
          $transArr = array('pickup', 'transserv', 'custacc');
        }

        $pdf = new PDF();
        $pdf->SetAutoPageBreak(1, 71);
        $pdf->AliasNbPages();
        $pdf->AddPage();

        //Date & VAT
        $pdf->Cell(12, $cellHeight, '', 0,0);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(15, $cellHeight, t('Date') . ': ', 0,0,'L');
        $pdf->Cell(68,$cellHeight, '20/10/2020', 0, 0, 'L'); // Variabel
        $pdf->Cell(12, $cellHeight, t('VAT') . ': ',0,0);
        $pdf->Cell(40, $cellHeight, 'BE0423.274.346',0,1,'L'); //Variabel

        //Your ref
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(12, $cellHeight, '', 0,0,'L');
        if($customer[0]->language == 'French'){
          $pdf->Cell(20, $cellHeight, utf8_decode(t('Your ref.: ')), 0, 0, 'L'); // Variabel
        }elseif($customer[0]->language == 'English'){
          $pdf->Cell(17, $cellHeight, utf8_decode(t('Your ref.: ')), 0, 0, 'L'); // Variabel
        }else{
          $pdf->Cell(15, $cellHeight, utf8_decode(t('Your ref.: ')), 0, 0, 'L'); // Variabel
        }
        $pdf->Cell(100, $cellHeight, 'Ontvangen via klant op 14/10/2020',0,1,'L'); // Variabel

        $pdf->Cell(190,4,'',0,1);

        //Price demand
        $pdf->SetTextColor(139,178,59);
        $pdf->Cell(12, $cellHeight, '', 0,0);
        if(strtoupper(substr($orderID,0,2)) == 'RP'){
          $pdf->Cell(140, $cellHeight, t('Thanks for your price demand. You can find the repair price below.'), 0,1,'L'); // Variabel
        }else{
          $pdf->Cell(140, $cellHeight, t('Thanks for your price demand.'), 0,1,'L'); // Variabel
        }
        $pdf->Cell(12, $cellHeight, '', 0,0);
        $pdf->Cell(140, $cellHeight, utf8_decode(t('Please mention our reference %1$s on all your documents.', strtoupper($orderID))), 0, 1, 'L'); // Variabel

        $pdf->Cell(190,$cellHeight,'',0,1,'L');

        //Item(s)
        $pdf->SetTextColor(0,0,0);
        if(strtoupper(substr($orderID,0,2)) == 'SO'){
          //SO offer
          $pdf->Cell(12, $cellHeight,'','L T',0);
          $pdf->Cell(178, $cellHeight, 'Siemens - 6AV2124-0MC01-0AX0 - Touch Panel : TP1200 Comfort','T R',1, 'L');
          $pdf->Cell(190, $cellHeight, '', 'L R', 1);
          $pdf->Cell(12, $cellHeight, '', 'L', 0);
          $pdf->SetFont('Arial', '', 10);
          $pdf->Cell(40, $cellHeight, t('Option'),0,0,'L');
          $pdf->Cell(30, $cellHeight, t('Quantity'),0,0,'L');
          $pdf->Cell(25, $cellHeight, t('Price/Piece'),0,0,'L');
          $pdf->Cell(83, $cellHeight, t('Delivery time'),'R',1,'L');


          for($i = 0; $i < count($optionArr); $i++){
            if(strpos($saletype, strtolower($optionArr[$i]['option'])) !== false){
              $check = 4;
            }else{
              $check = '';
            }
            if($optionArr[$i]['price'] == $bestOffer){
              $pdf->SetFillColor(139, 178, 59);
            }else{
              $pdf->SetFillColor(255,255,255);
            }
            $pdf->Cell(190, 1, '','L R',1,'L',1); // witruimte boven checkbox
            $pdf->Cell(5, 4, '', 'L', 0, 'L',1);
            $pdf->SetFont('ZapfDingbats','', 8);
            $checkbox_size = 4;
            $pdf->Cell($checkbox_size, $checkbox_size, $check, 1, 0, 'C',0);
            $pdf->Cell(1, 4, '', 0, 0, 'L',1);
            $pdf->Cell(2, 4, '', 0, 0, 'L',1);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(40, 4, t($optionArr[$i]['option']),0,0,'L',1);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(30, 4, $optionArr[$i]['quantity'],0,0,'L',1);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(25,4, chr(128) . number_format($optionArr[$i]['price'],2,'.',''),0,0,'L',1);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(83, 4, $optionArr[$i]['deltime'] . ' ' . t('week'),'R',1,'L',1);
            $pdf->Cell(190,1,'', 'L R',1,'L',1);
          }
          $pdf->Cell(190, 1, '', 'L B R',1);

          $pdf->Cell(190, $cellHeight, '', 0, 1);

          //Beste voorstel
          $pdf->Cell(107,$cellHeight,'',0,0,'L');
          $pdf->SetFillColor(139, 178, 59);
          $pdf->SetLineWidth(1);
          $pdf->Cell(45,$cellHeight,t('Our best proposal:'),'L T B',0,'L',1);
          $pdf->SetFont('Arial', 'B', 10);
          $pdf->Cell(38,$cellHeight,str_replace('€', chr(128),'Totaal € ') . number_format($bestOffer,2,'.',''),'T R B',1,'L',1);
        }else{
          //RP offer
          $pdf->Cell(12, 8, '', 'T L',0,'L');
          $pdf->Cell(178, 8, 'B&R - 4PP450.1043-K05','T R',1,'L');
          $pdf->Cell(12, 8, '', 'L',0,'L');
          $pdf->Cell(178,8, $itemName,'R',1,'L');
          $pdf->Cell(12, 8, '', 'L',0,'L');
          $pdf->Cell(8, 8, 's/n', 0, 0, 'L');
          $pdf->Cell(170, 8, $serialNr, 'R', 1, 'L');
          $pdf->Cell(190, 2, '', 'L B R', 1, 'L');

          $pdf->Image($itemUrl, 170, 85, 20, 20, 'JPG');


          $pdf->Cell(190,6,'', 0,1);

          $pdf->Cell(12, 8, '', 'T L', 0, 'L');
          $pdf->Cell(178, 8, t('Extra information'),'T R',1,'L');
          $pdf->SetFont('Arial', '', 10);
          $pdf->Cell(12, $cellHeight, '', 'L B', 0, 'L');
          $pdf->Cell(178, $cellHeight, $analysis,' B R',1,'L');

          $pdf->Cell(190,$cellHeight, '', 0,1);

          $pdf->SetFillColor(139, 178, 59);
          $pdf->SetLineWidth(1);
          if($repair == 1){
            $check = 4;
          }else{
            $check = '';
          }
          $pdf->SetFont('ZapfDingbats','', 8);
          $checkbox_size = 4;
          $cellHeight = 4;
          $pdf->Cell(190, 1.5, '','L T R',1,'L',1); // witruimte boven checkbox
          $pdf->Cell(5, $cellHeight, '', 'L', 0, 'L',1);
          $pdf->SetLineWidth(0.2);

          $pdf->Cell($checkbox_size, $checkbox_size, $check, 1, 0, 'C');
          $pdf->SetLineWidth(1);

          $pdf->Cell(1, $cellHeight, '', '', 0, 'L',1);
          $pdf->Cell(2, $cellHeight, '', '', 0, 'L',1);


          $pdf->SetFont('Arial', 'B', 10);
          $pdf->Cell(23, $cellHeight, utf8_decode(t('Repair:')),'',0,'L',1); // Variabel
          $pdf->SetFont('Arial', '', 10);
          $pdf->Cell(55, $cellHeight, '(' . utf8_decode(t('chosen priority : %1$s', $priority)) . ')','',0,'L',1);// Variabel
          $pdf->SetFont('Arial', 'B', 10);
          $pdf->Cell(55, $cellHeight, '981,75','',0,'L',1); //Variabel
          $pdf->Cell(45, $cellHeight, $deliveryPeriod,'R',1,'L',1); //Variabel

          $pdf->Cell(190, 1.5, '','L B R',1,'L',1); // witruimte onder checkbox

        }
        $cellHeight = 5;
        $pdf->Cell(190, 3, '', 0,1,'L');

        //Transport
        $pdf->SetLineWidth(0.2);
        $pdf->Cell(12, 8,'', 'L T',0);
        if(strtoupper(substr($orderID,0,2)) == 'SO'){
          $pdf->Cell(52, 8, 'Transport & ' . t('Optional service'),'T',0,'L');
          $pdf->SetFont('Arial', '', 10);
          $pdf->Cell(126,8,'(' . t('not included in our best offer') . ')','T R',1,'L');
        }else{
          $pdf->Cell(18, 8, 'Transport','T',0,'L');
          $pdf->SetFont('Arial', '', 10);
          $pdf->Cell(160,8,'(' . t('not included in the above price') . ')','T R',1,'L');
        }
        // prepare transport data
        $data = array();
        if($transport == 'pickup'){
          $data['checked'] = 1;
        }else{
          $data['checked'] = 0;
        }
        $data['title'] = t('Pickup organised by customer');
        $pdf->transportLine($data);

        // prepare transport data
        $data = array();
        if($transport == 'transserv'){
          $data['checked'] = 1;
        }else{
          $data['checked'] = 0;
        }        $data['title'] = t('Standaard levering via transportdienst');
        $data['description'] = t('< 20kg Nederland €35 - België €25');
        $data['description2'] = t('> 20kg of andere landen: prijs op aanvraag');
        $pdf->transportLine($data);


        // prepare transport data
        $data = array();
        if($transport == 'custacc'){
          $data['checked'] = 1;
        }else{
          $data['checked'] = 0;
        }
        $data['title'] = t('Delivery on customers account: ');
        $data['description'] = t('transporter: ')  . $transportDienst  . ' ' .t('N° account: ') . $accnr;
        $pdf->transportLine($data);

        // prepare transport data
        $data = array();
        if($transport == 'delbel'){
          $data['checked'] = 1;
        }else{
          $data['checked'] = 0;
        }
        $data['title'] = utf8_decode(t('Standard delivery in Belgium'));
        $data['description'] = '€ 25';
        $data['description2'] = t('Maximum weight 20kg');
        $pdf->transportLine($data);

        // prepare transport data
        $data = array();
        $data['checked'] = 0;
        $data['title'] = t('Custom delivery');
        $data['description'] = 'Custom description 1';
        $data['description2'] = 'Custom description 2';
        $pdf->transportLine($data);


        $pdf->Cell(190, 1, '', 'L B R',1);

        $pdf->Cell(190, $cellHeight, '', 0,1);

        if(strtoupper(substr($orderID,0,2)) == 'SO'){
          $pdf->Cell(107,$cellHeight,'',0,0,'L');
          $pdf->SetLineWidth(1);
          $pdf->Cell(45,$cellHeight,t('Your chosen options'),'L T B',0,'L');
          $pdf->SetFont('Arial', 'B', 10);
          $pdf->Cell(38,$cellHeight,'Totaal ' . chr(128) . ' ' .$totalprice,'T R B',1,'L');
          $pdf->Cell(190, $cellHeight, '',0,1);
        }


        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(100,4,utf8_decode(t('Mentioned prices in euro excl. VAT during 1 month')),0,0,'L');
        $pdf->Cell(15, 4, t('Name: '),0,0,'L');
        $pdf->Cell(25, 4, utf8_decode(ucfirst($name)),0,1,'L');
        $pdf->SetFont('Arial', 'U', 8);
        $pdf->Cell(100,4,t('Warranty:'), 0, 1,'L');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(100,4, t('1 year'), 0, 0, 'L');
        $pdf->Cell(15, 4, t('Date: '),0,0,'L');
        $pdf->Cell(25,4, $acceptdate,0,1,'L');
        $pdf->SetFont('Arial', 'U', 8);
        $pdf->Cell(100,4,t('Payment conditions:'),0,1,'L');
        $pdf->SetFont('Arial','',8);
        $pdf->Cell(100,4, utf8_decode(t('Belgium & Netherlands: 14 days net (after credit check)')),0,1,'L');
        $pdf->Cell(100,4,t('Other countries: prepayment'),0,1,'L');
        $pdf->SetFont('Arial', 'U',8);
        $pdf->Cell(100,4, utf8_decode(t('General sales conditions')),0,1,'L',0,'https://www.abcparts.be/nl/legal/algemene-voorwaarden');

        ob_get_clean();
        $pdf->Output();
        }
    }

    public function approval($variables = null){
      $variablesArr = explode('--', $variables);

      $db = \Database::connection();

      $orderID = $variablesArr[0];
      $token = $variablesArr[1];

      $customerData = file_get_contents("http://vpn.abcparts.be/bridge/services/orderCustomerData/". $orderID);
      $itemData = file_get_contents("http://vpn.abcparts.be/bridge/services/rpItemData/". $orderID);
      $offerData = file_get_contents("http://vpn.abcparts.be/bridge/services/repairOffer/". $orderID);

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
        $priorityFull = 'High';
        break;

        default:
        $priorityFull = '';
        break;
      }

      $this->set('orderID', strtoupper($orderID));
      $this->set('token', $token);

      $linkCon = "https://my.abcparts.be/offer/confirm/" . $orderID . "--TestToken987654321";
      $this->set('linkCon', $linkCon);
      $linkFeed = "https://my.abcparts.be/offer/feedback/" . $orderID . "--TestToken987654321";
      $this->set('linkFeed', $linkFeed);
      $linkPdf = "https://my.abcparts.be/offer/pdf/" . $orderID . "--TestToken987654321";
      $this->set('linkPdf', $linkPdf);

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
      $this->set('vat', $customer[0]->customer->party->identification->value);
      $this->set('brand', $item[0]->item->brand);
      $this->set('itemcode', $item[0]->item->code);
      $this->set('offerStatus', $offer[0]->status);
      $this->set('extraInfo', $offer[0]);
      $this->set('itemImg', base64_encode(file_get_contents(str_replace('esengo', 'vpn', $item[0]->imageUrl))));
      $this->set('datum', Date('Y-m-d'));
      $this->set('deliveryPeriod', $offer[0]->deliveryPeriod);
      $this->set('analysis', $offer[0]->analysisComment);

      $this->set('item', $item);
      $this->set('offer', $offer);

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
        )
      );

      $bestOffer = min(array_column($optionArr, 'price'));
      $this->set('bestOffer', $bestOffer);

      $this->set('optionArr', $optionArr);

      $result = $db->getRow('SELECT * FROM myAbcOffers Where offertenr=?', $orderID);
      if($result){
        $disable = 'disable';
        $this->set('filled', 'filled');
        $this->set('repair', $result['repair']);
        $this->set('trans', $result['transport']);
        $transporter = $result['transportdienst'];
        $accNr = $result['accnr'];
        $this->set('name', $result['name']);
        $this->set('datum', $result['acceptdate']);
        $this->set('totaalprijs', $result['totalprice']);
        $this->set('saletype', $result['saletype']);
      }

      if($disable == 'disable'){
        $disabled = 'disabled';
        $this->set('disabled', $disabled);
      }

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
      $this->set('transdata', $transdata);

      if(substr($orderID,0,2) == "RP"){
        $this->set('offerTitle', t('Repair quote'));
        $this->set('priceDemand', 'Thanks for your price demand. You can find the repair price below. <br> Please mention our reference %1$s on all your documents');
        $this->set('transportTitle', '<i class="fa fa-truck"></i><strong>Transport</strong> (' . t('not included in the above price') . ') <br>');
      }else{
        $this->set('offerTitle', t('Sales Quote'));
        $this->set('priceDemand', 'Thanks for your price demand. <br> Please mention our reference %1$s on all your documents');
        $this->set('transportTitle', '<i class="fa fa-truck"></i><strong>Transport & ' . t('Optional service') .'</strong> (' . t('not included in our best offer') . ') <br>');
      }

      //MISSING
      //Receiver
      //Ontvangen op
      // order comments
      // description
      // tot net prijs
    }

    public function confirm($variables = null){
      $variablesArr = explode('--', $variables);
      $orderID = $variablesArr[0];
      $token = $variablesArr[1];
      $db = \Database::connection();

      if($_POST['repair'] == 'on'){
          $repair = 1;
        }else{
          $repair = 0;
        }

        $saleoption = $_POST['saletype'];
        // $this->set('saleoption', $saleoption);
        $saletype = '';
        $totaalprijs = 0;
        foreach($saleoption as $sopt){
          $option = explode(',', $sopt);

          $saletype .= $option[0]. ', ';
          $totaalprijs += floatval($option[1]);
        }

        if($_POST['trans'] == 'delbel'){
          $totaalprijs += 25;
        }

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

        $mailsubject = 'Offerte ' . strtoupper($orderID) . ' is aanvaard.';
        $mailTitle = 'Offerte '. strtoupper($orderID) .' aanvaard';

        $mailbody = 'Offerte ' . strtoupper($orderID) . ' is aanvaard door ' . ucfirst($_POST['name']) . ' op ' . Date('Y-m-d') . '.';
        $mailbody .= '<h2 mc:edit="title1" style="font-family:\'Segoe UI\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size:26px; line-height:27pt; color:#555555; font-weight:300; margin-top:0; margin-bottom:15px !important; padding:0;"><span style="color:#8bb23b">Uw gekozen opties:</span></h2>';

        if($repair == 1){
          $mailbody .= '<table><th><img src="https://my.abcparts.be/application/img/checkbox_check.png" alt="checked"></th><th>Herstelling</th><th>981,75</th><th>+/- 3 weken</span></th></table>';
        }

        switch($_POST['saletype']){
          case 'trade':
          $mailbody .= '<table><th><img src="https://my.abcparts.be/application/img/checkbox_check.png" alt="checked"></th><th>6AV2124-0MC01-0AX0 - Omruil Aantal: 1</th></table>';
          break;

          case 'refurbished':
          $mailbody .= '<table><th><img src="https://my.abcparts.be/application/img/checkbox_check.png" alt="checked"></th><th>Gereviseerd</th></table>';
          break;

          case 'new':
          $mailbody .= '<table><th><img src="https://my.abcparts.be/application/img/checkbox_check.png" alt="checked"></th><th>Nieuw</th></table>';
          break;

          default:
          $mailbody .= '';
          break;
        }

        $mailbody .= 'Transport: <br>';
        switch($_POST['trans']){
          case 'pickup':
          $mailbody .= '<table><th><img class="imgTrans" src="https://my.abcparts.be/application/img/checkbox_check.png" alt="checked"></th><th>Afhaling door klant</th></table>';
          break;

          case 'transserv':
          $mailbody .= '<table><th><img class="imgTrans" src="https://my.abcparts.be/application/img/checkbox_checked.png" alt="checked"></th><th>Standaard levering via transportdienst</th></table>';
          break;

          case 'custacc':
          $mailbody .= '<table><th><img class="imgTrans" src="https://my.abcparts.be/application/img/checkbox_check.png" alt="checked"></th><th>Transportdienst op account van klant</th></table> Transportdienst: '. $_POST['transporter'] . ', klantennummer: ' . $_POST['accNr'] . '';
          break;

          case 'delbel':
          $mailbody .= '<table><th><img class="imgTrans" src="https://my.abcparts.be/application/img/checkbox_check.png" alt="checked"></th><th>Standaard levering in ' . utf8_decode('België') . '</th></table>';
          break;

          default:
          $mailbody .= '';
          break;
        }

        if($totaalprijs != 0){
          $mailbody .= 'Totaalprijs: <strong>'. chr(128) . $totaalprijs .'</strong>';
        }

        $mailbody .= '<br><a href="https://my.abcparts.be/offer/pdf/' . $orderID . '--TestToken987654321"><img src="https://my.abcparts.be/application/img/pdfbtn.png"></a>'; //Eventueel weg, te zien wat Admin zegt

        $this->sendMail($orderID, $mailbody, $mailsubject, $mailTitle);
    }

    public function feedback($variables = null){
      $variablesArr = explode('--', $variables);
      $orderID = $variablesArr[0];
      $token = $variablesArr[1];

      $customerData = file_get_contents("http://vpn.abcparts.be/bridge/services/orderCustomerData/". $orderID);

      $customer = json_decode($customerData);

      $contactName = $customer[0]->contactName;

      $mailsubject = 'Aanpassing gevraagd voor offerte ' . strtoupper($orderID);

      $mailTitle = 'Aanpassing gevraagd voor offerte ' . strtoupper($orderID);

      $mailbody .= $contactName . ' heeft onderstaande aanpassing gevraagd: <br><br>';
      $mailbody .= utf8_decode($_POST['feedback']);

      $this->sendMail($orderID, $mailbody, $mailsubject, $mailTitle);
    }

    public function sendMail($orderID, $mailbody, $mailsubject, $mailTitle){
      $bodyHeader = '
      <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
      <html xmlns="http://www.w3.org/1999/xhtml">
      <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
      <title>'. t('Your repair request has been registered') .'</title>


      <!--[if gte mso 9]>
      <style type="text/css">
      #pageContainer {
        background-color:transparent !important;
      }
      </style>
      <![endif]-->
      <style type="text/css">
        body{
          margin:0;
          padding:0;
          background-color:#e6e6e6;
          color:#777777;
          font-family:Arial, Helvetica, sans-serif;
          font-size:12px;
          -webkit-text-size-adjust:none;
          -ms-text-size-adjust:none;
        }
        h1,h2,h3{
          color:#555555;
          margin-bottom:15px !important;
        }
        h4{
          color:#777777;
          margin-bottom:0px !important;
        }
        a,a:link,a:visited{
          color:#74b843;
          text-decoration:underline;
        }
        a:hover,a:active{
          text-decoration:none;
          color:#28855d !important;
        }
        .phone a{
          text-decoration:none;
        }
        p{
          margin:0 0 14px 0;
          padding:0;
        }
        img{
          border:0;
        }
        table td{
          border-collapse:collapse;
        }
        td.border_b{
          border-bottom:1px #eeeeee solid;
        }
        td.border_r{
          border-right:1px #eeeeee solid;
        }
        td.border_b_r{
          border-bottom:1px #eeeeee solid;
          border-right:1px #eeeeee solid;
        }
        #pricingTable{
          border-collapse:separate !important;
        }
        .highlighted{
          color:#74b843;
        }
        .ReadMsgBody{
          width:100%;
        }
        .ExternalClass{
          width:100%;
        }
        .yshortcuts{
          color:#777777;
        }
        .yshortcuts a span{
          color:#777777;
          border-bottom:none !important;
          background:none !important;
          text-decoration:none !important;
        }
        .adres{
          font-size: 13px;
          color: #777777;
        }
        div{
          font-size: 13px;
        }
        .pad{
          padding-left: 10px;
        }

    </style></head>

    <body background="images/bg.jpg">

    <table id="pageContainer" width="100%" align="center" background="images/bg.jpg" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse; text-align:left; background-repeat:repeat; background-color:#e6e6e6;">
      <tr>
          <td style="padding-top:30px; padding-bottom:30px;">

          <!-- Start of logo, phone and banner container -->
          <table bgcolor="#ffffff" width="600" align="center" cellpadding="0" cellspacing="0" style="border-collapse:collapse; text-align:left; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:12px; line-height:15pt; color:#777777;">
              <tr>
                  <td width="270" valign="middle" style="padding-top:20px; padding-left:30px; padding-bottom:15px; font-family:Arial, Helvetica, sans-serif; font-size:24px; line-height:15pt; color:#000; border-top:1px solid #dddddd;"><a href="https://www.abcparts.be" target="_blank">
                      <img src="https://my.abcparts.be/application/img/ABC_Industrial_Parts800.png" alt="ABC Industrial Parts" border="0" width="200" style="margin: 0; padding: 0; width:200px" mc:edit="logo"></a>
                  </td>
                  <td mc:edit="support" width="270" valign="middle" style="padding-top:25px; padding-right:30px; padding-bottom:25px; font-family:Arial, Helvetica, sans-serif; font-size:12px; line-height:15pt; color:#777777; text-align:right; border-top:1px solid #dddddd;"><br>

  <h4 class="phone" style="font-family:\'Segoe UI\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size:24px; line-height:100%; font-weight:300; color:#777777; margin-top:0; margin-bottom:0 !important; padding:0;"><span class="mc-toc-title"><span>+32 9 380 43 50<br><a href="https://www.abcparts.be" target="_blank">www.abcparts.be</a></span></span></h4>
  </td>
              </tr>
          </table>
        <!-- End of logo, phone and banner container -->

              <!-- Start of content with image on the left -->
              <table bgcolor="#ffffff" width="600" align="center" cellpadding="0" cellspacing="0" style="border-collapse:collapse; text-align:left; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:12px; line-height:15pt; color:#777777;">
                  <tr>
                      <td width="600" colspan="2" style="padding-top:20px; padding-right:30px; padding-left:30px; font-family:Arial, Helvetica, sans-serif; font-size:12px; line-height:15pt; color:#777777;">
                          <h2 mc:edit="title1" style="font-family:\'Segoe UI\', \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size:26px; line-height:27pt; color:#555555; font-weight:300; margin-top:0; margin-bottom:15px !important; padding:0;"><span style="color:#8bb23b">'. $mailTitle .'</span></h2>
            </td>
                  </tr>
                  <tr>

                      <td mc:edit="inhoud1" width="600" valign="top" style="padding-right:30px; padding-left:30px; font-family:Arial, Helvetica, sans-serif; font-size:12px; line-height:15pt; color:#777777;"><div style="text-align: left;">
      ';


    $bodyFooter = '
      </div>
              </td>
                </tr>
                <tr>
                  <td width="600" colspan="2" height="31" style="padding-top:5px; font-size:2px; line-height:0px;"><img src="https://gallery.mailchimp.com/e3bc240bdf4c4a226170902c0/images/1248ec40-b635-4f50-9f84-3bb797a235a2.png" alt="" border="0" style="margin: 0; padding: 0;" mc:edit="divider1"></td>
                </tr>
      </table>
      <!-- End of content with image on the left -->



      <!-- Start of footer -->
      <table width="640" align="center" cellpadding="0" cellspacing="0" style="border-collapse:collapse; text-align:left; font-family:Arial, Helvetica, sans-serif; font-weight:normal; font-size:12px; line-height:15pt; color:#777777;">
      <tr>
        <td mc:edit="footer_copyright" width="600" colspan="7" style="padding-left:20px; padding-right:20px; font-family:Arial, Helvetica, sans-serif; font-size:11px; line-height:13pt; color:#777777; text-align:center;"><!-- TrustBox widget - Review Collector --> <div class="trustpilot-widget" data-locale="%localetrustpilot%" data-template-id="56278e9abfbbba0bdcd568bc" data-businessunit-id="5d14e4694d01c600010eee1a" data-style-height="52px" data-style-width="100%"> <a href="https://nl.trustpilot.com/review/abcparts.be" target="_blank" rel="noopener"><img src="https://www.abcparts.be/application/files/6615/6172/4284/trustpilot.png" width="300" height="150px" style="border:none"></a> </div> <!-- End TrustBox widget -->Copyright &copy; ' . date('Y') . ' <a href="https://www.abcparts.be" style="text-decoration:underline; color:#8bb23b;" target="_blank"><span style="color:#8bb23b">ABC Industrial Parts</span></a>, All rights reserved.</td>
      </tr>
      </table>
      <!-- End of footer -->

      </td></tr></table>



      </body>
      </html>';

    $bodyFull = $bodyHeader . $mailbody . $bodyFooter;

    // $to = 'rdm@abcparts.be, ch@abcparts.be';
    $to = 'rdm@abcparts.be, pn@abcparts.be';
    $subject = $mailsubject;
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=iso-8859-1';
    $headers[] = 'From: ABC parts offer <offer@abcparts.be>';

    if(mail($to, $subject, $bodyFull, implode("\r\n", $headers)));

    sleep(5);
    header('Location: /offer/approval/' . $orderID . '--TestToken987654321', true, 303);
    die();
    }
  }
?>
