<?php

/**
 * Class Urbit_ProductFeed_Model_Config_Abstract
 */
abstract class Urbit_ProductFeed_Model_Config_Abstract
{
    /**
     * Provide available options as a value/label array
     * @return array
     */
    abstract public function toOptionArray();
}
