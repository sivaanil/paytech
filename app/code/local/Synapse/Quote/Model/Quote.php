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

        public function sendEmail(){


            // This is the template name from your etc/config.xml
            $template_id = 'custom_template';

            // Who were sending to...
            $email_to = 'venkatalapati20097@example.com';
            $customer_name   = 'John Doe';

            // Load our template by template_id
            $email_template  = Mage::getModel('core/email_template')->loadDefault($template_id);

            // Here is where we can define custom variables to go in our email template!
            $email_template_variables = array(
                'customer_name' => $customer_name
                // Other variables for our email template.
            );

            // I'm using the Store Name as sender name here.
            $sender_name = Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_STORE_STORE_NAME);
            // I'm using the general store contact here as the sender email.
            $sender_email = Mage::getStoreConfig('trans_email/ident_general/email');
            $email_template->setSenderName($sender_name);
            $email_template->setSenderEmail($sender_email);
            $email_template->setTemplateSubject('Test');

            //Send the email!
            $email_template->send($email_to, $customer_name, $email_template_variables);

        }
	}
?>
