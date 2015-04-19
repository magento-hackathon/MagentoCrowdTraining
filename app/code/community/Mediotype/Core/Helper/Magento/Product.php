<?php
/**
 *
 *
 * @author  Joel Hart   @Mediotype
 */
class Mediotype_Core_Helper_Magento_Product extends Mediotype_Core_Helper_Abstract{

    /**
     * Return a Magento product or null by trying to load the product based on the sku
     *
     * @param $sku string
     * @return mixed
     */
    public function getProductBySku($sku)
    {
        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);

        if(is_null($product->getId())){
            return null;
        }

        return $product;
    }

    /**
     * Removes all images from a product using the Magento API which is incredible safe
     * TODO - Use something else that is faster, possibly a straight write from resource
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function truncateProductImages(Mage_Catalog_Model_Product $product)
    {
        $mediaApi = Mage::getModel("catalog/product_attribute_media_api"); /** @var $mediaApi Mage_Catalog_Model_Product_Attribute_Media_Api */

        // Get all images
        $items = $mediaApi->items($product->getId());

        // Remove each image
        foreach ($items as $item) {
            $mediaApi->remove($product->getId(), $item['file']);
        }

        return true;
    }


    /**
     * Evaluates if a product has images associated with it
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function hasImages(Mage_Catalog_Model_Product $product)
    {
        $product = Mage::getModel('catalog/product')->load($product->getId());
        $imageCount = count($product->getMediaGalleryImages());

        //TODO ask Steven why we did this as a foreach loop
        foreach ($product->getMediaGalleryImages() as $image) {
            return true;
        }
        return false;
    }

}