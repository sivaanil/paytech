<?php

class Dataman_Bannerslider_Block_Adminhtml_Bannerslider_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('bannerslider_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('bannerslider')->__('Banner Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('bannerslider')->__('Banner Information'),
          'title'     => Mage::helper('bannerslider')->__('Banner Information'),
          'content'   => $this->getLayout()->createBlock('bannerslider/adminhtml_bannerslider_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}
