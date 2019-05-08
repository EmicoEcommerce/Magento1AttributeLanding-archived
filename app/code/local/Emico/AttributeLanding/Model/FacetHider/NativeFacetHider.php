<?php

/**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico B.V. 2017
 */
class Emico_AttributeLanding_Model_FacetHider_NativeFacetHider implements Emico_AttributeLanding_Model_FacetHider_FacetHiderInterface
{
    /**
     * @param Emico_AttributeLanding_Model_Page $page
     * @param Mage_Core_Block_Template $block
     */
    public function hideFacets(Emico_AttributeLanding_Model_Page $page, Mage_Core_Block_Template $block)
    {
        /** @var Mage_Catalog_Block_Layer_View $block */
        $attributes = $page->getSearchAttributesKvp();

        foreach ($block->getLayer()->getState()->getFilters() as $filterItem) {
            if (!$filterItem instanceof Mage_Catalog_Model_Layer_Filter_Item) {
                continue;
            }

            $filter = $filterItem->getFilter();
            if (!isset($attributes[$filter->getRequestVar()])) {
                continue;
            }

            $filterItem->setData('hidden', true);
            $filter->setData('hidden', true);
        }
    }

    /**
     * Whether this hider supports a given layout block
     *
     * @param Mage_Core_Block_Abstract $block
     * @return mixed
     */
    public function supports(Mage_Core_Block_Abstract  $block)
    {
        return ($block instanceof Mage_Catalog_Block_Layer_View);
    }
}