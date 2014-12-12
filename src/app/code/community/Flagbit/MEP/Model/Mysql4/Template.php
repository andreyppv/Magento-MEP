<?php
/**
 * User: pierre
 * Date: 04/12/14
 * Project: Magento-MEP
 */

class   Flagbit_MEP_Model_Mysql4_Template extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('mep/template', 'id');
    }
}