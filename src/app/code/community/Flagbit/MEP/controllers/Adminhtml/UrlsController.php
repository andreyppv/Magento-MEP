<?php
class Flagbit_MEP_Adminhtml_UrlsController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Render grid action
     */
    public function indexAction()
    {
        $this->_forward('grid');
    }

    /**
     * Grid for AJAX request
     */
    public function gridAction()
    {
        $this->_initLayoutMessages('adminhtml/session');
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('mep/adminhtml_profile_view_urls_grid')->toHtml()
        );
    }
}
