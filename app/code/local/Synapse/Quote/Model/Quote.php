<?php 
	class Synapse_Quote_Model_Quote extends Mage_Core_Model_Abstract {
		
		protected function _construct(){
			
			$this->_init("quote/quote");
		}

        public function loadByAttributes($attributes)
        {
            $this->setData($this->getResource()->loadByAttributes($attributes));
            return $this;
        }

        public function sendQuoteCreatedEmail($customer){
//            echo '<pre>';
//            print_r($customer);
//            exit;
            if($customer->order_confirmation_email){
                $allemails = $customer->order_confirmation_email;
                $email_to=explode(",",$allemails);
            }
            $email_to[] = $customer->email;
//print_r($email_to); exit;
            // This is the template name from your etc/config.xml
            $template_id = 'custom_template';
            // Who were sending to...
           $customer_name   = $customer->getName();

        // Load our template by template_id
            //For templates which are useed in database

                //$email_template  = Mage::getModel('core/email_template')->loadByCode('custom_template'); //where 'custom_template' is the name of template

            //If you define in config.xml then please use below code.
            $email_template  = Mage::getModel('core/email_template')->loadDefault($template_id);

            // Here is where we can define custom variables to go in our email template!
            $email_template_variables = array(
                'customer_name' => $customer->getName()
                // Other variables for our email template.
            );

            // I'm using the Store Name as sender name here.
            $sender_name = Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_STORE_STORE_NAME);
            // I'm using the general store contact here as the sender email.
            $sender_email = Mage::getStoreConfig('trans_email/ident_general/email');
            $email_template->setSenderName($sender_name);
            $email_template->setSenderEmail($sender_email);
            $email_template->setTemplateSubject('Quote created by '.$customer->getName());

            //Send the email to the sub account
            $email_template->send($email_to, $customer_name, $email_template_variables);
            //Send the email to the sub account master customer
            if($customer->master_customer){
                $masterCustomer = Mage::getModel('customer/customer')->load($customer->master_customer);
                $email_to = $masterCustomer->getData('email');
                $email_template->send($email_to, $customer_name, $email_template_variables);
            }

        }
	}
?>
