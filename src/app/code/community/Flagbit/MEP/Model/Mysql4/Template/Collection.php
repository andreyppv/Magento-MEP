<?php
/**
 * User: pierre
 * Date: 04/12/14
 * Project: Magento-MEP
 */

class   Flagbit_MEP_Model_Mysql4_Template_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('mep/template');
    }

    public function toSelectArray($items = null)
    {
        if (is_null($items)) {
            $items = $this->getItems();
        }
        $select = array();
        $dateFormat = 'd.m.Y';
        foreach ($items as $item) {
            /** @var Flagbit_Mep_Model_Template $item */
            $date = Mage::getModel('core/date')->date($dateFormat, $item->getTemplateDate());
            $select[] = array(
                'value' => $item->getId(),
                'label' => 'V.' . $item->getTemplateVersion() . ' - ' . $date
            );
        }
        return $select;
    }
}