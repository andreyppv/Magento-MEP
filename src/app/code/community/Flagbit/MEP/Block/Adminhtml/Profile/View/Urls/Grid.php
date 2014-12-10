<?php

class Flagbit_MEP_Block_Adminhtml_Profile_View_Urls_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Class Constructor
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('urls_grid');
        $this->setUseAjax(true); // Using ajax grid is important
        $this->setDefaultSort('last_test_date');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
    }

    public function getProfileId()
    {
        return Mage::helper('mep')->getCurrentProfileData(true);
    }

    /**
     * _prepareCollection
     *
     * Prepares the collection for the grid
     *
     * @return Flagbit_MEP_Block_Adminhtml_Profile_View_Grid Self.
     */
    protected function _prepareCollection()
    {
        /* @var $collection Flagbit_MEP_Model_Mysql4_Mapping_Collection */
        $collection = Mage::getModel('mep/urls')->getCollection();
        $collection->addFieldToFilter('available', '0');
        $collection->addFieldToFilter('profile', $this->getProfileId());

        parent::setDefaultLimit('20');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _afterToHtml($html)
    {
        $html = parent::_afterToHtml($html);
        return $html;
    }


    /**
     * _prepareColumns
     *
     * Prepares the columns for the grid
     *
     * @return  Self.
     */
    protected function _prepareColumns()
    {

        $this->addColumn('url', array(
            'header' => Mage::helper('mep')->__('Url'),
            'align' => 'left',
            'index' => 'url',
            'renderer'  => 'mep/adminhtml_profile_view_grid_url_renderer',
            'sortable' => false
        ));

        $this->addColumn('apache_code', array(
            'header' => Mage::helper('mep')->__('Apache code'),
            'index' => 'apache_code'
        ));

        $this->addColumn('apache_code', array(
            'header' => Mage::helper('mep')->__('Product'),
            'index' => 'itemid',
            'renderer'  => 'mep/adminhtml_profile_view_grid_url_product_renderer',
        ));

        $this->addColumn('last_test_date', array(
            'header' => Mage::helper('mep')->__('Last test'),
            'index' => 'last_test_date'
        ));

        parent::_prepareColumns();
        return $this;
    }

    /**
     * call from ajax to get the grid
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/urls/grid', array('_current' => true));
    }
}
