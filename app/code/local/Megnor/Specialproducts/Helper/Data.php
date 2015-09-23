<?php class Megnor_Specialproducts_Helper_Data extends Mage_Core_Helper_Abstract
{ 
	const PATH_SIDEBAR_HEADING = 'specialproducts/sidebar/heading';
    const PATH_PAGE_HEADING = 'specialproducts/standalone/heading';
    const PATH_CMS_HEADING = 'specialproducts/cmspage/heading_block';
    const DEFAULT_LABEL = 'Special Products';
	
	public function getSidebarLabel() {
        $configValue = Mage::getStoreConfig(self::PATH_PAGE_HEADING);
        return strlen($configValue) > 0 ? $configValue : self::DEFAULT_LABEL;
    }

    public function getCmsBlockLabel() {
        $configValue = Mage::getStoreConfig(self::PATH_CMS_HEADING);
        return strlen($configValue) > 0 ? $configValue : self::DEFAULT_LABEL;
    }

    public function getPageLabel() {
        $configValue = Mage::getStoreConfig(self::PATH_PAGE_HEADING);
        return strlen($configValue) > 0 ? $configValue : self::DEFAULT_LABEL;
    }

    public function getIsActive() {
        return (bool) Mage::getStoreConfig('specialproducts/general/active');
    }
}
