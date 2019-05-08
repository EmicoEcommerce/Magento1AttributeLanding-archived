<?php

/**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico B.V. 2017
 */
interface Emico_AttributeLanding_Model_FacetHider_FacetHiderInterface
{
    /**
     * @param Emico_AttributeLanding_Model_Page $page
     * @param Mage_Core_Block_Template $block
     */
    public function hideFacets(Emico_AttributeLanding_Model_Page $page, Mage_Core_Block_Template $block);

    /**
     * Whether this hider supports a given layout block
     *
     * @param Mage_Core_Block_Abstract $block
     * @return mixed
     */
    public function supports(Mage_Core_Block_Abstract $block);
}