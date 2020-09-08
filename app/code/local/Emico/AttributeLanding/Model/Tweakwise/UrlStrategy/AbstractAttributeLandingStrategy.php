<?php

/**
 * @author : Edwin Jacobs, email: ejacobs@emico.nl.
 * @copyright : Copyright Emico B.V. 2020.
 */


abstract class Emico_AttributeLanding_Model_Tweakwise_UrlStrategy_AbstractAttributeLandingStrategy
    implements Emico_Tweakwise_Model_UrlBuilder_Strategy_StrategyInterface
{
    /**
     * @var array
     */
    protected $pageLookupTable;

    /**
     * @var bool
     */
    protected $strategyAllowed = true;

    /**
     * @param bool $allowed
     */
    public function setStrategyAllowed(bool $allowed)
    {
        $this->strategyAllowed = $allowed;
    }

    /**
     * @return bool
     */
    public function isStrategyAllowed()
    {
        return $this->strategyAllowed;
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
    ): array {
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
        /** @var Emico_AttributeLanding_Model_Resource_Page_Collection $pageCollection */
        $pageCollection = Mage::getModel('emico_attributelanding/page')->getCollection();
        $pageCollection
            ->addCurrentStoreFilter()
            ->addFieldToFilter('active', 1)
            ->addFieldToFilter('category_id', $category->getId());

        /** @var Emico_AttributeLanding_Model_Page $page */
        foreach ($pageCollection as $page) {
            $filterHash = $this->buildFilterHash($page->getSearchAttributesKvp(), $page->getCategoryId());
            $this->pageLookupTable[$filterHash] = $page;
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

        foreach ($filters as $key => $filterValues) {
            $sanitizedFilters = array_map('strtolower', $filterValues);
            $filters[$key] = $sanitizedFilters;
        }

        if ($categoryId !== null) {
            $filters['category'] = $categoryId;
        }

        ksort($filters);
        return md5(json_encode($filters));
    }

    /**
     * @param Zend_Controller_Request_Http $request
     * @return Emico_Tweakwise_Model_Bus_Request_Navigation
     */
    public function decorateTweakwiseRequest(Zend_Controller_Request_Http $httpRequest, Emico_Tweakwise_Model_Bus_Request_Navigation $tweakwiseRequest)
    {
        if (!$this->strategyAllowed) {
            return $tweakwiseRequest;
        }
        $page = $httpRequest->get('page');
        if (!$page instanceof Emico_AttributeLanding_Model_Page) {
            return $tweakwiseRequest;
        }
        foreach ($page->getSearchAttributesKvp() as $key => $values) {
            foreach ($values as $value) {
                $tweakwiseRequest->addFacetKey($key, $this->getTweakwiseAttributeValue($key, $value));
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
    protected function getTweakwiseAttributeValue(string $key, string $value)
    {
        try {
            return $this->getSlugAttributeMapper()->getAttributeValueBySlug($key, $value);
        } catch (Emico_TweakwiseExport_Model_Exception_SlugMappingException $exception) {
            return $value;
        }
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
        if (!$this->strategyAllowed) {
            return null;
        }
        return $this->buildUrl($state);
    }
}
