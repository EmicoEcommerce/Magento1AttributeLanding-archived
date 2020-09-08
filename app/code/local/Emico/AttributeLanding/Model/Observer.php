<?php

/**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico B.V. 2017
 */
class Emico_AttributeLanding_Model_Observer
{
    /**
     * @param Varien_Event_Observer $event
     */
    public function removeLandingFilters(Varien_Event_Observer $event)
    {
        $page = Mage::app()->getRequest()->getParam('page');
        if (!$page instanceof Emico_AttributeLanding_Model_Page) {
            return;
        }

        if (!$page->getHideSelectedFilterGroup()) {
            return;
        }

        $facetHider = $this->getFacetHider();

        $block = $event->getData('block');
        if (!$facetHider->supports($block)) {
            return;
        }

        $facetHider->hideFacets($page, $block);
    }

    /**
     * @return Emico_AttributeLanding_Model_FacetHider_FacetHiderInterface
     */
    protected function getFacetHider()
    {
        if (Mage::helper('core')->isModuleEnabled('Emico_Tweakwise')) {
            return Mage::getModel('emico_attributelanding/facetHider_tweakwiseFacetHider');
            return new Emico_AttributeLanding_Model_FacetHider_TweakwiseFacetHider();
        }
        return Mage::getModel('emico_attributelanding/facetHider_nativeFacetHider');
    }

    /**
     * @param Varien_Event_Observer $event
     * @return array
     * @throws Varien_Exception
     */
    public function addAttributeLandingPagesToCollection(Varien_Event_Observer $event)
    {
        //the only way to add something to the generated xml is to overwrite the class app/code/core/Mage/Sitemap/Model/Sitemap.php, which many modules do.
        //We are now using one of the triggered events to hack our way into it somewhat more gracefully.

        $categoryCollection = $event->getEvent()->getCollection()->getItems();
        $landingPageCollection = Mage::getResourceModel('emico_attributelanding/page_collection');

        foreach ($landingPageCollection as $landingPage) {
            if ($landingPage->getData('active') && $landingPage->getData('url_path')) {
                $sitemapItem = new Varien_Object();
                $sitemapItem->setData('url', $landingPage->getData('url_path'));
                $categoryCollection[] = $sitemapItem;
            }
        }
        $event->getEvent()->getCollection()->setItems($categoryCollection);

        return $categoryCollection;
    }

    /**
     * @param Varien_Event_Observer $event
     * @throws Varien_Exception
     */
    public function addHeadMetaData(Varien_Event_Observer $event)
    {
        $page = Mage::app()->getRequest()->getParam('page');
        if (!$page instanceof Emico_AttributeLanding_Model_Page) {
            return;
        }

        $headBlock = Mage::app()->getLayout()->getBlock('head');
        if (!$headBlock instanceof Mage_Page_Block_Html_Head) {
            return;
        }

        $metaTitle = $page->getMetaTitle();
        if (empty($metaTitle)) {
            $metaTitle = $page->getTitle();
        }
        if (!empty($metaTitle)) {
            $headBlock->setTitle($metaTitle);
        }

        if ($description = $page->getMetaDescription()) {
            $headBlock->setDescription($description);
        }
        if ($keywords = $page->getMetaKeywords()) {
            $headBlock->setKeywords($keywords);
        }

        $this->removeExistingCanonicalUrl($headBlock);

        if ($robots = $page->getRobots()) {
            $headBlock->setData('robots', $robots);
        }

        $headBlock->addLinkRel('canonical', $page->getCanonicalUrl());
    }

    /**
     * @param Varien_Event_Observer $event
     */
    public function setNoIndexNoFollow(Varien_Event_Observer $event)
    {
        $page = Mage::app()->getRequest()->getParam('page');
        if (!$page instanceof Emico_AttributeLanding_Model_Page) {
            return;
        }

        if (!Mage::helper('core')->isModuleEnabled('Emico_Tweakwise')) {
            return;
        }

        $searchAttributes = array_map(
            static function (array $item) {
                return $item['attribute'] . '||' . $item['value'] ;
            },
            $page->getSearchAttributes()
        );


        $layer = Mage::getSingleton('emico_tweakwise/catalog_layer');
        if (!empty(array_diff($this->getSelectedAttributesWithoutCategory($layer), $searchAttributes))) {
            $layout = Mage::app()->getLayout();
            /** @var Mage_Page_Block_Html_Head $head */
            $head = $layout->getBlock('head');
            $head->setData('robots', 'NOINDEX,NOFOLLOW');
        }
    }

    /**
     * @param Emico_Tweakwise_Model_Catalog_Layer $layer
     * @return array
     */
    protected function getSelectedAttributesWithoutCategory(Emico_Tweakwise_Model_Catalog_Layer $layer)
    {
        $selectedFacetsWithoutCategories = [];
        $selectedFacets = $layer->getSelectedFacets();
        foreach ($selectedFacets as $facet) {
            $settings = $facet->getFacetSettings();
            if ($settings->getSource() === Emico_Tweakwise_Model_Bus_Type_Facet_Settings::FACET_SOURCE_CATEGORY) {
                continue;
            }

            foreach ($facet->getActiveAttributes() as $attribute) {
                $selectedFacetsWithoutCategories[] = $facet->getFacetSettings()->getUrlKey() . '||' . $attribute->getTitle();
            }

        }

        return $selectedFacetsWithoutCategories;
    }

    /**
     * @param Mage_Page_Block_Html_Head $head
     */
    protected function removeExistingCanonicalUrl(Mage_Page_Block_Html_Head $head)
    {
        $headItems = $head->getData('items');
        foreach ($headItems as $item) {
            if ($item['type'] !== 'link_rel' || $item['params'] !== 'rel="canonical"') {
                continue;
            }
            $head->removeItem($item['type'], $item['name']);
        }
    }
}
