<?php

/**
 * Class Urbit_ProductFeed_Model_Feed
 */
class Urbit_ProductFeed_Model_Feed
{
    const SCHEDULE_INTERVAL_HOURLY  = 'HOURLY';
    const SCHEDULE_INTERVAL_DAILY   = 'DAILY';
    const SCHEDULE_INTERVAL_WEEKLY  = 'WEEKLY';
    const SCHEDULE_INTERVAL_MONTHLY = 'MONTHLY';

    const SCHEDULE_INTERVAL_HOURLY_TIME  = 1;
    const SCHEDULE_INTERVAL_DAILY_TIME   = 24;
    const SCHEDULE_INTERVAL_WEEKLY_TIME  = 168;
    const SCHEDULE_INTERVAL_MONTHLY_TIME = 5040;

    const FEED_VERSION = '2017-06-28-1';

    /**
     * Valid products for using in feed
     * @var array
     */
    protected $data = array();

    /**
     * Products collection to process
     * @var Urbit_ProductFeed_Model_List_Product
     */
    protected $collection = array();

    /**
     * Urbit_ProductFeed_Model_Feed constructor.
     * @param Urbit_ProductFeed_Model_List_Product $collection
     */
    public function __construct(Urbit_ProductFeed_Model_List_Product $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Process products to use in feed
     */
    public function process()
    {
        /** @noinspection PhpLanguageLevelInspection */
        $products = array();

        foreach ($this->collection as $product) {
            $feedProduct = Mage::getModel("productfeed/feed_product", $product);

            if ($feedProduct->process()) {
                $products[] = $feedProduct->toArray();
            }
        }

        $this->data = $products;
    }

    public function toArray()
    {
        if (empty($this->data)) {
            $this->process();
        }

        $lang = Mage::app()->getLocale()->getLocaleCode();

        $version = $this->getFeedVersion();

        return array(
            '$schema' => "https://raw.githubusercontent.com/urbitassociates/urbit-merchant-feeds/master/schemas/products/{$version}/products.json",
            'content_language' => $lang,
            'attribute_language' => $lang,
            'content_type' => 'products',
            'target_country' => array(
                $lang,
            ),
            'version' => $version,
            'feed_format' => array(
                "encoding" => "UTF-8",
            ),
            'schedule' => array(
                'interval' => $this->getIntervalText(),
            ),
            'entities' => $this->data,
        );
    }

    /**
     * Get schedule interval value
     * @return string
     */
    public function getIntervalText()
    {
        /** @var Urbit_ProductFeed_Model_Config $config */
        $config = Mage::getModel("productfeed/config");

        foreach (array(
            self::SCHEDULE_INTERVAL_HOURLY_TIME  => self::SCHEDULE_INTERVAL_HOURLY,
            self::SCHEDULE_INTERVAL_DAILY_TIME   => self::SCHEDULE_INTERVAL_DAILY,
            self::SCHEDULE_INTERVAL_WEEKLY_TIME  => self::SCHEDULE_INTERVAL_WEEKLY,
            self::SCHEDULE_INTERVAL_MONTHLY_TIME => self::SCHEDULE_INTERVAL_MONTHLY,
        ) as $time => $val) {
            if ($config->cron['cache_duration'] <= $time) {
                return $val;
            }
        }

        return self::SCHEDULE_INTERVAL_HOURLY;
    }

    public function getFeedVersion()
    {
        return self::FEED_VERSION;
    }
}
