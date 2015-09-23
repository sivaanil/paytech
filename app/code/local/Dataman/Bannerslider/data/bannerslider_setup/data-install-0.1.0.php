<?php
/**
 * Banner data installation script
 *
 * @author Priyanka_Dataman
 */

/**
 *  @var $installer Mage_Core_Model_Resource_Setup
 */
$installer = $this;

/**
 * @var $model Dataman_Bannerslider_Model_Bannerslider
 */
$model = Mage::getModel('bannerslider/bannerslider');

// Set up data rows
$dataRows = array(
    array(
        'title'    => 'Slider 1',
        'filename' => 'slider1.jpg',
        'status'   => '1',
         ),

array(
        'title'    => 'Slider 2',
        'filename' => 'slider2.jpg',
        'status'   => '1',
         ),
         
array(
        'title'    => 'Slider 3',
        'filename' => 'slider3.jpg',
        'status'   => '1',
         ),
array(
        'title'    => 'Slider 4',
        'filename' => 'slider4.jpg',
        'status'   => '1',
         ),
 array(
        'title'    => 'Slider 5',
        'filename' => 'slider5.jpg',
        'status'   => '1',
         ),
 array(
        'title'    => 'Slider 6',
        'filename' => 'slider6.jpg',
        'status'   => '1',
         ),
 array(
        'title'    => 'Slider 7',
        'filename' => 'slider7.jpg',
        'status'   => '1',
         ),
 array(
        'title'    => 'Slider 8',
        'filename' => 'slider8.jpg',
        'status'   => '1',
         ),
 array(
        'title'    => 'Slider 9',
        'filename' => 'slider9.jpg',
        'status'   => '1',
         ),
 array(
        'title'    => 'Slider 10',
        'filename' => 'slider10.jpg',
        'status'   => '1',
         ),
);

// Generate news items
foreach ($dataRows as $data) {
    $model->setData($data)->setOrigData()->save();
}
