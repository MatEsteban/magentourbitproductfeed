<?php

/**
 * Class Urbit_ProductFeed_Adminhtml_ProductfeedbackendController
 */
class Urbit_ProductFeed_Adminhtml_ProductfeedbackendController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Redirect to system configuration page of plugin
     */
	public function indexAction()
    {
        Mage::app()->getResponse()->setRedirect(
            Mage::helper("adminhtml")->getUrl(
                "adminhtml/system_config/edit/section/productfeed_config"
            )
        );
    }
}