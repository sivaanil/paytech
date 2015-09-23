<?php
class Adminmodules_Brandattr_Model_Product_Attribute_Source_Unit extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
	public function getAllOptions()
	{

		$collection = Mage::getModel('customer/customer')
				->getCollection()
				->addAttributeToSelect('*')
				->addFieldToFilter('is_dealer', 1);
		
		if ($this->_options === null) {
			$this->_options = array();
			$this->_options[] = array('value'=>'','label'=>'Select Dealer');
			foreach($collection as $_customer){
				$temp = array();
				$temp['value'] = $_customer->getId();
				$temp['label'] = $_customer->getFirstname().' '.$_customer->getLastname();
				$this->_options[] = $temp;
			}
		}
		return $this->_options;
	}
}

?>			
