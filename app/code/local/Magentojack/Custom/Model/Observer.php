<?php
class Magentojack_Custom_Model_Observer{
	public function saveQuoteBefore($evt){
		$quote = $evt->getQuote();
		$post = Mage::app()->getFrontController()->getRequest()->getPost();
		//print_r($post);//exit;
		if(isset($post['custom']['ssn'])){
			$var = $post['custom']['ssn'];
			$quote->setSsn($var);
		}
		if(isset($post['custom']['purchaseOrder'])){
			$var = $post['custom']['purchaseOrder'];
			$quote->setPurchaseorder($var);
		}
	}
	public function saveQuoteAfter($evt){
		$quote = $evt->getQuote();
		if($quote->getSsn()){
			$var = $quote->getSsn();
			if(!empty($var)){
				$model = Mage::getModel('custom/custom_quote');
				$model->deteleByQuote($quote->getId(),'ssn');
				$model->setQuoteId($quote->getId());
				$model->setKey('ssn');
				$model->setValue($var);
				$model->save();
			}
		}
		if($quote->getPurchaseorder()){
			$var = $quote->getPurchaseorder();
			if(!empty($var)){
				$model = Mage::getModel('custom/custom_quote');
				$model->deteleByQuote($quote->getId(),'purchaseorder');
				$model->setQuoteId($quote->getId());
				$model->setKey('purchaseorder');
				$model->setValue($var);
				$model->save();
			}
		}
	}
	public function loadQuoteAfter($evt){
		$quote = $evt->getQuote();
		$model = Mage::getModel('custom/custom_quote');
		$data = $model->getByQuote($quote->getId());
		foreach($data as $key => $value){
			$quote->setData($key,$value);
		}
	}
	public function saveOrderAfter($evt){
		$order = $evt->getOrder();
		$quote = $evt->getQuote();
		if($quote->getSsn()){
			$var = $quote->getSsn();
			if(!empty($var)){
				$model = Mage::getModel('custom/custom_order');
				$model->deleteByOrder($order->getId(),'ssn');
				$model->setOrderId($order->getId());
				$model->setKey('ssn');
				$model->setValue($var);
				$order->setSsn($var);
				$model->save();
			}
		}
		if($quote->getPurchaseorder()){
			$var = $quote->getPurchaseorder();
			if(!empty($var)){
				$model = Mage::getModel('custom/custom_order');
				$model->deleteByOrder($order->getId(),'purchaseorder');
				$model->setOrderId($order->getId());
				$model->setKey('purchaseorder');
				$model->setValue($var);
				$model->save();
			}
		}
	}
	public function loadOrderAfter($evt){
		$order = $evt->getOrder();
		$model = Mage::getModel('custom/custom_order');
		$data = $model->getByOrder($order->getId());
		foreach($data as $key => $value){
			$order->setData($key,$value);
		}
	}

}