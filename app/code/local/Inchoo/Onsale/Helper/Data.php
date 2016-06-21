<?php


class Inchoo_Onsale_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getOnSaleUrl()
    {
        $url = Mage::getUrl('', array(
            '_current' => true,
            '_use_rewrite' => true,
            '_query' => array(
                'sale' => 1,
                'p' => NULL
            )
        ));

        return $url;
    }

    public function getNotOnSaleUrl()
    {
        $url = Mage::getUrl('', array(
            '_current' => true,
            '_use_rewrite' => true,
            '_query' => array(
                'sale' => NULL,
                'p' => NULL
            )
        ));

        return $url;
    }
}