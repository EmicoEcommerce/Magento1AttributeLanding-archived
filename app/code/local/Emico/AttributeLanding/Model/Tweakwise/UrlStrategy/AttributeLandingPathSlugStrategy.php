<?php

/**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico B.V. 2017
 */
class Emico_AttributeLanding_Model_Tweakwise_UrlStrategy_AttributeLandingPathSlugStrategy
    extends Emico_AttributeLanding_Model_Tweakwise_UrlStrategy_AbstractAttributeLandingStrategy
    implements Emico_Tweakwise_Model_UrlBuilder_Strategy_RoutingStrategyInterface
{
    /**
     * @var
     */
    protected $_pathStrategyBaseUrl;

    /**
     * @inheritDoc
     */
    public function matchUrl(Zend_Controller_Request_Http $request)
    {
        if (!$this->strategyAllowed) {
            return null;
        }
        $path = trim($request->getPathInfo(), '/');
        $possiblePaths = [];
        foreach (explode('/', $path) as $index => $pathPart) {
            if (empty($possiblePaths)) {
                $possiblePaths[] = $pathPart;
                continue;
            }
            $possiblePaths[$index] = $possiblePaths[$index - 1] . '/' . $pathPart;
        }

        $possiblePages = Mage::getResourceModel('emico_attributelanding/page_collection')
            ->addFieldToFilter('url_path', ['in' => $possiblePaths]);
        if (!$pages = $possiblePages->getItems()) {
            return false;
        }
        foreach ($pages as $page) {
            $page->getResource()->afterLoad($page);
        }

        $pages = array_filter(
            $pages,
            static function (Emico_AttributeLanding_Model_Page $page) {
                return $page->isAllowedForStore();
            }
        );

        if (empty($pages)) {
            return false;
        }

        usort(
            $pages,
            static function (Emico_AttributeLanding_Model_Page $pageA, Emico_AttributeLanding_Model_Page $pageB) {
                return strlen($pageA->getUrlPath()) - strlen($pageB->getUrlPath());
            }
        );
        /** @var Emico_AttributeLanding_Model_Page $page */
        $page = reset($pages);

        $request
            ->setModuleName('attributelanding')
            ->setControllerName('index')
            ->setActionName('index')
            ->setParam('page', $page);

        $request->setAlias(Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS, $path);
        $request->setParam('filter_path', substr($path, strlen($page->getUrlPath())));

        return true;
    }

    /**
     * Builds the URL for a facet attribute
     *
     * @param Emico_Tweakwise_Model_Catalog_Layer $state
     * @param Emico_Tweakwise_Model_Bus_Type_Facet|null $facet
     * @param Emico_Tweakwise_Model_Bus_Type_Attribute $attribute
     * @return null|string
     * @throws Exception
     */
    public function buildUrl(Emico_Tweakwise_Model_Catalog_Layer $state, Emico_Tweakwise_Model_Bus_Type_Facet $facet = null, Emico_Tweakwise_Model_Bus_Type_Attribute $attribute = null)
    {
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

        /** @var Emico_AttributeLanding_Model_Page $landingPage */
        $landingPage = Mage::app()->getRequest()->getParam('page');

        $twUrlStrategy = Mage::helper('emico_attributelanding')->getPathSlugStrategy();
        $twUrlResult = $twUrlStrategy->buildUrl($state, $facet, $attribute);

        if ($landingPage && $attribute->getIsSelected() && $this->isFilterPartOfLandingPage($facet, $attribute, $landingPage)) {
            // This will construct the clear filter url
            return $twUrlResult . '#no-ajax';
        }

        // Check if we have an attribute landing page available for the filter combination
        $filters = $this->getActiveFilters($state, $facet, $attribute);
        $filterHash = $this->buildFilterHash($filters, $category->getId());
        $targetLandingPage = $filterHash ? $this->findLandingPageByFilters($category, $filterHash) : null;

        if ($targetLandingPage) {
            return '/' . $targetLandingPage->getUrlPath() . '#no-ajax';
        }

        // No matches
        if (!$landingPage) {
            return null;
        }

        $strippedTwUrlResult = str_replace([$this->getPathStrategyBaseUrl($category), $category->getUrlPath()], '', $twUrlResult);
        foreach ($landingPage->getSearchAttributesKvp() as $filter => $filterValues) {
            foreach ($filterValues as $filterValue) {
                $strippedTwUrlResult = str_replace(strtolower("/$filter/$filterValue"), '', $strippedTwUrlResult);
            }
        }

        return "{$landingPage->getUrlPath()}$strippedTwUrlResult";
    }

    /**
     * @param Emico_Tweakwise_Model_Bus_Type_Facet $facet
     * @param Emico_Tweakwise_Model_Bus_Type_Attribute $attribute
     * @param Emico_AttributeLanding_Model_Page $landingPage
     * @return bool
     */
    protected function isFilterPartOfLandingPage(
        Emico_Tweakwise_Model_Bus_Type_Facet $facet,
        Emico_Tweakwise_Model_Bus_Type_Attribute $attribute,
        Emico_AttributeLanding_Model_Page $landingPage
    ) {
        $pageFilterDefinition = $landingPage->getSearchAttributesKvp();
        if (!isset($pageFilterDefinition[$facet->getFacetSettings()->getUrlKey()])) {
            return false;
        }

        $filterValue = $pageFilterDefinition[$facet->getFacetSettings()->getUrlKey()];
        return in_array($attribute->getTitle(), $filterValue,true);
    }

    /**
     * This is a duplicate from tweakwise path strategy since it is protected
     *
     * @return string
     * @throws Mage_Core_Model_Store_Exception
     */
    protected function getPathStrategyBaseUrl(Mage_Catalog_Model_Category $category)
    {
        if ($this->_pathStrategyBaseUrl === null) {
            $categoryUrl = $category->getUrl();
            $queryPosition = strpos($categoryUrl, '?');
            $this->_pathStrategyBaseUrl = ($queryPosition > 0) ? substr($categoryUrl, 0, $queryPosition) : $categoryUrl;
        }

        return rtrim($this->_pathStrategyBaseUrl, '/');
    }
}
