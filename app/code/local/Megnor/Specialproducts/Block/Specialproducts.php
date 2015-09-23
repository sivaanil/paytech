<?php class Megnor_Specialproducts_Block_Specialproducts extends Mage_Core_Block_Template { 

	protected $_productsLimit = null;
	const DEFAULT_PRODUCTS_LIMIT = 5;
	
	public function _prepareLayout()
    {
    }

    public function getSpecialProductCollection()
    {    	
        $storeId    = Mage::app()->getStore()->getId();       
		
		$todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        $tomorrow = mktime(0, 0, 0, date('m'), date('d')+1, date('y'));
        $dateTomorrow = date('m/d/y', $tomorrow);
		
		$collection = Mage::getResourceModel('catalog/product_collection');
        $collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());
		
		if(Mage::registry('current_category') && Mage::getStoreConfig('specialproducts/general/categorywised')):
		
			$products = Mage::getResourceModel('catalog/product_collection')->addUrlRewrite()->addAttributeToSelect(array('name', 'price', 'thumbnail', 'short_description','image','small_image','url_key'), 'inner')->addAttributeToFilter('status','1')->addCategoryFilter(Mage::registry('current_category'))->setStoreId($storeId)->addStoreFilter($storeId)->addAttributeToFilter('special_from_date', array('date' => true, 'to' => $todayDate))->addAttributeToFilter('special_to_date', array('or'=> array(0 => array('date' => true, 'from' => $dateTomorrow), 1 => array('is' => new Zend_Db_Expr('null')))), 'left')->addAttributeToSort('special_from_date', 'desc')->setPageSize($this->getProductsLimit());
		else:			
			$products = Mage::getResourceModel('catalog/product_collection')->addUrlRewrite()->addAttributeToSelect(array('name', 'price', 'thumbnail', 'short_description','image','small_image','url_key'), 'inner')->addAttributeToFilter('status','1')->setStoreId($storeId)->addStoreFilter($storeId)->addAttributeToFilter('special_from_date', array('date' => true, 'to' => $todayDate))->addAttributeToFilter('special_to_date', array('or'=> array(0 => array('date' => true, 'from' => $dateTomorrow), 1 => array('is' => new Zend_Db_Expr('null')))), 'left')->addAttributeToSort('special_from_date', 'desc')->addAttributeToSort('news_from_date', 'desc')->setPageSize($this->getProductsLimit());			
		endif;
		Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);		
		return $products;
    }
		
	protected function _toHtml(){
        if (!(bool) Mage::getStoreConfig('specialproducts/general/active')) {
            return '';
        }
        return parent::_toHtml();
    }
	
	public function setProductsLimit($count){
        $this->_productsCount = $count;
        return $this;
    }

    public function getProductsLimit(){		
		$count = Mage::getStoreConfig('specialproducts/sidebar/number_of_items');
				
		if($count) 		
			return $count;
			
        if (null === $this->_productsLimit){
            $this->_productsCount = self::DEFAULT_PRODUCTS_LIMIT;
        }
        return $this->_productsLimit;
    }
	
	public function getSidebarHeading(){
		return Mage::getStoreConfig('specialproducts/sidebar/heading');
    }
	
}  