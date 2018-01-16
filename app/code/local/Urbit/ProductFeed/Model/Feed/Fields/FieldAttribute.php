<?php

/**
 * Class Urbit_ProductFeed_Model_Feed_Fields_FieldAttribute
 */
class Urbit_ProductFeed_Model_Feed_Fields_FieldAttribute
{
    /**
     * @param Urbit_ProductFeed_Model_Feed_Product $feedProduct
     * @param string $name
     * @return string
     */
    public static function processAttribute(Urbit_ProductFeed_Model_Feed_Product $feedProduct, $name)
    {
        $product = $feedProduct->getProduct();
        $name = static::getNameWithoutPrefix($name);

        return static::getAttributeValue($name, $product);
    }

    /**
     * Returns product's attribute value for current attributes's name
     * @param string $name
     * @param $product
     * @return null
     */
    protected static function getAttributeValue($name, $product)
    {
        $attr = $product->getResource()->getAttribute($name);

        if (!$attr) {
            return null;
        }

        return $attr->getFrontend()->getValue($product);
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        /** @var Mage_Catalog_Model_Resource_Product_Attribute_Collection $collection */
        $collection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addFieldToSelect("*")
            ->setOrder('frontend_label', Varien_Data_Collection::SORT_ORDER_ASC)
        ;

        $list = $collection
            ->load()
            ->toArray()
        ;

        $attributes = array(
            array(
                'value' => 'none',
                'label' => '------ Attributes ------',
            ),
        );

        foreach ($list['items'] as $attr) {
            if (!isset($attr['is_user_defined']) || !isset($attr['attribute_code']) || !isset($attr['frontend_label'])) {
                continue;
            }

            $hasLabel = strlen(trim($attr['frontend_label']));

            if ($hasLabel) {
                $attributes[] = array(
                    'value' => static::getPrefix() . $attr['attribute_code'],
                    'label' => $attr['frontend_label'],
                );
            }
        }

        return $attributes;
    }

    /**
     * @param string $name
     * @return string mixed
     */
    public static function getNameWithoutPrefix($name)
    {
        return str_replace(static::getPrefix(), '', $name);
    }

    /**
     * Returns prefix for attribute options in selectboxes
     * @return string
     */
    public static function getPrefix()
    {
        return 'attr_';
    }
}
