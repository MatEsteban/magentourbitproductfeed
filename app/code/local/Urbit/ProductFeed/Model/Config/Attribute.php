<?php

/**
 * Class Urbit_ProductFeed_Model_Config_Attribute
 */
class Urbit_ProductFeed_Model_Config_Attribute extends Urbit_ProductFeed_Model_Config_Abstract
{
    /**
     * Provide available product tags as a value/label array
     * @return array
     */
    public function toOptionArray()
    {
        $FieldAttribute = Mage::getModel("productfeed/feed_fields_fieldAttribute");
        $FieldCalculated = Mage::getModel("productfeed/feed_fields_fieldCalculated");
        $FieldDB = Mage::getModel("productfeed/feed_fields_fieldDB");

        return array_merge(
            array(
                array(
                    'value' => '',
                    'label' => '------ None ------',
                ),
            ),
            $FieldCalculated->getOptions(),
            $FieldDB->getOptions(),
            $FieldAttribute->getOptions(),
            array(
                array(
                    'value' => 'empty',
                    'label' => '------ None ------',
                ),
            )
        );
    }
}
