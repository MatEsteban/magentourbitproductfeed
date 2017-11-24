<?php

/**
 * Class Urbit_ProductFeed_IndexController
 */
class Urbit_ProductFeed_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * @var Urbit_ProductFeed_Model_List_Product
     */
    protected $_products;

    /**
     * Index action for plugin frontend
     * Show product feed in json format
     */
    public function IndexAction()
    {
	    /** @var Urbit_ProductFeed_Model_Config $config */
        $config = Mage::getModel("productfeed/config");

        /**
         * @var Urbit_ProductFeed_Model_List_Product
         */
        $this->_products = Mage::getModel("productfeed/list_product", $config->filter);
        
        $this->getResponse()
            ->clearHeaders()
            ->setHeader('Content-Type', 'application/json', true)
            ->setBody($this->getProductsJson());
        ;
    }

    /**
     * Return json feed data
     * @return string
     */
    public function getProductsJson()
    {
        /** @var Urbit_ProductFeed_Helper_Feed $feedHelper */
        $feedHelper = Mage::helper("productfeed/feed");

        if (!$feedHelper->checkCache()) {
            $feedHelper->generateFeed($this->_products);
        }

        return $feedHelper->getDataJson();
    }
}
