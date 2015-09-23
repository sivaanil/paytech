<?php
class Megnor_Bestseller_Helper_Data extends Mage_Core_Helper_Abstract
{ 
	const PATH_PAGE_HEADING = 'bestseller/standalone/heading';
    const PATH_CMS_HEADING = 'bestseller/cmspage/heading_block';
    const DEFAULT_LABEL = 'Popular Products';
		
    public function getCmsBlockLabel() {
        $configValue = Mage::getStoreConfig(self::PATH_CMS_HEADING);
        return strlen($configValue) > 0 ? $configValue : self::DEFAULT_LABEL;
    }

    public function getPageLabel() {
        $configValue = Mage::getStoreConfig(self::PATH_PAGE_HEADING);
        return strlen($configValue) > 0 ? $configValue : self::DEFAULT_LABEL;
    }

    public function getIsActive() {
		return (bool) Mage::getStoreConfig('bestseller/general/active');
    }

	
}
