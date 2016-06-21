<?php
    class Inchoo_Onsale_Block_Catalog_Product_List extends Mage_Catalog_Block_Product_List
    {
        protected function _getProductCollection()
        {
            if (is_null($this->_productCollection)) {
                $layer = $this->getLayer();
                /* @var $layer Mage_Catalog_Model_Layer */
                if ($this->getShowRootCategory()) {
                    $this->setCategoryId(Mage::app()->getStore()->getRootCategoryId());
                }

                // if this is a product view page
                if (Mage::registry('product')) {
                    // get collection of categories this product is associated with
                    $categories = Mage::registry('product')->getCategoryCollection()
                                      ->setPage(1, 1)
                                      ->load();
                    // if the product is associated with any category
                    if ($categories->count()) {
                        // show products from this category
                        $this->setCategoryId(current($categories->getIterator()));
                    }
                }

                $origCategory = null;
                if ($this->getCategoryId()) {
                    $category = Mage::getModel('catalog/category')->load($this->getCategoryId());
                    if ($category->getId()) {
                        $origCategory = $layer->getCurrentCategory();
                        $layer->setCurrentCategory($category);
                        $this->addModelTags($category);
                    }
                }
                $this->_productCollection = $layer->getProductCollection();

//                inchoo start
                $param = Mage::app()->getRequest()->getParam('sale');
                if(isset($param) && $param==='1'){
                    $this->_productCollection
                        ->addFinalPrice()
                        ->getSelect()
                        ->where('price_index.final_price < price_index.price');
                }
//                inchoo end

                $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());

                if ($origCategory) {
                    $layer->setCurrentCategory($origCategory);
                }
            }

            return $this->_productCollection;
//            return parent::_getProductCollection();
        }
    }
