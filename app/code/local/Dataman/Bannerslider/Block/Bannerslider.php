<?php
class Dataman_Bannerslider_Block_Bannerslider extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
    
	public function getEnable()
    {	
		return Mage::getStoreConfig('tab1/general/enable');		
    }
    
     public function getBannerslider()     
     { 
        if (!$this->hasData('bannerslider')) {
            $this->setData('bannerslider', Mage::registry('bannerslider'));
        }
        return $this->getData('bannerslider');
        
    }
}
