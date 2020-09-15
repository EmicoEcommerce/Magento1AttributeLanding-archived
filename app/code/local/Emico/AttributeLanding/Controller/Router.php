<?php

/**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico B.V. 2017
 */
class Emico_AttributeLanding_Controller_Router extends Mage_Core_Controller_Varien_Router_Standard
{
    /**
     * Match the request
     *
     * @param Zend_Controller_Request_Http $request
     * @return boolean
     */
    public function match(Zend_Controller_Request_Http $request)
    {
        if (!$this->_beforeModuleMatch()) return false;

        $path = trim($request->getPathInfo(), '/');

        /** @var Emico_AttributeLanding_Model_Page $page */
        $page = Mage::getModel('emico_attributelanding/page');
        $page->loadByUrl($path);

        if (!$page->getId() || !$page->isAllowedForStore()) {
            return false;
        }

        $request
            ->setModuleName('attributelanding')
            ->setControllerName('index')
            ->setActionName('index')
            ->setParam('page', $page);

        $request->setAlias(Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS, $page->getUrlPath());

        return true;
    }
}
