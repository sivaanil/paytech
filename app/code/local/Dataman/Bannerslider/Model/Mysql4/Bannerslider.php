<?php

class Dataman_Bannerslider_Model_Mysql4_Bannerslider extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the bannerslider_id refers to the key field in your database table.
        $this->_init('bannerslider/bannerslider', 'bannerslider_id');
    }
}