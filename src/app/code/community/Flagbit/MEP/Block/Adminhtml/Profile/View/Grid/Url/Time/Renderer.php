<?php

class   Flagbit_MEP_Block_Adminhtml_Profile_View_Grid_Url_Time_Renderer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        if($row->getData('executed') != '') return $row->getData('executed') . ' (in ' . $row->getData('time') . ' sec)';
        return '';
    }
}