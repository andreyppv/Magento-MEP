<?php

class   Flagbit_MEP_Block_Adminhtml_Profile_View_Grid_Url_Renderer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());
        return '<a target="_blank" href="' . $value . '">' . $value . '</a>';
    }
}