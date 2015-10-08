<?php
class Synapse_Quote_Model_Price_Observer extends Varien_Object
{
    public function applyQuotePrice(Varien_Event_Observer $observer){
        $item = $observer->getQuoteItem();

        if(Mage::getSingleton('core/session')->getIsQuoteAddedToCart()){
            if(Mage::getSingleton('customer/session')->getQuoteCreatedThroughUpload()){
                $quote_id = Mage::getSingleton('customer/session')->getQuoteCreatedThroughUpload();
            }else{
                $quote_id = Mage::getSingleton('core/session')->getCurrentQuote();
            }
            $model = Mage::getModel("quote/quote");
            $existing_quote_record = $model->load($quote_id);
            $quote_details = $existing_quote_record->getData();
            $quote_products = explode(',',$quote_details['quote_product_ids']);
            $quote_product_qty = array_count_values($quote_products);
            $aud_prices=explode(',',$quote_details['quote_product_prices_aud']);
            $nzd_prices=explode(',',$quote_details['quote_product_prices_nzd']);
            $quote_product_price_aud = array_combine($quote_products,$aud_prices);
            $quote_product_price_nzd = array_combine($quote_products,$nzd_prices);
            /* @var $item Mage_Sales_Model_Quote_Item */
            $item = $observer->getQuoteItem();
            $productid = $item->getProduct()->getId();
            $product1 = Mage::getModel('catalog/product')->setStoreId($storeId)->load($productid);
            $attribTxt = strtolower($product1->getAttributeText('product_type'));
            if ($item->getParentItem()) {
                $item = $item->getParentItem();
            }
            $current_currency_code = Mage::app()->getStore()->getCurrentCurrencyCode();

            $itemprice_aud = $quote_product_price_aud[$productid]*$quote_product_qty[$productid];
            $itemprice_nzd = $quote_product_price_nzd[$productid]*$quote_product_qty[$productid];
            if($current_currency_code == 'NZD'){
                $item->setCustomPrice($itemprice_nzd);
                $item->setOriginalCustomPrice($itemprice_nzd);

            }else{
                $item->setCustomPrice($itemprice_aud);
                $item->setOriginalCustomPrice($itemprice_aud);

            }
            $item->getProduct()->setIsSuperMode(true);
            if($attribTxt=='software product' or $attribTxt=='maintenance product'){
                $item->getCustomPrice();
            }

        }else{
            $prodId = $item->getProduct()->getId();
            $storeId = Mage::app()->getStore()->getStoreId();
            $product1 = Mage::getModel('catalog/product')->setStoreId($storeId)->load($prodId);
            $attribTxt = $product1->getAttributeText('product_type');
            if(strtolower($attribTxt)=='maintenance product'){
                $items=Mage::getModel('checkout/cart')->getQuote()->getAllVisibleItems();
                foreach ($items as $item) {
                    $prod=$item->getProduct();
                    $productId=$prod->getId();
                    $product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($productId);
                    $attribTxt1 = $product->getAttributeText('product_type');
//					if(strtolower($attribTxt1)=='maintenance product'){
//						Mage::getSingleton('checkout/session')->addError(Mage::helper('checkout')->__('Upgrade Assurance Product already added to the cart'));
//						header("Location: " . $product1->getProductUrl());
//						die();
//					}
                }
            }
            else{

                $this->getCustomPrice();
            }
        }
    if(!Mage::getSingleton('customer/session')->getQuoteCreatedThroughUpload()){
            $this->getCustomPrice();
        }



	    
    }

	public function getCustomPrice(){
		$cart = Mage::getModel('checkout/cart')->getQuote();
		$this->updateCart($cart->getAllItems());
	}

	public function checkDuplicate(Varien_Event_Observer $item){
		//$item = $observer->getQuoteItem();
		$prodId = $item->getProduct()->getId();	
		$storeId = Mage::app()->getStore()->getStoreId();
		$product1 = Mage::getModel('catalog/product')->setStoreId($storeId)->load($prodId);
		$attribTxt = $product1->getAttributeText('product_type');
		if(strtolower($attribTxt)=='maintenance product'){
			$items=Mage::getModel('checkout/cart')->getQuote()->getAllVisibleItems();
			foreach ($items as $item) {
				$prod=$item->getProduct();
				$productId=$prod->getId();
				$product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($productId);
				$attribTxt1 = $product->getAttributeText('product_type');
				if(strtolower($attribTxt1)=='maintenance product'){			
					Mage::getSingleton('checkout/session')->addError(Mage::helper('checkout')->__('Upgrade Assurance Product already added to the cart'));
					header("Location: " . $product1->getProductUrl());
					die();
				}
			}
		}
	}

	public function updateCustomPrice(Varien_Event_Observer $observer)
	{
		$cart=$observer->getCart()->getQuote()->getAllVisibleItems();
        $this->updateCart($cart);
	}

	private function updateCart($items){
        $total=0;
		$maintenanceProd='';

		foreach ($items as $item) {
			$prod=$item->getProduct();
			$prodId=$item->getProductId();
            $qty = $item->getQty();
            if($qty=='')
				$qty=1;
            $price = $prod->getFinalPrice($qty);
            $storeId = Mage::app()->getStore()->getStoreId();
			$product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($prodId);
            $attribTxt = $product->getAttributeText('product_type');
            switch(strtolower($attribTxt)){
				case 'software product':
                    $total += $qty*$price;
                    Mage::getSingleton('core/session')->setCurrentSoftwareProductPrice($total);
                    break;
				case 'maintenance product':
                    $maintenanceProd=$item;
				break;
			}
		}
        if($maintenanceProd!=''){
			$this->setMaintenancePrice($maintenanceProd,$total);
		}

	}

	private function setMaintenancePrice($item,$total){

        $product=$item->getProduct();
		//$productOptions = $product->getTypeInstance(true)->getOrderOptions($product);
		$prodId=$item->getProductId();

		$storeId = Mage::app()->getStore()->getStoreId();


        $customprice=$product->getFinalPrice();

        $data = Mage::getSingleton('customer/session')->getUpgradeRenewal();
        $softProd = Mage::getSingleton('core/session')->getsoftwareProdPrice();
        $softProd+=$total;
        Mage::getSingleton('core/session')->setsoftwareProdPrice($softProd);
        if(!empty($data)){
            $customprice = $data['selectedMaintenanceProductPrice'];
        }




//echo $customprice; exit;
		$calculatedPrice=$percent='';
		$product = Mage::getModel('catalog/product')->load($prodId);
		/*if(count($productOptions['options'])>0){
			foreach ($productOptions['options'] as $key => $value) {
				$product = Mage::getModel("catalog/product")->load($product->getId()); 
				foreach ($product->getOptions() as $o) {
					$values = $o->getValues();
					if ($o->getTitle() == 'Years of Maintenance') { 
						foreach ($values as $v) {
							if ($value['option_value'] == $v->getOptionTypeId()) {
								$percent = $v->getprice(); 
								$years = $v->getTitle();
							}
						}
					}
				}
			}
		}else{
			$product = Mage::getModel("catalog/product")->load($product->getId()); 
				foreach ($product->getOptions() as $o) {
					$values = $o->getValues();
					if ($o->getTitle() == 'Years of Maintenance') { 
						foreach ($values as $v) {
							if(strtolower($v->getTitle())=='1 year'){
								$defaultPercent=$v->getprice(); 
							}
						}
					}
				}
		}*/
		$percent = $product->getData('tot_software_perc');

		if($percent==''){
			$calculatedPrice=$customprice;
		}else{
			$calculatedPrice=($total/100)*$percent;
		}

        if($softProd!==''){
            $calculatedPrice=($softProd/100)*$percent;

        }
//echo $customprice.'<br/>';
//echo $softProd.'<br/>';
//echo $total.'<br/>';
//echo $calculatedPrice;
//        exit;
		if($customprice<$calculatedPrice)
			$customprice=$calculatedPrice;

		$item->setCustomPrice($customprice);
		$item->setOriginalCustomPrice($customprice);
		$item->getProduct()->setIsSuperMode(true);
	}

public function sendQuoteCreatedEmail($data){
    $rawData = $data->getData();
    $currency_code = Mage::app()->getStore()->getCurrentCurrencyCode();
    $ItemData ='';
    if($rawData['customer']['order_confirmation_email']){
             $allemails = $rawData['customer']['order_confirmation_email'];
            $email_to=explode(",",$allemails);
        }
        $email_to[] = $rawData['customer']['email'];

//    $email_to = 'venkatalapati20097@gmail.com';

    // This is the template name from your etc/config.xml
        $template_id = 'custom_template';
        // Who were sending to...
        $customer_name   = $rawData['customer']['firstname'].$rawData['customer']['lastname'];
    $address = Mage::getModel('customer/customer')->load($rawData['customer']['entity_id']);
//    TO load customer address
    foreach ($address->getAddresses() as $address){
        $data = $address->toArray();
    }
        $customer_address   = ucfirst($data['street']).',<br/>'.ucwords($data['city'].',<br/>'.$data['country_id']
                    .',<br/>'.$data['postcode'].'.');

//    Load the items for template
    $productids = explode(",",$rawData['quote']['quote_product_ids']);
    $productqtys = explode(",",$rawData['quote']['quote_product_qtys']);


    if($currency_code == 'AUD'){
        $productprices = explode(",",$rawData['quote']['quote_product_prices_aud']);
    }else{
        $productprices = explode(",",$rawData['quote']['quote_product_prices_nzd']);
    }
    $total = 0;
    for($i=0;$i<count($productids);$i++){
        $total += $productprices[$i];
        $_product = Mage::getModel('catalog/product')->load($productids[$i]);
        $ItemData .= "<tr><td valign='top' style='font-size:12px; text-align: center;padding:7px 9px 9px 9px; border-left:1px solid
#EAEAEA; border-bottom:1px solid #EAEAEA; border-right:1px solid #EAEAEA;'>".$_product->getData('name')."</td><td valign='top'
style='font-size:12px; text-align: center;padding:7px 9px 9px 9px; text-align: center;
border-left:1px solid #EAEAEA; border-bottom:1px solid #EAEAEA; border-right:1px solid #EAEAEA;'>".$productqtys[$i]."
</td><td valign='top' style='font-size:12px; text-align: center; padding:7px 9px 9px 9px; border-left:1px solid
#EAEAEA; border-bottom:1px solid #EAEAEA; border-right:1px solid #EAEAEA;'>".$productprices[$i]."</td></tr>";
    }
        // Load our template by template_id
        //For templates which are useed in database

        $email_template  = Mage::getModel('core/email_template')->loadByCode('quote_created_template'); //where 'custom_template' is the name of template

        //If you define in config.xml then please use below code.
//        $email_template  = Mage::getModel('core/email_template')->loadDefault($template_id);
    $searches = array('{{quote.customerName}}', '{{quote.id}}','{{quote.createdAt}}','{{quote.customerAddress}}',
        '{{itemData}}','{{currency_code}}','{{total}}');
    $replacements = array($customer_name, $rawData['quote']['quote_id'],$rawData['quote']['created_date'],
        $customer_address,$ItemData,$currency_code,$total);
    $finalData = str_replace($searches, $replacements, $email_template->getData()['template_text']);
    $email_template->setData()['template_text'] = $finalData;
    // Here is where we can define custom variables to go in our email template!

        $email_template_variables = array(
          // Other variables for our email template.
        );

        // I'm using the Store Name as sender name here.
        $sender_name = Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_STORE_STORE_NAME);
        // I'm using the general store contact here as the sender email.
        $sender_email = Mage::getStoreConfig('trans_email/ident_general/email');
        $email_template->setSenderName($sender_name);
        $email_template->setSenderEmail($sender_email);
        $email_template->setTemplateSubject('Quote created by '.$customer_name);

        //Send the email to the sub account
        $email_template->send($email_to, $customer_name, $email_template_variables);
//        Send the email to the sub account master customer
        if($rawData['customer']['master_customer']){
            $masterCustomer = Mage::getModel('customer/customer')->load($rawData['customer']['master_customer']);
            $email_to = $masterCustomer->getData('email');
            $email_template->send($email_to, $customer_name, $email_template_variables);
        }

    }
}
?>
