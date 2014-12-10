<?php

class   Flagbit_MEP_Block_Adminhtml_Profile_View_Grid_Url_Product_Renderer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $model = Mage::getModel('catalog/product');
        $_product = $model->load($row->getData($this->getColumn()->getIndex()));
        return '<a target="_blank" rel="external" href="' . Mage::helper('adminhtml')->getUrl('adminhtml/catalog_product/edit', array('id' => $_product->getId())) . '">' . $_product->getName() . '</a>';
    }
}