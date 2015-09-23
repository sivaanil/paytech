<?php

class Dataman_Bannerslider_Block_Adminhtml_Bannerslider_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('bannerslider_form', array('legend'=>Mage::helper('bannerslider')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('bannerslider')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('bannerslider')->__('Banner Image'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('bannerslider')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('bannerslider')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('bannerslider')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('bannerslider')->__('Alt Text'),
          'title'     => Mage::helper('bannerslider')->__('Alt Text'),
          'wysiwyg'   => false,
          'required'  => false,
      ));
      
      $fieldset->addField('titlestatus', 'select', array(
          'label'     => Mage::helper('bannerslider')->__('Display title on banner'),
          'name'      => 'titlestatus',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('bannerslider')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('bannerslider')->__('Disabled'),
              ),
          ),
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getBannersliderData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getBannersliderData());
          Mage::getSingleton('adminhtml/session')->setBannersliderData(null);
      } elseif ( Mage::registry('bannerslider_data') ) {
          $form->setValues(Mage::registry('bannerslider_data')->getData());
      }
      return parent::_prepareForm();
  }
}
