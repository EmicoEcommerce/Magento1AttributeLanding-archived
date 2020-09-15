<?php

/**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico B.V. 2017
 */
class Emico_AttributeLanding_Model_Tweakwise_UrlStrategy_AttributeLandingStrategy extends Emico_AttributeLanding_Model_Tweakwise_UrlStrategy_AbstractAttributeLandingStrategy
{
    /**
     * Builds the URL for a facet attribute
     *
     * @param Emico_Tweakwise_Model_Catalog_Layer $state
     * @param Emico_Tweakwise_Model_Bus_Type_Facet|null $facet
     * @param Emico_Tweakwise_Model_Bus_Type_Attribute $attribute
     * @return null|string
     * @throws Exception
     */
    public function buildUrl(
        Emico_Tweakwise_Model_Catalog_Layer $state,
        Emico_Tweakwise_Model_Bus_Type_Facet $facet = null,
        Emico_Tweakwise_Model_Bus_Type_Attribute $attribute = null
    ) {
        if (!$this->strategyAllowed) {
            return null;
        }
        if ($facet === null || $attribute === null || $facet->isCategory()) {
            return null;
        }

        $category = Mage::registry('current_category');
        if (!$category instanceof Mage_Catalog_Model_Category) {
            return null;
        }

        // Construct URL to remove the filter when we are on a landing page scope and we have selected filters
        /** @var Emico_AttributeLanding_Model_Page $landingPage */
        $landingPage = Mage::app()->getRequest()->getParam('page');
        if ($landingPage && $attribute->getIsSelected()) {
            $queryParamStrategy = Mage::helper('emico_attributelanding')->getQueryParamStrategy();
            if (!$queryParamStrategy) {
                return null;
            }
            return $landingPage->getUrlPath() . '?' . http_build_query($queryParamStrategy->getUrlKeyValPairs($facet, $attribute));
        }

        // Check if we have an attribute landing page available for the filter combination
        $filters = $this->getActiveFilters($state, $facet, $attribute);
        $filterHash = $this->buildFilterHash($filters, $category->getId());
        if ($filterHash === null) {
            return null;
        }

        $page = $this->findLandingPageByFilters($category, $filterHash);
        if ($page !== null) {
            return $page->getUrlPath() . '#no-ajax';
        }

        return null;
    }
}
