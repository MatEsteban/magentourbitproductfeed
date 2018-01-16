<?php

/**
 * Class Urbit_ProductFeed_Model_Feed_Fields_FieldDB
 */
class Urbit_ProductFeed_Model_Feed_Fields_FieldDB
{
    /**
     * @param Urbit_ProductFeed_Model_Feed_Product $feedProduct
     * @param string $name
     * @return string
     */
    public static function processAttribute(Urbit_ProductFeed_Model_Feed_Product $feedProduct, $name)
    {
        $funcName = static::getNameWithoutPrefix('get' . $name);

        return $feedProduct->getProduct()->$funcName();
    }

    /**
     * Returns DB options for admin config page's selectboxes
     * @return array
     */
    public function getOptions()
    {
        $options = array();

        //these getters methods will not be included in the admin config page's selectboxes
        $unnecessaryFields = array(
            'ResourceCollection',
            'TypeInstance',
            'LinkInstance',
            'IdBySku',
            'Category',
            'CategoryIds',
            'CategoryCollection',
            'WebsiteIds',
            'StoreIds',
            'Attributes',
            '_Resource',
            'PriceModel',
            'FormatedTierPrice',
            'RelatedProducts',
            'RelatedProductIds',
            'RelatedProductCollection',
            'RelatedLinkCollection',
            'UpSellProducts',
            'UpSellProductIds',
            'UpSellProductCollection',
            'UpSellLinkCollection',
            'CrossSellProducts',
            'CrossSellProductIds',
            'CrossSellProductCollection',
            'CrossSellLinkCollection',
            'GroupedLinkCollection',
            'MediaAttributes',
            'MediaGalleryImages',
            'MediaConfig',
            'VisibleInCatalogStatuses',
            'VisibleStatuses',
            'VisibleInSiteVisibilities',
            'AttributeText',
            'CustomDesignDate',
            'OptionInstance',
            'ProductOptionsCollection',
            'OptionById',
            'Options',
            'CustomOptions',
            'CustomOption',
            'AvailableInCategories',
            '_ImageHelper',
            'ReservedAttributes',
            'CacheIdTags',
            'PreconfiguredValues',
            'ProductEntitiesInfo',
            '_EventData',
            '_Data',
            '__',
            'Data',
            'CacheIdTags',
            'Resource',
            'Collection',
            'DataSetDefault',
            'DataUsingMethod',
            'OrigData',
            'RatingSummary',
            'CacheTags',
            'UrlModel',
            'SpecialFromDate',
            'SpecialToDate',
            'LockedAttributes',
            'Store',
            'WebsiteStoreIds'
        );

        $options[] = array(
            'value'   => 'none',
            'label' => '------ Db Fields ------',
        );

        $reflection = new ReflectionClass('Mage_Catalog_Model_Product');

        //get all methods of Mage_Catalog_Model_Product class
        $methods = $reflection->getMethods();

        //choose getters methods, which not contained in unnecessaryFields array
        foreach ($methods as $method) {
            if (strpos($method->getName(), 'get') !== false) {
                $name = str_replace('get', '', $method->getName());

                if (!empty($name) && !in_array($name, $unnecessaryFields)) {
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
     * @param $name
     * @return mixed
     */
    public static function getNameWithoutPrefix($name)
    {
        return str_replace(static::getPrefix(), '', $name);
    }

    /**
     * Returns prefix for database options in selectboxes
     * @return string
     */
    public static function getPrefix()
    {
        return 'db_';
    }
}
