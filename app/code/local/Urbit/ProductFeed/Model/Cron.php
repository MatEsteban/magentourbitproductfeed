<?php

/**
 * Class Urbit_ProductFeed_Model_Cron
 */
class Urbit_ProductFeed_Model_Cron extends Mage_Core_Helper_Abstract
{
	/**
	 * Generate feed file to cache
	 */
	public function generateFeed()
    {
        /** @var Urbit_ProductFeed_Model_Config $config */
        $config = Mage::getModel("productfeed/config");

        $products = Mage::getModel(
            "productfeed/list_product",
            $config->filter
        );

        Mage::helper("productfeed/feed")->generateFeed($products);
	}
}