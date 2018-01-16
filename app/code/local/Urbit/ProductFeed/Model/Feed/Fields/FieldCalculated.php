<?php

/**
 * Class Urbit_ProductFeed_Model_Feed_Fields_FieldCalculated
 */
class Urbit_ProductFeed_Model_Feed_Fields_FieldCalculated
{
    const FUNCTION_PREFIX = 'getProduct';

    /**
     * @param Urbit_ProductFeed_Model_Feed_Product $feedProduct
     * @param string $name
     * @return string
     */
    public static function processAttribute(Urbit_ProductFeed_Model_Feed_Product $feedProduct, $name)
    {
        $static = new static();
        $funcName = static::FUNCTION_PREFIX . static::getNameWithoutPrefix($name);

        return $static->{$funcName}($feedProduct);
    }

    /**
     * Returns calculated options for admin config page's selectboxes
     * @return array
     */
    public function getOptions()
    {
        $options = array();

        $options[] = array(
            'value' => 'none',
            'label' => '------ Calculated ------',
        );

        $cls = get_class($this);
        $reflection = new ReflectionClass($cls);

        $methods = $reflection->getMethods();

        foreach ($methods as $method) {
            if (strpos($method->getName(), static::FUNCTION_PREFIX) !== false) {
                $name = str_replace(static::FUNCTION_PREFIX, '', $method->getName());

                if (!empty($name)) {
                    $options[] = array(
                        'value' => static::getPrefix() . $name,
                        'label' => $name,
                    );
                }
            }
        }

        return $options;
    }

    /**
     * @param Urbit_ProductFeed_Model_Feed_Product $feedProduct
     * @return string
     */
    protected function getProductId(Urbit_ProductFeed_Model_Feed_Product $feedProduct)
    {
        $product = $feedProduct->getProduct();
        $sku = $product->getSku();

        return ($sku) ? $sku : (string)$product->getId();
    }

    /**
     * @param Urbit_ProductFeed_Model_Feed_Product $feedProduct
     * @return string
     */
    protected function getProductName(Urbit_ProductFeed_Model_Feed_Product $feedProduct)
    {
        $product = $feedProduct->getProduct();

        return $product->getName();
    }

    /**
     * @param Urbit_ProductFeed_Model_Feed_Product $feedProduct
     * @return string
     */
    protected function getProductDescription(Urbit_ProductFeed_Model_Feed_Product $feedProduct)
    {
        $product = $feedProduct->getProduct();

        return $product->getDescription();
    }

    /**
     * @param Urbit_ProductFeed_Model_Feed_Product $feedProduct
     * @return string
     */
    protected function getProductLink(Urbit_ProductFeed_Model_Feed_Product $feedProduct)
    {
        $product = $feedProduct->getProduct();

        return $product->getProductUrl();
    }

    /**
     * @param Urbit_ProductFeed_Model_Feed_Product $feedProduct
     * @return float
     */
    protected function getProductWeight(Urbit_ProductFeed_Model_Feed_Product $feedProduct)
    {
        $product = $feedProduct->getProduct();

        return (float)$product->getWeight();
    }

    /**
     * @param string $name
     * @return string
     */
    public static function getNameWithoutPrefix($name)
    {
        return str_replace(static::getPrefix(), '', $name);
    }

    /**
     * Returns prefix for calculated options in selectboxes
     * @return string
     */
    public static function getPrefix()
    {
        return 'calc_';
    }
}
