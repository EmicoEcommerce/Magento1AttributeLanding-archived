<?php

/**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico B.V. 2017
 */
class Emico_AttributeLanding_Helper_Page extends Mage_Core_Helper_Abstract
{
    /**
     * Retrieve page direct URL
     *
     * @param Emico_AttributeLanding_Model_Page $page
     * @return string
     */
    public function getPageUrl(Emico_AttributeLanding_Model_Page $page)
    {
        return Mage::getUrl(null, array('_direct' => $page->getUrlPath()));
    }
}