<?php
class Synapse_Quote_Model_Price_Observer
{
    public function applyQuotePrice(Varien_Event_Observer $observer){
     	$item = $observer->getQuoteItem();
       if(Mage::getSingleton('core/session')->getIsQuoteAddedToCart()){

			$quote_id = Mage::getSingleton('core/session')->getCurrentQuote();
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
			if($attribTxt=='software product' or $attribTxt=='maintenance product')
			    $this->getCustomPrice();
		}



		/*else{
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
			else{
			    $this->getCustomPrice();
			}
		}*/

//        print_r($this->getCustomPrice()); exit;
		$this->getCustomPrice();


	    
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
}
?>
