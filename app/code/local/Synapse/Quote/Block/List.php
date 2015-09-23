<?php 
	
	class Synapse_Quote_Block_List extends Mage_Core_Block_Template {
		
		public function displayList(){
			
			$collection = Mage::getModel("quote/quote")->getCollection();
			return $collection;
		}
	}
?>
