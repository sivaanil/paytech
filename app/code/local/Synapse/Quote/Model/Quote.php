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
	}
?>
