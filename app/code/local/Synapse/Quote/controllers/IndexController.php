<?php
class Synapse_Quote_IndexController extends Mage_Core_Controller_Front_Action {

    	public function saveAction(){

			$data = $this->getRequest()->getPost();
            echo '<pre>';

           $session = Mage::getSingleton("core/session");
			$this->_initLayoutMessages('customer/session');
			$products=array();
			$maintenance_required=false;

			if(!$data['quoteid']){ #if it's a new quote
                $quote_customer_id = Mage::getSingleton('customer/session')->getCustomer()->getId();
				$quote_product_ids = Mage::getSingleton('customer/session')->getNewQuote();
                print_r($data);
                print_r($quote_product_ids);

//				$quote_product_options = Mage::getSingleton('customer/session')->getProdOptions();
                $qtys='';
				$model = Mage::getModel('quote/quote');
				foreach($quote_product_ids as $quote_prod){
					$prod_id=$quote_prod;

//					if(in_array($prod_id,$products))
//						continue;

					$product = Mage::getModel('catalog/product')->load($prod_id);
					$prodType = $product->getAttributeText('product_type');
					$qty=$data['qty'][$quote_prod];
//                    echo '6';
//
                    if(strtolower($prodType)=='maintenance product')
						$maintenance_required=true;

					$prod_price = $product->getFinalPrice($qty);
					$aud_prices[] = $prod_price;
					$nzd_prices[] = $this->priceInNZD($prod_price);
					$products[] = $prod_id;
					$qtys[] = $qty;
				}

				if($maintenance_required)
					$this->updateQuote4Maintenance($products, $qtys, $aud_prices, $nzd_prices);

				$model->setData('quote_customer_id', $quote_customer_id);
				$model->setData('quote_product_ids', implode(',',$products));
				$model->setData('quote_product_qtys', implode(',',$qtys));
				$model->setData('quote_service_num', $data['service_number']);
				$model->setData('quote_product_prices_aud', implode(',',$aud_prices));
				$model->setData('quote_product_prices_nzd', implode(',',$nzd_prices));
//				$model->setData('quote_product_params',$quote_product_options);
				$model->setData('created_date', date('Y-m-d'));
				$model->setData('updated_date', date('Y-m-d'));
				$model->setData('is_approved',0);
				try{
					$model->save();
                    if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                        // Load the customer's data
                        $customer = Mage::getSingleton('customer/session')->getCustomer();
                       // Mage::getModel('quote/quote')->sendQuoteCreatedEmail($customer);
//                        Mage::dispatchEvent()
                    }

//                    $event_data_array  =  array('customer' => $customer);
//                    Mage::dispatchEvent('send_quote_created_email', $event_data_array);

//					$session->addSuccess("Quote saved successfully");
					$this->_redirect('*/*/quotes');
				} catch(Exception $e){
					$session->addError("Unable to add the product to Quote");
					$url = Mage::getSingleton('core/session')->getLastUrl();
					$this->_redirectUrl($url);
				}
			}else if(isset($data['quoteid']) && $data['quoteid']!=""){

				$model = Mage::getModel('quote/quote');
				$existing_quote_record = $model->load($data['quoteid']);
				$quote_details = $existing_quote_record->getData();
			    $quote_product_ids=$existing_quote_record->getData('quote_product_ids');
                $quote_product_ids=explode(',',$quote_product_ids);
				$aud_prices=$existing_quote_record->getData('quote_product_prices_aud');
				$aud_prices=explode(',',$aud_prices);
				$nzd_prices=$existing_quote_record->getData('quote_product_prices_nzd');
				$nzd_prices=explode(',',$nzd_prices);
				$qtys=array();
				$maintenance_prod=0;
				foreach($quote_product_ids as $quote_prod){
					if(!in_array($quote_prod,$products)){
						$product = Mage::getModel('catalog/product')->load($quote_prod);
					    $attribTxt = $product->getAttributeText('product_type');

						if(strtolower($attribTxt)=='maintenance product')
							$maintenance_prod=$quote_prod;

						$products[]=$quote_prod;
						$qtys[]=$data['qty'][$quote_prod];
					}
				}
				if($maintenance_prod>0)
					$this->updateQuote4Maintenance($products,$qtys,$aud_prices,$nzd_prices);

				$existing_quote_record->setData('quote_product_qtys', implode(',',$qtys));
				$existing_quote_record->setData('quote_product_ids',implode(',',$products));
				$existing_quote_record->setData('quote_service_num', $data['service_number']);
				$existing_quote_record->setData('quote_product_prices_aud', implode(',',$aud_prices));
				$existing_quote_record->setData('quote_product_prices_nzd', implode(',',$nzd_prices));
				try{
					$existing_quote_record->save();
                    $session->addSuccess("The Quote has been saved");
				} catch(Exception $e){
					$session->addError("Unable to save the Quote");
				}
		        $url = Mage::getSingleton('core/session')->getLastUrl();
				$this->_redirectUrl($url);
			}

		}
		private function priceInNZD($price){
			return Mage::helper('directory')->currencyConvert($price, AUD, NZD);
		}
		public function quotesAction(){
			$this->loadLayout();
			$this->_initLayoutMessages('customer/session');
			$this->getLayout()->getBlock('head')->setTitle($this->__('My Quotes'));
			$this->renderLayout();
		}
		public function viewAction(){
			$quote_id = $this->getRequest()->getParam('id');
			Mage::getSingleton('customer/session')->setCurrentquote($quote_id);
			$current_quote = Mage::getModel('quote/quote')->load($quote_id);
			$session = Mage::getSingleton("core/session");
			$this->_initLayoutMessages('customer/session');
			if($current_quote->getId()){
				$quote_products = explode(',',$current_quote['quote_product_ids']);
				$quote_qtys = explode(',',$current_quote['quote_product_qtys']);
				$quote_prices_AUD = explode(',',$current_quote['quote_product_prices_aud']);
				$quote_prices_NZD = explode(',',$current_quote['quote_product_prices_nzd']);
				$quote_products=array_filter($quote_products);
				$quote_qtys=array_filter($quote_qtys);
				$quote_prices_AUD=array_filter($quote_prices_AUD);
				$quote_prices_NZD=array_filter($quote_prices_NZD);
				if($quote_products){
				$quote_product_qty = array_combine($quote_products,$quote_qtys);
				$valid_prods=$valid_qtys=$valid_prices_AUD=$valid_prices_NZD=array();
				$i=0;
				foreach($quote_product_qty as $k => $v){
					$check_product_type = explode('-',$k);
					if($check_product_type[0]!="" && $check_product_type[1]!="" && substr($check_product_type[1],0,3)=="sub"){
						$product = Mage::getModel('catalog/product')->load($check_product_type[0]);
					}
					else{
						$product = Mage::getModel('catalog/product')->load($k);
					}
					if ($product->getId() && $product->getStatus()==1) {
						$valid_prods[]= $k;
						$valid_qtys[]= $v;
						$valid_prices_AUD[]=$quote_prices_AUD[$i];
						$valid_prices_NZD[]=$quote_prices_NZD[$i];
					}else{
						$array_Invalid[]= $k;
					}
				}
				if(count($array_Invalid)>0)
				{
					$current_quote->setData('quote_product_ids', implode(',',$valid_prods));
					$current_quote->setData('quote_product_qtys', implode(',',$valid_qtys));
					$current_quote->setData('quote_product_prices_aud',implode(',',$valid_prices_AUD));
					$current_quote->setData('quote_product_prices_nzd',implode(',',$valid_prices_NZD));
					$current_quote->save();
					$session->addError("Invalid product(s) are removed from the Quote");
				}
			}
			}
			$this->loadLayout();
			$this->getLayout()->getBlock('head')->setTitle($this->__("Quote $quote_id"));
			$this->renderLayout();
		}
		public function refreshAction(){
			$session = Mage::getSingleton("core/session");
			$this->_initLayoutMessages('customer/session');
			$quote_id = $this->getRequest()->getParam('id');
			$model = Mage::getModel("quote/quote");
                  	$existing_quote_record = $model->load($quote_id);
			$quote_details = $existing_quote_record->getData();
			$quote_products = explode(',',$quote_details['quote_product_ids']);
			foreach($quote_products as $quote_product){
			$product = Mage::getModel('catalog/product')->load($quote_product);
			  if($product->getSpecialPrice()){
			       $prod_price_in_aud[] = $product->getSpecialPrice();
			       $prod_price_in_nzd[] = Mage::helper('directory')->currencyConvert($product->getSpecialPrice(), AUD, NZD);
			  }else{
				$prod_price_in_aud[] = $product->getPrice();
				$prod_price_in_nzd[] = Mage::helper('directory')->currencyConvert($product->getPrice(), AUD, NZD);
			  }
			}
			$existing_quote_record->setData('quote_product_prices_aud', implode(',', $prod_price_in_aud));
			$existing_quote_record->setData('quote_product_prices_nzd', implode(',', $prod_price_in_nzd));
			$existing_quote_record->setData('updated_date', date('Y-m-d'));
			try{
				$existing_quote_record->save();
				$session->addSuccess("The Quote has been refreshed");
			} catch(Exception $e){
				$session->addError("Unable to add the product to Quote");
			}
                        $url = Mage::getSingleton('core/session')->getLastUrl();
			$this->_redirectUrl($url);
		}
		public function addCartAction(){
            Mage::getSingleton('customer/session')->unsQuoteCreatedThroughUpload();
			$session = Mage::getSingleton("core/session");
			$this->_initLayoutMessages('customer/session');
			$quote_id = $this->getRequest()->getParam('id');
			$session->setIsQuoteAddedToCart(1);
			$session->setCurrentQuote($quote_id);
			$model = Mage::getModel("quote/quote");
            $existing_quote_record = $model->load($quote_id);
			$quote_details = $existing_quote_record->getData();
            $quote_products = explode(',',$quote_details['quote_product_ids']);
			$quote_qtys = explode(',',$quote_details['quote_product_qtys']);
			$quote_product_qty = array_combine($quote_products,$quote_qtys);
			$quote_product_price_aud =explode(',',$quote_details['quote_product_prices_aud']);
			$quote_product_price_nzd =explode(',',$quote_details['quote_product_prices_nzd']);
            $quote_product_params = unserialize($quote_details['quote_product_params']);
            $storeId = Mage::app()->getStore()->getStoreId();
			foreach ($quote_product_qty as $k => $v){
					$_product = Mage::getModel('catalog/product')->load($k);
					$attribTxt = $_product->getAttributeText('product_type');
					$addProd2Cart=true;
                	if(strtolower($attribTxt)=='maintenance product'){
						$items=Mage::getModel('checkout/cart')->getQuote()->getAllItems();
						foreach ($items as $item) {
							$prod=$item->getProduct();
							$productId=$prod->getId();
							$prod1 = Mage::getModel('catalog/product')->setStoreId($storeId)->load($productId);
							$attribTxt1 = $prod1->getAttributeText('product_type');
							if(strtolower($attribTxt1)=='maintenance product'){
								$addProd2Cart=false;
							}
						}
					}
					if(!$addProd2Cart){
						$session->addError("Upgrade Assurance product already exists in the cart");
						continue;
					}
					if($_product->getId()){
						$cart = Mage::getModel('checkout/cart');
						$cart->init();
						$cart->addProduct($_product, array('qty' => $v));
						$cart->save();
					}
					else{
						$session = Mage::getSingleton("core/session");
						$this->_initLayoutMessages('customer/session');
						$session->addError("As ".$k." Product is not available, its not added to the cart");
				    }
					Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
			}
			Mage::getSingleton('checkout/session')->setData('enduserorganization',$quote_details['quote_service_num']);
			$session->unsCurrentQuote();
			$session->unsIsQuoteAddedToCart();

			$this->_redirect('checkout/cart');
		}
		public function deleteAction(){
            $session = Mage::getSingleton("core/session");
			$this->_initLayoutMessages('customer/session');
			$quote_id = $this->getRequest()->getParam('id');
            $model = Mage::getModel("quote/quote");
			$model->setId($quote_id)->delete();
			$session->unsCurrentQuote();
			$session->addSuccess("Quote deleted successfully");
			$this->_redirect('*/*/quotes');
		}
		public function removeitemAction(){
            $session = Mage::getSingleton("core/session");
			Mage::getSingleton('customer/session')->addSuccess('YOUR MSG');
			$this->_initLayoutMessages('customer/session');
			$quote_id = Mage::getSingleton('customer/session')->getCurrentquote();
            $item_id = $this->getRequest()->getParam('id');
            if(isset($quote_id) && $quote_id!=""){

                $params = $this->getRequest()->getParam('params');
                if(isset($params)){
                    $decodedParams = unserialize($params);
                }

				$model = Mage::getModel("quote/quote");
				$quote = $model->load($quote_id);
		        $quote_details = $quote->getData();
                $quoteProds = explode(',',$quote_details['quote_product_ids']);
				$aud_prices = explode(',',$quote_details['quote_product_prices_aud']);
				$prod_qtys = explode(',',$quote_details['quote_product_qtys']);
				$nzd_prices = explode(',',$quote_details['quote_product_prices_nzd']);
                $alreadyAddedProductParams = unserialize($quote_details['quote_product_params']);
                $alreadyAddedProductParams = array_filter($alreadyAddedProductParams);
                $maintenanceProd='';
                foreach($quoteProds as $k=>$v){
                    if($v == $item_id){
                        if(array_key_exists($item_id,$alreadyAddedProductParams)){
                            foreach($alreadyAddedProductParams[$item_id] as $key => $value){

                                $itemToBeRemoved = array_intersect($value,$decodedParams);
                                if(!empty($itemToBeRemoved) && (sizeof($itemToBeRemoved)>1)){
                                    unset($alreadyAddedProductParams[$item_id][$key]);
                                    break;
                                }

                            }
                            $rearrangingIndex = array_values($alreadyAddedProductParams[$item_id]);
                            $alreadyAddedProductParams[$item_id]= $rearrangingIndex;



                            $updatedQuoteParamsData = serialize($alreadyAddedProductParams);
                                if(sizeof($alreadyAddedProductParams[$item_id])==0)
                                    unset($quoteProds[$k]);

                        }else{
                            unset($quoteProds[$k]);
                            unset($aud_prices[$k]);
                            unset($prod_qtys[$k]);
                            unset($nzd_prices[$k]);
                        }


                    }

                    $prod = Mage::getModel('catalog/product')->load($v);
                    $attribTxt1 = $prod->getAttributeText('product_type');
                    if(strtolower($attribTxt1)=='maintenance product')
                        $maintenanceProd=$prod;
                }


				#if deleted product is a software product and quote contains maintenance/support product update maintenance product price
				$product = Mage::getModel('catalog/product')->load($item_id);
				$attribTxt = $product->getAttributeText('product_type');
				if(strtolower($attribTxt)=='software product' && !empty($maintenanceProd)){
					$this->updateQuote4Maintenance($quoteProds, $prod_qtys, $aud_prices,$nzd_prices);
				}

				$quote->setData('quote_product_ids', implode(',', $quoteProds));
				$quote->setData('quote_product_prices_aud', implode(',', $aud_prices));
				$quote->setData('quote_product_qtys', implode(',', $prod_qtys));
				$quote->setData('quote_product_prices_nzd', implode(',', $nzd_prices));
                if(isset($params)){

                    $quote->setData('quote_product_params',$updatedQuoteParamsData);
                }

				try{
					$quote->save();
                    $session->addSuccess("The Item has been deleted successfully");
					$url = Mage::getSingleton('core/session')->getLastUrl();
					Mage::getSingleton('customer/session')->setNewQuote($quoteProds);
					$this->_redirectUrl($url);
				} catch(Exception $e){
					$session->addError("Unable to delete the item from the Quote");
					$this->_redirect('*/*/quotes');
				}
			}else{

                $quoteProds = Mage::getSingleton('customer/session')->getNewQuote();
                $arr = Mage::getSingleton('customer/session')->getProdOptions();
                $params = $this->getRequest()->getParam('params');
                $productOptions = unserialize($arr);
                $productOptions = array_filter($productOptions);
                if(isset($params)){
                    $decodedParams = unserialize($params);

                }
                foreach($quoteProds as $k=>$prod){
                    if($prod == $item_id){
                        if(array_key_exists($item_id,$productOptions)){
                            foreach($productOptions[$item_id] as $key => $value){
                                $itemToBeRemoved = array_intersect($value,$decodedParams);

//                                print_r($value);
                                if(!empty($itemToBeRemoved) && (sizeof($itemToBeRemoved)>1)){
                                    unset($productOptions[$item_id][$key]);
                                    break;
                                }
                            }

                            $rearrangingIndex = array_values($productOptions[$item_id]);
                            $productOptions[$item_id]= $rearrangingIndex;
                            $updatedQuoteParamsData = serialize($productOptions);
                            if(sizeof($productOptions[$item_id])==0){
                                unset($quoteProds[$k]);
                            }
                        }else{
                            $updatedQuoteParamsData = serialize($productOptions);
                            unset($quoteProds[$k]);
                        }

					}
				}

				Mage::getSingleton('customer/session')->setNewQuote($quoteProds);
                Mage::getSingleton('customer/session')->setProdOptions($updatedQuoteParamsData);
				$session->addSuccess("The Item has been deleted successfully");
				$url = Mage::getSingleton('core/session')->getLastUrl();
				$this->_redirectUrl($url);
			}

		}
		private function updateQuote4Maintenance($products,$qtys,&$aud_prices,&$nzd_prices){
			$software_prod_total=0;
			foreach($products as $k=>$v){
				$prod_id=$products[$k];
				$product = Mage::getModel('catalog/product')->load($prod_id);
				$attribTxt = $product->getAttributeText('product_type');

				#Check for Software products and get the total
				if(strtolower($attribTxt)=='software product'){
					$calc=$aud_prices[$k]*$qtys[$k];
					$software_prod_total=$software_prod_total+$calc;
				}

				#if product is a maintenance product hold the details and array index
				if(strtolower($attribTxt)=='maintenance product'){
					$maintenance_prod=$product;
					$j=$k;
				}
			}
			# if we have maintenance product get info and calculate the price
			if($maintenance_prod>0){
				$product = $maintenance_prod;
				$percent = $product->getData('tot_software_perc');
				$calc_price=($software_prod_total/100)*$percent;
				$default_price=$product->getFinalPrice();
				if($calc_price<$default_price)
					$calc_price=$default_price;
				$aud_prices[$j]=$calc_price;
				$nzd_prices[$j]=$this->priceInNZD($calc_price);
			}
		}
		public function licenseQuoteToCartAction(){
            Mage::getSingleton('core/session')->unsCheckMaintenanceProduct();
            $session = Mage::getSingleton("core/session");
            $maintenanceProductAdded='';
            $this->_initLayoutMessages('customer/session');
            $isLicenseUploaded = Mage::getSingleton('customer/session')->getUploadedLicense();
            if($isLicenseUploaded){
                $new_quote = Mage::getSingleton('customer/session')->getproducts();
                $productIds = array_keys($new_quote);
                $quote_items = array_count_values($productIds);
                $quoteQty = Mage::getSingleton('customer/session')->getQuoteQty();
                $price = Mage::getSingleton('core/session')->getProductsPrice();

            }else{
                $new_quote = Mage::getSingleton('customer/session')->getNewQuote();
                $quote_items = array_count_values($new_quote);
            }
            $result = array();
            $currency_code = Mage::app()->getStore()->getCurrentCurrencyCode();
            foreach ($quote_items as $k => $v){
				$model = Mage::getModel('catalog/product');
				$_product = $model->load($k);
                $prodType = $_product->getAttributeText('product_type');
                if(strtolower($prodType)=='software product'){

                    $softwareProd = 0;
                    $item_price=$price[$k];
                    if($currency_code!= 'AUD'){
                        $item_price=$this->priceInNZD($item_price);
                    }
                    $softwareProd=$softwareProd+$item_price;
                    Mage::getSingleton('core/session')->setsoftwareProdPrice($softwareProd);
                    continue  ;
                }
                if(strtolower($prodType)=='maintenance product'){
                    if($maintenanceProductAdded)
                        continue;
                    $maintenanceProductAdded =1;
                }
                try{
					$cart = Mage::getModel('checkout/cart');
					$cart->init();
                    $cart->addProduct($_product, array('qty' => $quoteQty[$k]));
					$cart->save();
                    $quoteAddedToCart = true;
                    Mage::getSingleton('core/session')->setIsQuoteAddedToCart($quoteAddedToCart);
					Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
                    $result[] = "success";
				}catch(Exception $e){
					$result[] = "failed";
				}

			}
			if(in_array("failed", $result)){
				$session->addError("Quote cannot be added to cart");
				$url = Mage::getSingleton('core/session')->getLastUrl();
				$this->_redirectUrl($url);
			}else{
				$this->_redirect('checkout/cart');
			}
            //exit;
		}
		public function createAction()
		{
            $session=Mage::getSingleton('customer/session');
			$quote = $this->getRequest()->getParam('quote');
            if(empty($quote)){
				$session->unsetData('currentquote');
				$session->unsetData('current_quote');
				$session->unsetData('new_quote');
			}
			$this->loadLayout();
			$this->getLayout()->getBlock('head')->setTitle($this->__("Create Quote"));
			$this->renderLayout();
		}
		public function addservicenumberAction()
		{
			$service_num = $this->getRequest()->getParam('number');
			if(isset($service_num) && $service_num!="")
			Mage::getSingleton('customer/session')->setNewservicenumber($service_num);
		}

		public function addproductAction()
		{
            if($_POST['super_attribute']) {
                $selectedOptions['super_attribute'] = $_POST['super_attribute'];
                $selectedOptions['super_attribute']['qty'] = $_POST['qty'];
            }
			if($_POST['bundle_option']) {
                $selectedOptions['bundle_option'] = $_POST['bundle_option'];
                $selectedOptions['bundle_option']['qty'] = $_POST['qty'];
            }

			$session = Mage::getSingleton("core/session");
			$this->_initLayoutMessages('customer/session');
			if($_POST['product'])
				$productId =	$_POST['product'];
			else
			    $productId = $this->getRequest()->getParam('id');

            // For saving the selected configurable product or bundle product options into the Quote Information table
            if(isset($selectedOptions)){
                if(array_key_exists('super_attribute',$selectedOptions)){
                    $selectedOptions['0'] = $selectedOptions['super_attribute'];
                    if(isset($_POST['ProdfinalPrice']))
                        $selectedOptions['0']['price'] = $_POST['ProdfinalPrice'];

                    unset($selectedOptions['super_attribute']);
                }
                if(array_key_exists('bundle_option',$selectedOptions)){
                    $selectedOptions['0'] = $selectedOptions['bundle_option'];
                    if(isset($_POST['ProdfinalPrice']))
                        $selectedOptions['0']['price'] = $_POST['ProdfinalPrice'];
                    unset($selectedOptions['bundle_option']);
                }
                $arr[$productId]= $selectedOptions;
            }

			$requestType = $this->getRequest()->getParam('requestType');
			$quoteId = Mage::getSingleton('customer/session')->getCurrentquote();
			if(isset($quoteId) && $quoteId!="" && $productId){
				$model = Mage::getModel("quote/quote");
		        $existing_quote_record = $model->load($quoteId);
				$quote_details = $existing_quote_record->getData();
				$added_product_qtys=array();

                	if($quote_details['quote_product_params']){
                   		$productOptions = unserialize($quote_details['quote_product_params']);
//
                        foreach($productOptions as $productOption=>$val){
                            //print_r($val); exit;
                            if(array_key_exists($productId,$productOptions)){
                                $latestAttributes = $this->compareAttributes($selectedOptions,$val);
                                $newAttributes = $latestAttributes['newAttributeProduct'];
                              //  print_r($newAttributes); exit;
                                $index =  $latestAttributes['index'];

                             // To check whether the product exists or not
                                if($newAttributes){
  // If product exists, We are checking whether only the quantity changed or the attributes also changed. If only
  // quantity changes we will update only quantity or else we will add it as new product

                                    if(sizeof($newAttributes) > 0){

                                        // Working Perfect

                                        if(!is_null($index)){
                                          $productOptions[$productId][$index]['qty'] = $newAttributes['qty'];
                                          $arr = $productOptions;

                                        }else {

                                            // Working Perfect

                                            // If attributes are changed, we are pushing it as a different product
                                            $optionsPush = $newAttributes;
                                            $productOptions[$productId][] = $optionsPush;
                                            $arr = $productOptions;
                                        }

                                       }}
                        }else{
                                $productOptions[$productId] = $selectedOptions;
                                $arr = $productOptions;
                            }
                        }
			 }

                if($quote_details['quote_product_ids']){
					$added_products = explode(',',$quote_details['quote_product_ids']);
					$added_product_qtys= explode(',',$quote_details['quote_product_qtys']);
					$aud_prices = explode(',',$quote_details['quote_product_prices_aud']);
					$nzd_prices = explode(',',$quote_details['quote_product_prices_nzd']);

				}


				#Check for maintenance product existance
				$product = Mage::getModel('catalog/product')->load($productId);
				$prodType = $product->getAttributeText('product_type');
//                echo '<pre>';
//                print_r(strtolower($prodType));
//                print_r($added_products);
//                exit;
				if(strtolower($prodType)=='maintenance product'){
                    foreach($added_products as $prod){
						$product1 = Mage::getModel('catalog/product')->load($prod);
						$prodType1 = $product1->getAttributeText('product_type');
						if(strtolower($prodType1)=='maintenance product'){
							$session->addError("Upgrade Assurance product was added to the quote");
							$this->_redirect('*/*/quotes');
							return;
						}
					}
				}

                // To check for the price depending on the selected options

				if(!in_array($productId,$added_products)){

                        $added_products[] = $productId;
                        $qty=1;
                        $prod_price=$product->getFinalPrice();


				if($_POST['ProdfinalPrice'])
						$prod_price=$_POST['ProdfinalPrice'];
					if($_POST['qty'])
						$qty=$_POST['qty'];
					$added_product_qtys[] =$qty;
					$aud_prices[] = $prod_price;
					$nzd_prices[] = $this->priceInNZD($prod_price);

				}else{
               // For adding the selected configurable product or bundle product options into the Quote table which
               // is previously added
                $key=array_search($productId,$added_products);
                    if(!in_array($productId,$productOptions)){


                        ++$added_product_qtys[$key];
                    }


				}
				if(!is_object($product))
					$product = Mage::getModel('catalog/product')->load($productId);
					$attribTxt = $product->getAttributeText('product_type');
					#If added product is a software product update the quote for maintenance price
					if(strtolower($attribTxt)=='software product'){
						$this->updateQuote4Maintenance($added_products, $added_product_qtys, $aud_prices,$nzd_prices);
					}
                $productOptions=serialize(array_filter($arr));
                $existing_quote_record->setData('quote_product_ids', implode(',', $added_products));
				$existing_quote_record->setData('quote_product_prices_aud', implode(',', $aud_prices));
				$existing_quote_record->setData('quote_product_qtys', implode(',', $added_product_qtys));
				$existing_quote_record->setData('quote_product_prices_nzd', implode(',', $nzd_prices));
                $existing_quote_record->setData('quote_product_params', $productOptions);
				try{
					$existing_quote_record->setData('is_approved', 0);
					$existing_quote_record->save();
					$session->addSuccess("The Item is added to the Quote successfully");
					if(empty($requestType))
						$this->_redirect('*/*/quotes');
					else
						echo 'success';
				} catch(Exception $e){
					$session->addError("Unable to add the product to Quote");
					if(!empty($requestType))
						echo 'failure';
				}
			}else if(isset($quoteId) && $quoteId!=""){
				$this->_redirectUrl(Mage::getUrl('education.html'));
			}else if(!$productId && $quoteId==""){
				$this->_redirectUrl(Mage::getUrl('education.html'));
			}else if($productId && $quoteId==""){
                	$already_added_products = Mage::getSingleton('customer/session')->getNewQuote();
                	$already_added_product_qtys = Mage::getSingleton('customer/session')->getProdQtys();
					$options = Mage::getSingleton('customer/session')->getProdOptions();
                    $productOptions = unserialize($options);

// To update quantity for already added configurable product

                    foreach($productOptions as $productOption=>$val){
                    if(array_key_exists($productId,$productOptions)){

                        $latestAttributes = $this->compareAttributes($selectedOptions,$val);
                        $newAttributes = $latestAttributes['newAttributeProduct'];

                        $index =  $latestAttributes['index'];

                        // To check whether the product exists or not
                        if($newAttributes){
                            // If product exists, We are checking whether only the quantity changed or the attributes also changed. If only
                            // quantity changes we will update only quantity or else we will add it as new product

                            if(sizeof($newAttributes) > 0){

                                // Working Perfect

                                if(!is_null($index)){

                                    $productOptions[$productId][$index]['qty'] = $newAttributes['qty'];
                                    $arr = serialize($productOptions);
                                }else {


                                    // Working Perfect

                                    // If attributes are changed, we are pushing it as a different product
                                    $optionsPush = $newAttributes;
                                    $productOptions[$productId][] = $optionsPush;
                                    $arr = serialize($productOptions);
                                }

                            }}
                    }else{
                        $productOptions[$productId] = $selectedOptions;

                        $arr = serialize($productOptions);

                    }
                }
// If it is a first configurable product then we are saving the options
                if(empty($productOptions)){
                    $productOptions[$productId] = $selectedOptions;
                    $arr = serialize($productOptions);
                }

                $product = Mage::getModel('catalog/product')->load($productId);
                if(!in_array($productId,$already_added_products)){
                    $already_added_products[] = $productId;
                    $qty=1;
                    $already_added_product_qtys[$productId] = $qty;

                }else{
                    ++$already_added_product_qtys[$productId];
                    $arr = serialize(array_filter($productOptions));

                }


                $already_added_product_prices = Mage::getSingleton('customer/session')->getProdPrices();

                    $prod_price=$product->getFinalPrice();
                    if($_POST['ProdfinalPrice'])
                        $prod_price=$_POST['ProdfinalPrice'];

                    if($_POST['qty'])
                        $qty=$_POST['qty'];

                $price[] = $prod_price;

                    $already_added_product_prices[$productId] = $prod_price;

                    $added_product_qtys[$productId] =$qty;
                    $added_product_price[$productId] = $prod_price;

                	Mage::getSingleton('customer/session')->setNewQuote($already_added_products);
                	Mage::getSingleton('customer/session')->setProdQtys($already_added_product_qtys);
                	Mage::getSingleton('customer/session')->setProdPrices($already_added_product_prices);
 					Mage::getSingleton('customer/session')->setProdOptions($arr);
                    $session->addSuccess("The Item is added to Current Quote successfully");
					if(empty($requestType))
					 $this->_redirectUrl(Mage::getUrl('education.html'));
					else
						echo 'success';
			}

		}

// To compare for the attributes

    public function compareAttributes($selectedOptions,$val){
        foreach($val as $k => $value){
            $quantityChange = array_diff_assoc($selectedOptions[0],$value);
            if(sizeof($quantityChange) == 1 && isset($quantityChange['qty'])){
                $data['newAttributeProduct'] = $quantityChange;
                $data['index'] = $k;
                break;
            }
            else{

                $data['newAttributeProduct'] = $selectedOptions[0];
            }


        }
        return $data;

    }
}
?>
