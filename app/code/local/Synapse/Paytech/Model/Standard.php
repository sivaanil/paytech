<?php
 
class Synapse_Paytech_Model_Standard extends Mage_Paypal_Model_Standard
{
 
    public function getStandardCheckoutFormFields()
    {
        $orderIncrementId = $this->getCheckout()->getLastRealOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        $api = Mage::getModel('paypal/api_standard')->setConfigObject($this->getConfig());
        $api->setOrderId($orderIncrementId)
            ->setCurrencyCode($order->getOrderCurrencyCode())
            //->setPaymentAction()
            ->setOrder($order)
            ->setNotifyUrl(Mage::getUrl('paypal/ipn/'))
            ->setReturnUrl(Mage::getUrl('paypal/standard/success'))
            ->setCancelUrl(Mage::getUrl('paypal/standard/cancel'));
 
        // export address
        $isOrderVirtual = $order->getIsVirtual();
        $address = $isOrderVirtual ? $order->getBillingAddress() : $order->getShippingAddress();
        if ($isOrderVirtual) {
            $api->setNoShipping(true);
        } elseif ($address->validate()) {
            $api->setAddress($address);
        }
 
        // add cart totals and line items
        $api->setPaypalCart(Mage::getModel('paypal/cart', array($order)))
            ->setIsLineItemsEnabled($this->_config->lineItemsEnabled)
        ;
        if (!$this->_config->lineItemsEnabled) {
            $api->setCartSummary($this->_getAggregatedCartSummary());
        }
		
		$api->setLocale($api->getLocaleCode());
        $result = $api->getStandardCheckoutRequest();
		$request['address_override'] = 0;
        $result['amount'] = round($order->getGrandTotal(), 2);
 
		$j = 0;
		$items = $order->getAllItems();
		//echo '<pre>';
		
 
		foreach ($items as $itemId => $item)
		{
			$j ++;
			$result['amount_'.$j] = round($item->getPrice(), 2);
			//print_r($item);
			//echo 
		}
		$j ++;
		if($result['item_name_'.$j]=='Shipping')
		{
			$result['amount_'.$j] = round($order->getShippingAmount(), 2);
			$result['shipping'] = round($order->getShippingAmount(), 2);
		}

		$result['country'] = $order->getBillingAddress()->getCountryId();
		
				/*echo "<pre>";
		print_r($result);
		exit;*/
        return $result;
    }

 
}