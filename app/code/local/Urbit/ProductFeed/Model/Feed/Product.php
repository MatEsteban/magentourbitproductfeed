<?php

/**
 * Class Urbit_ProductFeed_Model_Feed_Product
 *
 * Special properties:
 * @property $isSimple
 * @property $currencyCode
 *
 * Field properties (for feed $data property):
 * @property string $id
 * @property string $name
 * @property string $description
 * @property string $link
 * @property array $prices
 * @property array $brands
 * @property array $attributes
 * @property string $gtin
 * @property array $categories
 * @property string $image_link
 * @property array $additional_image_links
 * @property string $item_group_id
 * @property array $dimensions
 *
 * @property string $sizeType
 * @property string $size
 * @property string $color
 * @property string $gender
 * @property string $material
 * @property string $pattern
 * @property string $age_group
 * @property string $condition
 */
class Urbit_ProductFeed_Model_Feed_Product
{
    /**
     * Array with product fields
     * @var array
     */
    protected $data = array();

    /**
     * Magento product object
     * @var Mage_Catalog_Model_Product
     */
    protected $product;

    /**
     * Magento product resource object
     * @var Mage_Catalog_Model_Resource_Product
     */
    protected $resource;

    /**
     * Urbit_ProductFeed_Model_Feed_Product constructor.
     * @param Mage_Catalog_Model_Product $product
     */
    public function __construct(Mage_Catalog_Model_Product $product)
    {
        $this->product = $product->load($product->getId());
        $this->resource = $product->getResource();
    }

    /**
     * Get feed product data
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * Get feed product data fields
     * @param string $name
     * @return mixed|null
     */
    public function __get($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }

        if (stripos($name, 'is') === 0 && method_exists($this, $name)) {
            return $this->{$name}();
        }

        $getMethod = "get{$name}";

        if (method_exists($this, $getMethod)) {
            return $this->{$getMethod}();
        }

        return null;
    }

    /**
     * Set feed product data fields
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {

        $setMethod = "set{$name}";

        if (method_exists($this, $setMethod)) {
            $this->{$setMethod}($value);

            return;
        }

        $this->data[$name] = $value;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return $this|mixed|null
     * @throws Exception
     */
    public function __call($name, $arguments)
    {
        $property = strtolower(preg_replace("/^unset/", '', $name));
        $propertyExist = isset($this->data[$property]);

        if ($propertyExist) {
            if (stripos($name, 'unset') === 0) {
                unset($this->data[$property]);

                return $this;
            }

            if (stripos($name, 'get') === 0) {
                return $this->{$property};
            }

            if (stripos($name, 'set') === 0 && isset($arguments[0])) {
                $this->{$property} = $arguments[0];

                return $this;
            }
        }

        throw new Exception("Unknown method {$name}");
    }

    /**
     * Process Magento product and get data for feed
     * @return bool
     */
    public function process()
    {
        $product = $this->product;

        $this->processId();
        $this->name = $product->getName();
        $this->description = $product->getDescription();
        $this->link = $product->getProductUrl();

        $this->processPrices();
        $this->processCategories();
        $this->processImages();
        $this->processVariableProduct();
        $this->processAttributes();
        $this->processConfigurableFields();

        return true;
    }

    /**
     * Process product id
     */
    protected function processId()
    {
        $product = $this->product;
        $sku = $product->getSku();

        $this->id = ($sku) ? $sku : (string)$product->getId();
    }

    /**
     * Process product prices
     */
    protected function processPrices()
    {
        $product = $this->product;

        // get tax country from module config
        $configTaxCountry = $this->model("productfeed/config", 'get', 'tax');
        $configTaxCountry = $configTaxCountry['tax_country'];

        //get default shop's country
        $countryCode = Mage::getStoreConfig('general/country/default');

        //get taxes filtered by product's tax class
        $taxCol = Mage::getModel('tax/calculation')->getResourceCollection();
        $productTaxClassId = $product->getTaxClassId();
        $taxCol->addFieldtoFilter('product_tax_class_id', $productTaxClassId);
        $rates = $taxCol->load()->getData();

        $productTax = null;
        $defaultTax = null;

        if ($rates) {

            foreach ($rates as $rate) {
                $rateInfo = Mage::getSingleton('tax/calculation_rate')->load($rate['tax_calculation_rate_id']);

                $countryId = $rateInfo->getTaxCountryId();

                if ($countryId == $configTaxCountry) {
                    $productTax = $rateInfo->getRate();
                } elseif ($countryId == $countryCode) {
                    $defaultTax = $rateInfo->getRate();
                }
            }
        }

        // Regular price
        $prices = array(
            array(
                "currency" => $this->currencyCode,
                "value"    => $this->getPriceWithTax($this->product->getPrice(), $productTax, $defaultTax) * 100,
                "type"     => "regular",
                "vat"      => $this->getFormattedVat($productTax, $defaultTax),
            ),
        );

        // Special price with date range

        if ($product->getSpecialPrice()) {

            $from = new DateTime($product->getSpecialFromDate());
            $from = $from->format('c');

            $to = new DateTime($product->getSpecialToDate());
            $to = $to->format('c');

            $value = $this->getPriceWithTax($product->getSpecialPrice(), $productTax, $defaultTax) * 100;

            $prices[] = array(
                "currency"             => $this->currencyCode,
                "value"                => $value,
                "type"                 => "sale",
                'price_effective_date' => "{$from}/{$to}",
                "vat"                  => $this->getFormattedVat($productTax, $defaultTax),
            );
        }

        // current special price

        $rule = Mage::getModel('catalogrule/rule')->calcProductPriceRule($product, $product->getPrice());

        if ($rule) {
            $prices[] = array(
                "currency" => $this->currencyCode,
                "value"    => $this->getPriceWithTax($rule, $productTax, $defaultTax) * 100,
                "type"     => "sale",
                "vat"      => $this->getFormattedVat($productTax, $defaultTax),
            );
        }

        $this->prices = $prices;
    }

    protected function getPriceWithTax($priceValue, $tax, $defaultTax)
    {
        return $priceValue + ($tax ? ($priceValue * (float)$tax / 100) : ($defaultTax ? $priceValue * (float)$defaultTax / 100 : 0));
    }

    protected function getFormattedVat($tax, $defaultTax)
    {
        return $tax ? ((float)$tax * 100) : ($defaultTax ? (float)$defaultTax * 100 : null);
    }

    /**
     * Process product categories
     */
    protected function processCategories()
    {
        $category_ids = $this->product->getCategoryIds();

        if (empty($category_ids)) {
            return;
        }

        $categories = array();

        foreach ($category_ids as $category_id) {
            $category = $this->model('catalog/category', 'load', $category_id);

            if (!$category) {
                continue;
            }

            $categories[] = array(
                'id'       => (int)$category->getId(),
                'name'     => $category->getName(),
                'parentId' => (int)$category->getParentId(),
            );

            if ($category->getParentId()) {
                $categories = $this->processParentCategory($categories, $category->getParentId());
            }
        }

        if (!empty($categories)) {
            $this->categories = $categories;
        }
    }

    /**
     * get all parent category
     * @param  array $categories List of found categories
     * @param  int $parentId Id of parent category
     * @return array             Full list of categories
     */
    public function processParentCategory($categories, $parentId)
    {
        foreach ($categories as $category) {
            if ($category['id'] == $parentId) {
                return $categories;
            }
        }

        $parent_category = $this->model('catalog/category', 'load', $parentId);

        $category = array(
            'id'   => (int)$parent_category->getId(),
            'name' => $parent_category->getName(),
        );

        if ($parent_category->getParentId() !== 0) {
            $category['parentId'] = $parent_category->getParentId();
            $categories[] = $category;
            $categories = $this->processParentCategory($categories, $parent_category->getParentId());
        } else {
            $categories[] = $category;
        }

        return $categories;
    }

    /**
     * Process product images
     */
    protected function processImages()
    {
        if ($images = $this->product->getMediaGalleryImages()) {
            $additional = array();

            foreach ($images as $image) {
                $additional[] = $image['url'];
            }

            $this->additional_image_links = $additional;
        }

        $this->image_link = (string)Mage::helper('catalog/image')->init($this->product, 'image');
    }

    /**
     * Process on part of configurable product
     */
    protected function processVariableProduct()
    {
        $parent_ids = $this->model('catalog/product_type_configurable', 'getParentIdsByChild', $this->id);

        if (!$parent_ids) {
            $parent_ids = $this->model('catalog/product_type_grouped', 'getParentIdsByChild', $this->id);
        }

        if (isset($parent_ids[0])) {
            $this->item_group_id = $this->model('catalog/product', 'load', $parent_ids[0])->getSku();
        }
    }

    /**
     * Process configurable fields (associated custom product attributes)
     */
    protected function processConfigurableFields()
    {

        $fields = $this->model("productfeed/config", 'get', 'fields');

        $brand = $this->getAttributeValue($fields['brands']);

        if ($brand && $brand !== 'No') {
            $this->brands = array(
                array(
                    'name' => $brand,
                ),
            );
        }

        $dimensions = array();

        $weight = (float)$this->product->getWeigth();

        if ($weight > 0) {

            $dimensions['weight'] = array(
                'value' => $weight,
                'unit'  => $this->getAttributeValue($fields['weight_unit']),
            );
        }

        foreach (array('height', 'length', 'width') as $key) {
            $keyField = "dimention_{$key}";

            if (!isset($fields[$keyField]) || !$fields[$keyField]) {
                continue;
            }

            $attr = $this->getAttributeValue($fields[$keyField]);

            if (!$attr || $attr <= 0) {
                continue;
            }

            $dimensions[$key] = array(
                'value' => (float)$attr,
                'unit'  => $this->getAttributeValue($fields['dimention_unit']),
            );
        }

        if (!empty($dimensions)) {
            $this->dimensions = $dimensions;
        }

        foreach (array("ean", "mpn", "sizeType", "size", "color", "gender", "material", "pattern", "age_group", "condition") as $key) {
            if (!isset($fields[$key]) || !$fields[$key]) {
                continue;
            }

            $attr = $this->getAttributeValue($fields[$key]);

            if ($attr === null) {
                continue;
            }

            $this->{$key} = $attr;
        }
    }

    /**
     * Process product attributes
     */
    protected function processAttributes()
    {
        $fields = $this->model("productfeed/config", 'getSelect', 'fields/attributes');

        $product = $this->product;
        $attributes = array();

        /** @var Mage_Catalog_Model_Resource_Eav_Attribute $attr */
        foreach ($product->getAttributes() as $k => $attr) {
            $code = $attr->getAttributeCode();
            $type = $attr->getBackendType();
            $value = $this->getAttributeValue($code);

            if (!in_array($code, $fields) || $value === null) {
                continue;
            }

            if ($type === 'int' && in_array(strtolower($value), array('yes', 'no'))) {
                continue;
            }

            switch ($type) {
                // String
                case 'varchar':
                    $type = 'string';
                case 'url':
                case 'text':
                case 'string':
                    if ($value === 'no_selection') {
                        // Skip attribute and continue foreach
                        continue 2;
                    }

                    if (preg_match("#^[a-zA-Z]://#", $value)) {
                        $type = 'url';
                    }

                    $value = (string)$value;
                    break;
                // Integer
                case 'int':
                    $type = 'number';
                case 'number':
                    $value = (int)$value;
                    break;
                // Float
                case 'price':
                case 'decimal':
                    $type = 'float';
                case 'float':
                    $value = (float)$value;
                    break;
                // Boolean
                case 'bool':
                    $type = 'boolean';
                case 'boolean':
                    $value = !!$value;
                    break;
                // Date/Time
                case 'datetime':
                    $type = 'time';
                case 'time':
                    $value = date('c', strtotime($value));
                    break;
                // Skip attribute and continue foreach
                case 'static':
                default:
                    continue 2;
            }

            // TODO: implement attribute unit
            $attributes[] = array(
                'name'  => $code,
                'type'  => $type,
                'value' => $value,
            );
        }

        if (!empty($attributes)) {
            $this->attributes = $attributes;
        }
    }

    /**
     * Check if product have simple type
     * @return bool
     */
    public function isSimple()
    {
        return $this->product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE;
    }

    public function isPositiveQuantity()
    {
        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($this->product);
        $quantity = (int)$stockItem->getQty();

        return ($quantity <= 0) ? false : true;
    }

    /**
     * Helper function
     * Get currency code for current store
     * @return string
     */
    protected function getCurrencyCode()
    {
        return Mage::app()->getStore()->getCurrentCurrencyCode();
    }

    /**
     * Helper function
     * Call function of other models
     * @param string $name
     * @param string $func
     * @param mixed $param
     * @return mixed
     */
    protected function model($name, $func, $param)
    {
        return Mage::getModel($name)->{$func}($param);
    }

    /**
     * Helper function
     * Get product attribute object
     * @param string $name
     * @return Mage_Eav_Model_Entity_Attribute_Abstract
     */
    protected function getAttribute($name)
    {
        return $this->resource->getAttribute($name);
    }

    /**
     * Helper function
     * Get product attribute value
     * @param string $name
     * @return mixed
     */
    protected function getAttributeValue($name)
    {
        $attr = $this->getAttribute($name);

        if (!$attr) {
            return null;
        }

        return $attr->getFrontend()->getValue($this->product);
    }
}
