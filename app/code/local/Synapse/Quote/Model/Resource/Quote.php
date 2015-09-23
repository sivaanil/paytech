<?php 
	class Synapse_Quote_Model_Resource_Quote extends Mage_Core_Model_Resource_Db_Abstract {
		
		protected function _construct(){
			
			$this->_init("quote/quote","quote_id");
		}

        public function loadByAttributes($attributes)
        {
            $adapter = $this->_getReadAdapter();
            $where   = array();
            foreach ($attributes as $attributeCode=> $value) {
                $where[] = sprintf('%s=:%s', $attributeCode, $attributeCode);
            }
            $select = $adapter->select()
                ->from($this->getMainTable())
                ->where(implode(' AND ', $where));

            $binds = $attributes;

            return $adapter->fetchAll($select, $binds);
        }
	}
?>
