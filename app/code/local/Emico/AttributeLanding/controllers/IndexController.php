<?php

class Emico_AttributeLanding_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * @param Emico_AttributeLanding_Model_Page $page
     * @return bool|Mage_Core_Model_Abstract
     */
    protected function initCategory(Emico_AttributeLanding_Model_Page $page)
    {
        $categoryId = $page->getCategoryId() ? $page->getCategoryId() : Mage::app()->getStore()->getRootCategoryId();

        $category = Mage::getModel('catalog/category')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($categoryId);

        Mage::register('current_category', $category);
        Mage::register('current_entity_key', $category->getPath());

        return $category;
    }

    /**
     * @param Emico_AttributeLanding_Model_Page $page
     */
    protected function setFilterState(Emico_AttributeLanding_Model_Page $page)
    {
        if (Mage::helper('core')->isModuleEnabled('Emico_Tweakwise')) {
            return;
        }

        $request = $this->getRequest();
        foreach ($page->getSearchAttributesKvp() as $key => $values) {
            $existingValues = array_filter(explode('|', $request->getParam($key)));
            $request->setParam($key, implode('|', array_merge($existingValues, $values)));
        }
    }

    /**
     * Render landing page
     */
    public function indexAction()
    {
        $page = $this->getRequest()->getParam('page');
        if (!$page instanceof Emico_AttributeLanding_Model_Page) {
            $this->norouteAction();
            return;
        }

        $this->initCategory($page);
        $this->setFilterState($page);

        if (!$this->getRequest()->getAlias(Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS)) {
            $this->getRequest()->setAlias(Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS, $page->getUrlPath());
        }

        if (Mage::helper('core')->isModuleEnabled('Emico_Tweakwise') && $templateId = $page->getData('tweakwise_template')) {
            Mage::getSingleton('emico_tweakwise/catalog_layer')->setTemplateId($templateId);
        }

        $layoutHandles = [
            'default',
            'emico_attributelanding_index_index',
            'emico_attributelanding_implementation_' . $this->getLayoutHandle()
        ];

        if ($page->getData('custom_layout_handle')) {
            $layoutHandles[] = $page->getData('custom_layout_handle');
        }

        $this->loadLayout($layoutHandles);
        $this->renderLayout();
    }

    /**
     * @return string
     */
    protected function getLayoutHandle()
    {
        $coreHelper = Mage::helper('core');
        if ($coreHelper->isModuleEnabled('Emico_Tweakwise')) {
            return 'tweakwise';
        }

        return 'native';
    }
}
