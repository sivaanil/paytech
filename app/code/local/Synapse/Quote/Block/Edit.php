<?php 
	class Synapse_Quote_Block_Edit extends Mage_Core_Block_Template {
		
		public function edit(){
			
			$id = $this->getRequest()->getParam('id');
			$lid = Mage::getModel("quote/quote")->load($id);
			$data = $lid->getData();
			return $data;
			
		}
	}
?>
