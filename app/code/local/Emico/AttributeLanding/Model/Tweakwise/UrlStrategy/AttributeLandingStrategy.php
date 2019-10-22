<?php

/**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico B.V. 2017
 */
class Emico_AttributeLanding_Model_Tweakwise_UrlStrategy_AttributeLandingStrategy implements Emico_Tweakwise_Model_UrlBuilder_Strategy_StrategyInterface
{
    /**
     * @var array
     */
    private $pageLookupTable;

    /**
     * Builds the URL for a facet attribute
     *
     * @param Emico_Tweakwise_Model_Catalog_Layer $state
     * @param Emico_Tweakwise_Model_Bus_Type_Attribute $attribute
     * @return null|string
     */
    public function buildUrl(Emico_Tweakwise_Model_Catalog_Layer $state, Emico_Tweakwise_Model_Bus_Type_Facet $facet = null, Emico_Tweakwise_Model_Bus_Type_Attribute $attribute = null)
    {
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
            $filterPart = http_build_query($this->getQueryParamStrategy()->getUrlKeyValPairs($facet, $attribute));
            $removeUrl = $landingPage->getUrlPath();
            if (!empty($filterPart)) {
                $removeUrl .= '?' . $filterPart;
            }
            return $removeUrl;
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

    /**
     * @param Emico_Tweakwise_Model_Catalog_Layer $state
     * @param Emico_Tweakwise_Model_Bus_Type_Facet $facet
     * @param Emico_Tweakwise_Model_Bus_Type_Attribute $attribute
     * @param bool $excludeCurrentAttribute
     * @return array
     * @throws Exception
     */
    protected function getActiveFilters(
        Emico_Tweakwise_Model_Catalog_Layer $state,
        Emico_Tweakwise_Model_Bus_Type_Facet $facet,
        Emico_Tweakwise_Model_Bus_Type_Attribute $attribute,
        $excludeCurrentAttribute = false
    ) {
        $filters = [];

        $slugMapper = $this->getSlugAttributeMapper();
        foreach ($state->getSelectedFacets() as $activeFacet) {
            foreach ($activeFacet->getActiveAttributes() as $activeAttribute) {
                if ($excludeCurrentAttribute && $activeAttribute === $attribute) {
                    continue;
                }
                $filters[$activeFacet->getFacetSettings()->getUrlKey()][] = $slugMapper->getSlugForAttributeValue($activeAttribute->getTitle());
            }
        }

        $facetCode = $facet->getFacetSettings()->getUrlKey();
        $facetValue = $slugMapper->getSlugForAttributeValue($attribute->getTitle());
        if (!isset($filters[$facetCode])) {
            $filters[$facetCode] = [];
        }

        if (!$excludeCurrentAttribute && !in_array($facetValue, $filters[$facetCode], true)) {
            $filters[$facetCode][] = $facetValue;
        }

        return $filters;
    }

    /**
     * @param Zend_Controller_Request_Http $request
     * @return Emico_Tweakwise_Model_Bus_Request_Navigation
     */
    public function decorateTweakwiseRequest(Zend_Controller_Request_Http $httpRequest, Emico_Tweakwise_Model_Bus_Request_Navigation $tweakwiseRequest)
    {
        $page = $httpRequest->get('page');
        if (!$page instanceof Emico_AttributeLanding_Model_Page) {
            return $tweakwiseRequest;
        }
        foreach ($page->getSearchAttributesKvp() as $key => $values) {
            foreach ($values as $value) {
                $tweakwiseRequest->addFacetKey($key, $this->getTweakwiseAttributeValue($value));
            }
        }
        return $tweakwiseRequest;
    }

    /**
     * Filter value configured for attribute landing page can be the raw attribute value or the slug. Accommodate for both cases
     *
     * @param string $value
     * @return int|null|string
     */
    protected function getTweakwiseAttributeValue(string $value)
    {
        try {
            return $this->getSlugAttributeMapper()->getAttributeValueBySlug($value);
        } catch (Emico_TweakwiseExport_Model_Exception_SlugMappingException $exception) {
            return $value;
        }
    }

    /**
     * @param array $filters
     * @param int $categoryId
     * @return string
     */
    protected function buildFilterHash(array $filters, int $categoryId = null)
    {
        unset($filters['categorie']);
        if (empty($filters)) {
            return null;
        }

        if ($categoryId !== null && !$this->isRootCategory($categoryId)) {
            $filters['category'] = $categoryId;
        }
        ksort($filters);
        return md5(json_encode($filters));
    }

    /**
     * @param int $categoryId
     * @return bool
     */
    protected function isRootCategory(int $categoryId): bool
    {
        try {
            $rootCategoryId = (int) Mage::app()->getStore()->getRootCategoryId();
            return $rootCategoryId === $categoryId;
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @param string $filterHash
     * @return Emico_AttributeLanding_Model_Page|null
     */
    protected function findLandingPageByFilters(Mage_Catalog_Model_Category $category, string $filterHash)
    {
        if ($this->pageLookupTable === null) {
            $this->loadPageLookupTable($category);
        }

        if (isset($this->pageLookupTable[$filterHash])) {
            return $this->pageLookupTable[$filterHash];
        }

        return null;
    }

    /**
     * Load lookup table
     * @todo caching
     */
    protected function loadPageLookupTable(Mage_Catalog_Model_Category $category)
    {
        $categoryFilterClause = [
            ['eq' => $category->getId()]
        ];
        if ($this->isRootCategory($category->getId())) {
            $categoryFilterClause[] = ['null' => true];
        }

        /** @var Emico_AttributeLanding_Model_Resource_Page_Collection $pageCollection */
        $pageCollection = Mage::getModel('emico_attributelanding/page')->getCollection();
        $pageCollection
            ->addCurrentStoreFilter()
            ->addFieldToFilter('active', 1)
            ->addFieldToFilter('category_id', $categoryFilterClause);

        /** @var Emico_AttributeLanding_Model_Page $page */
        foreach ($pageCollection as $page) {
            $filterHash = $this->buildFilterHash($page->getSearchAttributesKvp(), $page->getCategoryId());
            $this->pageLookupTable[$filterHash] = $page;
        }
    }

    /**
     * @return Emico_Tweakwise_Model_UrlBuilder_Strategy_QueryParamStrategy|false|Mage_Core_Model_Abstract
     */
    protected function getQueryParamStrategy(): Emico_Tweakwise_Model_UrlBuilder_Strategy_QueryParamStrategy
    {
        return Mage::getModel('emico_tweakwise/urlBuilder_strategy_queryParamStrategy');
    }

    /**
     * @return Emico_TweakwiseExport_Model_SlugAttributeMapping
     */
    protected function getSlugAttributeMapper()
    {
        return Mage::getSingleton('emico_tweakwiseexport/slugAttributeMapping');
    }

    /**
     * @param Emico_Tweakwise_Model_Catalog_Layer $state
     * @return mixed|void
     */
    public function buildCanonicalUrl(Emico_Tweakwise_Model_Catalog_Layer $state)
    {
        return $this->buildUrl($state);
    }
}