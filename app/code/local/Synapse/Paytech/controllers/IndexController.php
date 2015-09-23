<?php
ini_set('auto_detect_line_endings', true);
class Synapse_Paytech_IndexController extends Mage_Core_Controller_Front_Action {
	public function subaccountslistAction(){
		$this->loadLayout();
		$this->_initLayoutMessages('customer/session');
		$this->getLayout()->getBlock('head')->setTitle($this->__('My Sub Accounts'));		
		$this->renderLayout();
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
        require_once(Mage::getBaseDir('lib') . '/Papercut/PaperCutResellerUrlBuilder.php');
        $key = Mage::getStoreConfig('paytech/papercut/secretkey');
        $authId = Mage::getStoreConfig('paytech/papercut/authId');
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
           $customer = Mage::getSingleton('customer/session')->getCustomer();
        }
        $resellerLogin = $customer->getName();
        $returnUrl = 'http://localhost/paytec/index.php/paytech/index/test/';
        $url = PaperCutResellerUrlBuilder::create($authId,$resellerLogin,$returnUrl,$key);
        //echo $url; exit;
        $this->_redirectUrl($url);

	}
	public function uploadLicenseAction(){
        $notificationAboutExpiry='';
        $session = Mage::getSingleton("core/session");
		$this->_initLayoutMessages('customer/session');
		$this->loadLayout();
		$file_errors=array(1=>'uploaded file exceeds the Max file size',
			2=>'uploaded file exceeds the Max file size',	
			3=>'uploaded file was only partially uploaded',
			4=>'No file was uploaded',
			6=>'Missing a temporary folder, please contact administrator',
			7=>'Failed to write file to disk, please contact administrator',
			8=>'Unknown error,please contact administrator');
		if(is_array($_FILES['uploadlicense'])){
			#Check for file upload errors
			if($_FILES['uploadlicense']['error']==0){
				$fileName_array=explode('.',$_FILES['uploadlicense']['name']);
				$file_extension=$fileName_array[count($fileName_array)-1];
				#Check for Valid file
				if(strtolower($file_extension)=='license'){
					$fileTmpLoc = $_FILES["uploadlicense"]["tmp_name"];
					$timestamp=date('Ymdhis');
					$targetdir = Mage::getBaseDir('media')."/customerlicense/".$timestamp;
					mkdir($targetdir,0777);
					$fileName=$fileName_array[0].'.zip';
					$targetzip = $targetdir.'/'.$fileName;
					#Check whether file moved successfully or not
					if(move_uploaded_file($fileTmpLoc, $targetzip)){
						$zip = new ZipArchive();
						$x = $zip->open($targetzip);  // open the zip file to extract
						if ($x === true) 
						{
							$zip->extractTo($targetdir); 
							$zip->close();
							unlink($targetzip);
							$handle = fopen($targetdir.'/license.txt', "r");
							$linestoskip=array('signature','updates-expiry-date','organization-name','updates-expiry-policy','created-by','unique-id','expiry-date','order-reference','issued-by');
							if ($handle) {
								//echo "<pre>";
								while (($line = fgets($handle)) !== false) {   
									if(strpos($line, "=")){	
										$line_keys = explode("=", $line);
										if($line_keys[0]=='organization-name')
											$license_file_created_by = $line_keys[1];	
										if($line_keys[1]=='0')
										 continue;
										//if(preg_match('/^ext-devices-/', $line_keys[0]))
										if(!in_array($line_keys[0],$linestoskip))
											$license_file_values[$line_keys[0]] = $line_keys[1];							


									}
								}


//                              For validating about Quote expiry
                                if(isset($license_file_values['support-expiry-date'])){
                                    $currentDate = new DateTime();
                                    $date2 = new DateTime($license_file_values['support-expiry-date']);
                                    $interval = $currentDate->diff($date2);

                                     if($interval->days > 30){
                                         $notificationAboutExpiry = false;
                                         $this->_redirect('*/*/upgradeSupport');

                                     }
                                    if($interval->days <= 30){
                                        $notificationAboutExpiry = true;
                                        Mage::getSingleton('core/session')->setNotificationAboutExpiry($notificationAboutExpiry);
                                    }
                                }


								if(count($license_file_values)>0){
									if(isset($license_file_values['users-licensed'])){
                                        if(isset($license_file_values['organization-type'])&&isset($license_file_values['edition'])){
												
										$edition=trim($license_file_values['edition']);
										$ot=trim($license_file_values['organization-type']);
											$key='organization-type-'.$ot.'-'.$edition;
											$license_file_values[$key]=$license_file_values['users-licensed'];
										}

									}
                                    if($notificationAboutExpiry == true){

                                        if(isset($license_file_values['issued-date']) && isset($license_file_values['support-expiry-date'])){

                                            $date1 = new DateTime($license_file_values['issued-date']);
                                            $date2 = new DateTime($license_file_values['support-expiry-date']);
                                            $interval = $date1->diff($date2);
                                            $license_file_values['support-assurance'.'-'.$interval->y]=1;
                                        }
                                    }

								}
								
							}else{  
								$session->addError("There has been an error in reading your license file");  
								$this->_redirect('*/*/upload');
							} 
							unlink($targetdir.'/license.txt');
							rmdir($targetdir);
							//parsing master file for mapping the data
							$filename = Mage::getBaseDir('media')."/licensemasterfile/masterlicensefile.csv";
							$tempcsvdata = array();
							$organisation=array();
							$issued_date='';
							if (($handle = fopen($filename, "r")) !== FALSE) {
								while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
									/*$num = count($data);
									$temp = array();
									for ($c=0; $c < $num; $c++) {					        
										$temp[] = $data[$c];
									}
									$tempcsvdata[]= $temp;*/

									if($data[0]!='' && $data[0]!='organization-type'&& $data[0]!='edition' && $data[0]!='users-licensed'){
										/*if($data[1]!='')
											$data[0]=$data[0].'-'.$data[1];*/
									    $csvdata[trim($data[0])]=$data[2];
									}
									else if($data[0]=='organization-type'){
										$organisation[$data[0].'-'.$data[1]]=$data[2];
									}
									else if($data[0]=='edition'){
										/*print_r($organisation);
										exit;*/
										if($organisation){
											foreach($organisation as $key=>$val){
												$csvdata[trim($key.'-'.$data[1])]=$val;
											}
										}
									}
								}
								fclose($handle);
							}
							/*$tempcsvdata_count = count($tempcsvdata);
							for($d=1; $d < $tempcsvdata_count; $d++){
								$csvdata[] = array_combine($tempcsvdata[0],$tempcsvdata[$d]);
							}*/
						
							//Mage::register('licensefile_values', $license_file_values);			
							//Mage::register('licensefile_mappingdata',$csvdata);
/*print_r($license_file_values);
print_r($csvdata);
exit;*/

//                         To collect all maintenance Products

                            $_collection = Mage::getModel('catalog/product')->getCollection();
                            $_collection->addAttributeToFilter('product_type',array('eq'=>'4'));
                            foreach ($_collection as $maintenanceProducts) {
                                $mProducts[] = $maintenanceProducts['sku'];
                        }

							Mage::getSingleton('core/session')->setLicensefileValues($license_file_values);		
							Mage::getSingleton('core/session')->setLicensefileMappingdata($csvdata);
							Mage::getSingleton('core/session')->setLicensefileCreatedBy($license_file_created_by);
							Mage::getSingleton('core/session')->setMaintenanceProductsCollection($mProducts);



						}else{
							$session->addError('Error in extracting the file.');
							$this->_redirect('*/*/upload');
						}
					}
					else{
						$session->addError('Failed to move uploaded file.');
						$this->_redirect('*/*/upload');
					}
				}
				else{
					$session->addError("Licensing file uploaded is invalid. Please upload new file");
					$this->_redirect('*/*/upload');
				}
				
			}else{
				$error=$file_errors[$_FILES['uploadlicense']['error']];
				$session->addError("Error in uploading the file -- ".$error);
				$this->_redirect('*/*/upload');
			}
		}
		$this->renderLayout();			
	}

    public function upgradeSupportAction(){

        $this->loadLayout();
        $this->renderLayout();
    }

    public function viewsubaccountordersAction(){
        $this->loadLayout();
        $orderIds = array();
        $data = $this->getRequest()->getParams();
        $id = $data['id'];
        $collection  = Mage::getResourceModel('sales/order_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id',$id);
        foreach($collection as $order) {
            $orderIds[] = $order->getid();


        }
//        Zend_Debug::dump($this->getLayout()->getUpdate()->getHandles());
//        $this->getLayout()->getBlock('subaccountOrders')->setData('data', $orderIds);
        Mage::getSingleton('customer/session')->setSubaccountorders($orderIds);

        $this->renderLayout();
    }

    public function viewsubaccountquotesAction(){
        $includePath = Mage::getBaseDir(). "/lib/Papercut/Classes";
        set_include_path(get_include_path() . PS . $includePath);
        $this->loadLayout();
        $quoteIds = array();
        $data = $this->getRequest()->getParams();
        $id = $data['id'];
        $attributes = array('quote_customer_id'=> $id);
        $collection  = Mage::getModel('quote/quote')->loadByAttributes($attributes);
        $quotes = $collection->getData();
        foreach($quotes as $quote) {
            $quoteIds[] = $quote['quote_id'];
        }
        Mage::getSingleton('customer/session')->setSubaccountquotes($quoteIds);
        $authId=1;
        $resellerLogin=1;
        $returnUrl=1;
        $key=1;
        //PaperCutResellerUrlBuilder::create($authId, $resellerLogin, $returnUrl, $key);
        $this->renderLayout();
    }

    public function testAcction(){
        print_r($_REQUEST);
    }
}
?>
