<?php
/**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico B.V. 2017
 */ 
class Emico_AttributeLanding_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @return Emico_Tweakwise_Model_UrlBuilder_Strategy_PathStrategy
     */
    public function getPathSlugStrategy(): ?Emico_Tweakwise_Model_UrlBuilder_Strategy_StrategyInterface
    {
        $twUriStrategyHelper = Mage::helper('emico_tweakwise/uriStrategy');
        /** @noinspection NullPointerExceptionInspection */
        return $twUriStrategyHelper->getStrategy('path');
    }

    /**
     * @return Emico_Tweakwise_Model_UrlBuilder_Strategy_QueryParamStrategy
     */
    public function getQueryParamStrategy(): ?Emico_Tweakwise_Model_UrlBuilder_Strategy_QueryParamStrategy
    {
        $twUriStrategyHelper = Mage::helper('emico_tweakwise/uriStrategy');
        /** @noinspection NullPointerExceptionInspection */
        return $twUriStrategyHelper->getStrategy('queryParam');
    }
}
