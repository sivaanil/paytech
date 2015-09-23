<?php class Megnor_Specialproducts_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
	
        if (!Mage::helper('specialproducts')->getIsActive()) {
            $this->_forward('noRoute');
            return;
        }

        $template = Mage::getConfig()->getNode('global/page/layouts/' . Mage::getStoreConfig("specialproducts/standalone/layout") . '/template');

        $this->loadLayout();
        $this->getLayout()->getBlock('root')->setTemplate($template);
        $this->getLayout()->getBlock('head')->setTitle($this->__(Mage::getStoreConfig("specialproducts/standalone/meta_title")));
        $this->getLayout()->getBlock('head')->setDescription($this->__(Mage::getStoreConfig("specialproducts/standalone/meta_description")));
        $this->getLayout()->getBlock('head')->setKeywords($this->__(Mage::getStoreConfig("specialproducts/standalone/meta_keywords")));

        $breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');
        $breadcrumbsBlock->addCrumb('special_products', array(
            'label' => Mage::helper('specialproducts')->__(Mage::helper('specialproducts')->getPageLabel()),
            'title' => Mage::helper('specialproducts')->__(Mage::helper('specialproducts')->getPageLabel()),
        ));
        $this->renderLayout();
    }

}