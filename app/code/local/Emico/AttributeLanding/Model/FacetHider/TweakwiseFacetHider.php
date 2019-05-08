<?php

/**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico B.V. 2017
 */
class Emico_AttributeLanding_Model_FacetHider_TweakwiseFacetHider implements Emico_AttributeLanding_Model_FacetHider_FacetHiderInterface
{
    /**
     * @param Emico_AttributeLanding_Model_Page $page
     * @param Mage_Core_Block_Template $block
     */
    public function hideFacets(Emico_AttributeLanding_Model_Page $page, Mage_Core_Block_Template $block)
    {
        /** @var Emico_Tweakwise_Block_Catalog_Layer_Facets $block */

        $attributes = $page->getSearchAttributesKvp();

        foreach ($block->getFacetBlocks() as $childBlock) {
            if (isset($attributes[$childBlock->getFacetSettings()->getUrlKey()])) {
                $block->unsetChild($childBlock->getBlockAlias());
            }
        }
    }

    /**
     * Whether this hider supports a given layout block
     *
     * @param Mage_Core_Block_Abstract $block
     * @return mixed
     */
    public function supports(Mage_Core_Block_Abstract $block)
    {
        return ($block instanceof Emico_Tweakwise_Block_Catalog_Layer_Facets);
    }
}