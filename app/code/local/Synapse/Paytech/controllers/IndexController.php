<?php
ini_set('auto_detect_line_endings', true);
class Synapse_Paytech_IndexController extends Mage_Core_Controller_Front_Action {
	public function subaccountslistAction(){
		$this->loadLayout();
		$this->_initLayoutMessages('customer/session');
		$this->getLayout()->getBlock('head')->setTitle($this->__('My Sub Accounts'));		
		$this->renderLayout();
	}
    private function priceInNZD($price){
        return Mage::helper('directory')->currencyConvert($price, AUD, NZD);
    }

	public function subaccountsAction(){				
		$this->loadLayout();
		$this->_initLayoutMessages('customer/session');
		$this->renderLayout();
	}
	public function allordersAction(){
		$this->loadLayout();
		$this->renderLayout();
	}
	public function createsubaccountAction(){				
		$data = $this->getRequest()->getPost();
		$redirectUrl = Mage::getUrl('*/*/subaccounts', array('_secure' => true));
		$websiteId = Mage::app()->getWebsite()->getId();
		$store = Mage::app()->getStore();
		$current_customer = "";
		$current_customer = Mage::getSingleton('customer/session')->getCustomer()->getId();
		$current_customer_group_id = Mage::getSingleton('customer/session')->getCustomerGroupId();			 
		$customer = Mage::getModel("customer/customer");
		$customer->setWebsiteId($websiteId)
			    ->setStore($store)
			    ->setGroupId($current_customer_group_id)	
			    ->setMasterCustomer($current_customer)	
			    ->setFirstname($data['firstname'])
			    ->setLastname($data['lastname'])
			    ->setEmail($data['email'])
			    ->setPassword($data['password'])
			    ->setUsernamePapercut($data['username_papercut'])
			    ->setPasswordPapercut($data['password_papercut'])
			    ->setCanPlaceOrder($data['can_place_order'])
			    ->setOrderConfirmationEmail($data['order_confirmation_email']);
		if ($data['is_subscribed']) {
            $customer->setIsSubscribed(1);
        }

		try{
		    $customer->save();
		}
		catch (Exception $e) {
			//print_r($e->getMessage());exit;
			Mage::getSingleton('customer/session')->setData('postedData', $data);
		    Mage::getSingleton('customer/session')->addError($e->getMessage());
		    $this->_redirectError($redirectUrl);	
		}
		if($customer->getId()>0){
			$address = Mage::getModel("customer/address");
			$address->setCustomerId($customer->getId())
				->setFirstname($customer->getFirstname())
				->setMiddleName($customer->getMiddlename())
				->setLastname($customer->getLastname())
				->setCountryId($data['country_id'])
				//->setRegionId('1') //state/province, only needed if the country is USA
				->setPostcode($data['postcode'])
				->setCity($data['city'])
				->setTelephone($data['telephone'])			
				->setCompany($data['company'])
				->setStreet($data['street'])
				->setIsDefaultBilling('1')
				->setIsDefaultShipping('1')
				->setSaveInAddressBook('1');

                if($data['region']!=''){
					$address->setRegion($data['region']);
				}
				if($data['region_id']!=''){
					$address->setRegionId($data['region_id']);
				}
			try{
				$address->save();
				Mage::getSingleton('customer/session')->addSuccess($this->__('Sub Account has been successfully registered with your account'));		    
					$this->_redirectSuccess($redirectUrl);	
			}
			catch (Exception $e) {
				Mage::getSingleton('customer/session')->setData('postedData', $data);
				Mage::getSingleton('customer/session')->addError($e->getMessage());
				$this->_redirectError($redirectUrl);
			}
		}
	}
	public function subaccounteditAction(){
		$this->loadLayout();
		$this->getLayout()->getBlock('head')->setTitle($this->__('Edit Sub Account'));
		$this->renderLayout();
	}
	public function subaccounteditpostAction(){
		  $session = Mage::getSingleton("core/session");	
		  $data = $this->getRequest()->getPost();		
		  $this->_initLayoutMessages('customer/session');		
		  $websiteId = Mage::app()->getWebsite()->getId();
		  $store = Mage::app()->getStore(); 			 
		  $customer = Mage::getModel("customer/customer")->load($data['customerid']);
		  $customer->setWebsiteId($websiteId)
		    	   ->setStore($store)
		    	   ->setFirstname($data['firstname'])
		    	   ->setLastname($data['lastname'])
		    	   ->setEmail($data['email'])
			       ->setPassword($data['password'])
                   ->setUsernamePapercut($data['username_papercut'])
                   ->setPasswordPapercut($data['password_papercut'])
                   ->setCanPlaceOrder($data['can_place_order'])
                   ->setOrderConfirmationEmail($data['order_confirmation_email']);

		
		  $address = Mage::getModel("customer/address");
		  $address->setCustomerId($customer->getId())
			->setFirstname($data['firstname'])			
			->setLastname($data['lastname'])
			->setCountryId($data['country_id'])					
			->setPostcode($data['postcode'])
			->setCity($data['city'])
			->setRegion($data['region'])
			->setTelephone($data['telephone'])			
			->setCompany($data['company'])
			->setStreet($data['street'][0]." ".$data['street'][1])
			->setIsDefaultBilling('1')
			->setIsDefaultShipping('1')
			->setSaveInAddressBook('1');
	 	 try{
		    $customer->save();	
		    $address->save();
		    $session->addSuccess($this->__('Sub Account has been Edited successfully.'));	
		 }
		 catch (Exception $e) {
	 	    $session->addError($e->getMessage());
		 }
		 $this->_redirect('*/*/subaccountedit/id/'.$customer->getId());		  				  		   
	}
	public function deletesubaccountAction(){
		$this->_initLayoutMessages('customer/session');
		$id = $this->getRequest()->getParam('id');		
		Mage::register('isSecureArea', true);
		$customer = Mage::getModel('customer/customer')->load($id);
		try{
			$customer->delete();
			Mage::getSingleton('customer/session')->addSuccess($this->__('Sub Account has been deleted successfully.'));
		}catch(Exception $e){
			Mage::getSingleton('customer/session')->addSuccess($this->__('Unable to delete the Sub Account.'));
		}
		Mage::unregister('isSecureArea');
		$this->_redirect('*/*/subaccountslist/');
	}
	public function uploadAction(){
        // Perfectly working for papercut
        require_once(Mage::getBaseDir('lib') . '/Papercut/PaperCutResellerUrlBuilder.php');
        $key = Mage::getStoreConfig('paytech/papercut/secretkey');
        $authId = Mage::getStoreConfig('paytech/papercut/authId');
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
           $customer = Mage::getSingleton('customer/session')->getCustomer();
        }
        $resellerLogin = $customer->username_papercut;
        $baseUrl = Mage::getBaseUrl();
        $returnUrl = $baseUrl.'paytech/index/responsefrompapercut/';
        $url = PaperCutResellerUrlBuilder::create($authId,$resellerLogin,$returnUrl,$key);
        $this->_redirectUrl($url);


	}

    public function responsefrompapercutAction(){
        Mage::getSingleton('customer/session')->unsproducts();
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Upload License File'));
        $this->_initLayoutMessages('customer/session');
        $session = Mage::getSingleton("core/session");
        $products = array();
        $count = 0;
        $quotegst = 0;
        $quoteDiscount = 0;
        $quoteDiscountDescription = '';
        $products = $product = $quoteSubTotalForAllProducts = array();
        if(sizeof($_REQUEST['orderJson'])){
            $rawdata = json_decode($_REQUEST['orderJson']);
//echo '<pre>';
//print_r($rawdata);
//            exit;
            //        created for local purpose testing

//            $url = 'http://localhost/response.json';
//            $ch = curl_init();
//
//            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//            curl_setopt($ch, CURLOPT_URL, $url);
//
//            $data = curl_exec($ch);
//            $resultCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//            curl_close($ch);
//
//            if ($resultCode == 200) {
//                $rawdata  = json_decode($data);
//
////                print_r($rawdata);
////                exit;
//            } else {
//                return false;
//            }

            if($rawdata->isQuote){
                if (Mage::getSingleton('customer/session')->isLoggedIn()) {

                    // Load the customer's data
                    $current_customer = Mage::getSingleton('customer/session')->getCustomer();
                    $quote_customer_id = $current_customer->getId();

                }
                $items = $rawdata->orderLines;
                $license_file_created_by = $rawdata->resellerCode;
                $model = Mage::getModel('quote/quote');
                foreach($items as $item){
                    if($item->description){
                        if($item->description == "GST"){
                            $quotegst = $item->subtotal;
                        }
                        if($item->subtotal < 0){
                            $quoteDiscount = $item->subtotal;
                            $quoteDiscountDescription = $item->description;
                        }


                        $prodDetails = Mage::getModel('catalog/product')->loadByAttribute('sku_mapping',$item->description);
                        if($prodDetails['sku_mapping']==$item->description){
                            $ids[] = $product['id'] = $prodDetails->entity_id;
                            $product['name'] = $prodDetails->name;
                            $product['image'] = $prodDetails->image;
                            $quoteQty[$prodDetails->entity_id]=$qtys[] = $product['quantity'] = $item->quantity;
                            $quoteSubTotalForAllProducts[] = $product['subtotal'] = $item->subtotal;
                            $products[$prodDetails->entity_id] = $product;

                            $prod_price = $item->price*$item->quantity;
                            $price[$prodDetails->entity_id] = $aud_prices[] = $prod_price;
                            $nzd_prices[] = $this->priceInNZD($prod_price);
                        }
                            $model->setData('quote_customer_id', $quote_customer_id);
                            $model->setData('quote_product_ids', implode(',',$ids));
                            $model->setData('quote_product_qtys', implode(',',$qtys));
                            $model->setData('quote_service_num', $license_file_created_by);
                            $model->setData('quote_product_prices_aud', implode(',',$aud_prices));
                            $model->setData('quote_product_prices_nzd', implode(',',$nzd_prices));
                            $model->setData('created_date', date('Y-m-d'));
                            $model->setData('updated_date', date('Y-m-d'));
                            $model->setData('is_approved',0);
                            $model->setData('discount_description',$quoteDiscountDescription);
                            $model->setData('discount_value',$quoteDiscount);
                            $model->setData('gst',$quotegst);

                            $model->setData('quote_dump',serialize($_REQUEST['orderJson']));

                            try{
                                $model->save();
                                if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                                    // Load the customer's data
                                    $customer = Mage::getSingleton('customer/session')->getCustomer();
                                }

                                $event_data_array  =  array('customer' => $customer,'quote'=>$model);
//                $this->_redirect('quote/index/quotes/');

//                Mage::getSingleton('core/session')->setNewQuote($model);


                            } catch(Exception $e){
                                $session->addError("Unable to save to Quote");
                                $this->_redirect('*/*/');
                            }
                            $uploadedLicense = true;
                            $amount = array_sum($quoteSubTotalForAllProducts);
                        $quoteTotalAmount = $amount;
//                        echo $quoteTotalAmount; exit;
                            Mage::getSingleton('core/session')->setQuoteAmount('1262');
                            Mage::getSingleton('customer/session')->setproducts($products);
                            Mage::getSingleton('customer/session')->setproducts($products);
                            Mage::getSingleton('customer/session')->setQuoteCreatedThroughUpload($model['quote_id']);
                            Mage::getSingleton('customer/session')->setUploadedLicense($uploadedLicense);
                            Mage::getSingleton('customer/session')->setQuoteQty($quoteQty);
                            Mage::getSingleton('core/session')->setProductsPrice($price);
                            Mage::getSingleton('core/session')->setLicensefileCreatedBy($license_file_created_by);
                            Mage::getSingleton('core/session')->setQuoteDiscountDescription($quoteDiscountDescription);
                            Mage::getSingleton('core/session')->setQuoteDiscount($quoteDiscount);
                            Mage::getSingleton('core/session')->setQuotegst($quotegst);

                            if($count == 0){
                                $session->addError("No products were added since it doesn't match with our records.");
                            }
                            else{
                                $session->addError("Some products were not added since it doesn't match with our records.");
                            }

                    }
                }



            }
        }else{
            $noItems =true;
            Mage::getSingleton('customer/session')->setNotificationAboutNoProducts($noItems);

        }
        $this->renderLayout();
        if(sizeof($_REQUEST['orderJson'])){
          Mage::dispatchEvent('send_quote_created_email', $event_data_array);
         }
    }
}
?>
