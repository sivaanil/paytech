<?php

class Megnor_Bestseller_Block_Bestsellerlist extends Mage_Catalog_Block_Product_Abstract
{
	 /**
     * Retrive Bestselling product
     *
     * @param   
     * @Return Mage_Catalog_Block_Product_Bestseller
     */ 	 
	 
	public function _prepareLayout() 
	{
		
		 if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
                $breadcrumbsBlock->addCrumb('home', array(
                    'label'=>Mage::helper('catalog')->__('Home'),
                    'title'=>Mage::helper('catalog')->__('Go to Home Page'),
                    'link'=>Mage::getBaseUrl()
                ));
        }    

		$storeId = Mage::app()->getStore()->getId();

		$products = Mage::getResourceModel('reports/product_collection')
			->addOrderedQty()
			->addUrlRewrite()
			->addAttributeToSelect(array('name', 'price', 'thumbnail', 'short_description','image','small_image','url_key'), 'inner')
			->addAttributeToFilter('status','1')
			->setStoreId($storeId)
			->addStoreFilter($storeId)
			->setOrder('ordered_qty', 'desc'); 
		Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
		Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);

		$productIds = array();
		foreach($products as $key => $prod) 
		{ 		
			
			$parentId = $this->getParentId($prod);
			
			if($parentId == '') continue; 
			
			if($parentId == $prod->getId()) {
				$product = $prod;
			}
			else {
				$product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($parentId);
			}
			
			/**
			 * if the product is not visible or is disabled
			 * OR,
			 * if two or more simple products of the same configurable product are ordered
			 */		
			if(!$product->isVisibleInCatalog() || in_array($product->getId(),$productIds)) continue; 
			
			$productIds[] = $product->getId();		
		}	
		
		$products = Mage::getResourceModel('catalog/product_collection')->addIdFilter($productIds);	
		
		$this->setToolbar($this->getLayout()->createBlock('catalog/product_list_toolbar', 'Toolbar'));
		$toolbar = $this->getToolbar();
	
		$toolbar->setAvailableOrders(array(
		'ordered_qty'  => $this->__('Most Purchased'),
		'name'      => $this->__('Name'),
		'price'     => $this->__('Price')
		
			))
		->setDefaultOrder('ordered_qty')
		->setDefaultDirection('desc')
		->setCollection($products);
		
		return $this;
	}


	/**
     * Retrive Bestselling product collection
     *
     * @param   
     * @Return Mage_Reports_Model_Mysql4_Product_Collection
     */ 
	protected function _getProductCollection() {
		return $this->getToolbar()->getCollection();
	}

	/**
     * Retrive Toolbar
     *
     * @param   
     * @Return String (HTML for Toolbar)
     */ 
	public function getToolbarHtml() {
		return $this->getToolbar()->_toHtml();
	}

	/**
     * Retrive Mode
     *
     * @param   
     * @Return String (grid || list)
     */ 
	public function getMode() {
		return $this->getToolbar()->getCurrentMode();
	}

	/**
     * Retrive Loaded product Collection
     *
     * @param   
     * @Return Mage_Reports_Model_Mysql4_Product_Collection
     */ 
	public function getLoadedProductCollection() {
		return $this->_getProductCollection();
	}
	
	public function getAllCategory()
	{				
		return Mage::helper('catalog/category')->getStoreCategories('name', true, false);		
	}
	
	public function getParentId($product)
	{
		$parentId = '';
				
		// if the product visibility is not set to "Nowhere"
		// i.e. if the product is visible
		if($product->getVisibility() != '1') {
			$parentId = $product->getId(); 
		}		
		else {
			// get parent id if the product is not visible
			// this means that the product is associated with a configurable product
			$parentIdArray = $product->loadParentProductIds()->getData('parent_product_ids');
			
			if(!empty($parentIdArray)) {
				$parentId = $parentIdArray[0];
			}
		}		
		return $parentId; 
	}
	
	protected function _toHtml() {
        if (!$this->helper('bestseller')->getIsActive()) {
            return '';
        }
        return parent::_toHtml();
    }
	
	public function getPageHeading() {		
		return  Mage::getStoreConfig('bestseller/standalone/heading');		
    }
	
	public function getHeading() {		
		return  Mage::getStoreConfig('bestseller/listing_home/heading');		
    } 

} 


