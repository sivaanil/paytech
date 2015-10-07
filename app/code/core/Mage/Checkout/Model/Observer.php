<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Checkout observer model
 *
 * @category   Mage
 * @package    Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Checkout_Model_Observer
{
    public function unsetAll()
    {
        Mage::getSingleton('checkout/session')->unsetAll();
    }

    public function loadCustomerQuote()
    {
		
        try {
            Mage::getSingleton('checkout/session')->loadCustomerQuote();
        }
        catch (Mage_Core_Exception $e) {
            Mage::getSingleton('checkout/session')->addError($e->getMessage());
        }
        catch (Exception $e) {
            Mage::getSingleton('checkout/session')->addException(
                $e,
                Mage::helper('checkout')->__('Load customer quote error')
            );
        }
    }

    public function salesQuoteSaveAfter($observer)
    {
        $quote = $observer->getEvent()->getQuote();
        /* @var $quote Mage_Sales_Model_Quote */
        if ($quote->getIsCheckoutCart()) {
            Mage::getSingleton('checkout/session')->getQuoteId($quote->getId());
        }
    }

    public function sendOrderCreatedEmail(){
        $orderId  = Mage::getSingleton('checkout/session')->getLastOrderId();
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            if($customer['order_confirmation_email']){
                $allEmails = $customer['order_confirmation_email'];
                $email_to=explode(",",$allEmails);
            }
        }
    $order = Mage::getModel('sales/order')->load($orderId);
        $billingAddress = $order->getBillingAddress();
        $payment = $order->getPayment()->getMethodInstance()->getTitle();
//        $shippingAddress = $order->getShippingAddress();

        // This is the template name from your etc/config.xml
        $template_id = 'send_order_created_mail_to_admin';
        // Who were sending to...
        $customer_name   = $customer->getName();

        // Load our template by template_id
        //For templates which are used in database

        $email_template  = Mage::getModel('core/email_template')->loadByCode('New_Order_Notification_To_Admin'); //where 'custom_template' is the name of template

        //If you define in config.xml then please use below code.
//        $email_template  = Mage::getModel('core/email_template')->loadDefault($template_id);

        // Here is where we can define custom variables to go in our email template!
        $email_template_variables = array(
            'order'        => $order,
            'billing'      => $billingAddress,
            'payment' => $payment

            // Other variables for our email template.
        );

        // I'm using the Store Name as sender name here.
        $sender_name = Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_STORE_STORE_NAME);
        // I'm using the general store contact here as the sender email.
        $sender_email = Mage::getStoreConfig('trans_email/ident_general/email');
        $email_template->setSenderName($sender_name);
        $email_template->setSenderEmail($sender_email);
        $email_template->setTemplateSubject('Order created by '.$customer_name);

        //Send the email to the sub account
        $email_template->send($email_to, $customer_name, $email_template_variables);
    }
}
