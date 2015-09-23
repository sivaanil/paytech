<?php
/*Magento allows user to view details of the orders placed by himself only*/
require_once Mage::getModuleDir('controllers', 'Mage_Sales').DS.'OrderController.php';
//echo "I am loading";exit;
class Synapse_Paytech_Frontend_Sales_OrderController extends Mage_Sales_OrderController{
	
	protected function _canViewOrder($order)
    {
        $availableStates = Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates();
        if ($order->getId()&& in_array($order->getState(),$availableStates, $strict = true)) {
            return true;
        }
        return false;
    }
}
?>