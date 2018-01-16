<?php

/**
 * Class Urbit_ProductFeed_Model_Feed_Fields_Factory
 */
class Urbit_ProductFeed_Model_Feed_Fields_Factory
{
    public function processAttribute($product, $configValue)
    {

        $cls = static::getFieldClassByFieldName($configValue);

        return $cls::processAttribute($product, $configValue);
    }

    /**
     * @param $name
     * @return bool|mixed
     */
    public static function getFieldClassByFieldName($name)
    {
        foreach (array(
            'Urbit_ProductFeed_Model_Feed_Fields_FieldCalculated',
            'Urbit_ProductFeed_Model_Feed_Fields_FieldAttribute',
            'Urbit_ProductFeed_Model_Feed_Fields_FieldDB',
        ) as $cls) {
            $prefix = $cls::getPrefix();

            if (preg_match("/^{$prefix}/", $name)) {
                return $cls;
            }
        }

        return false;
    }
}
