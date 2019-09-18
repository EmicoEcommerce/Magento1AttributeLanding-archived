<?php

/**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico B.V. 2017
 */
class Emico_AttributeLanding_Block_Page extends Mage_Core_Block_Template
{

    /**
     * Retrieve current category model object
     *
     * @return Mage_Catalog_Model_Category
     */
    public function getCurrentCategory()
    {
        if (!$this->hasData('current_category')) {
            $this->setData('current_category', Mage::registry('current_category'));
        }
        return $this->getData('current_category');
    }

    /**
     * @return Emico_AttributeLanding_Model_Page
     */
    public function getPage()
    {
        return Mage::app()->getRequest()->getParam('page');
    }

    /**
     * @return string|null
     */
    public function getPageHeader()
    {
        $imagePath = $this->getPage()->getData('header_image');
        if (empty($imagePath)) {
            return null;
        }

        $mediaPath = Mage::getBaseUrl('media');
        return $mediaPath . $imagePath;
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $page = $this->getPage();

        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbsBlock->addCrumb('home', [
                'label' => Mage::helper('catalog')->__('Home'),
                'title' => Mage::helper('catalog')->__('Go to Home Page'),
                'link' => Mage::getBaseUrl()
            ]);

            $currentCategory = $this->getCurrentCategory();
            if ($currentCategory && !$currentCategory->getId() !== Mage::app()->getStore()->getRootCategoryId()) {
                $parents = $currentCategory->getParentCategories();
                /** @var Mage_Catalog_Model_Category $parent */
                foreach ($parents as $parent) {
                    $breadcrumbsBlock->addCrumb('category' . $parent->getId(), [
                        'label' => Mage::helper('catalog')->__($parent->getName()),
                        'title' => Mage::helper('catalog')->__($parent->getName()),
                        'link'  => $parent->getUrl(),
                        'readonly' => true,
                    ]);
                }
            }

            $breadcrumbsBlock->addCrumb('page-detail', [
                'label' => $page->getTitle(),
                'title' => $page->getTitle(),
                'link' => $this->getUrl($page->getUrlPath())
            ]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeToHtml()
    {
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock instanceof Mage_Page_Block_Html_Head) {
            $category = $this->getCurrentCategory();
            if ($category && $this->helper('catalog/category')->canUseCanonicalTag()) {
                $headBlock->removeItem('link_rel', $category->getUrl());
            }
        }

        return parent::_beforeToHtml();
    }

    /**
     * @return string
     */
    public function getProductListBlockHtml()
    {
        $coreHelper = Mage::helper('core');
        if ($coreHelper->isModuleEnabled('Emico_Tweakwise')) {
            return $this->getChildHtml('tweakwise.category.products.wrapper');
        }

        return $this->getChildHtml('product_list');
    }

    /**
     * @return string
     */
    public function getShortDescription()
    {
        $page = $this->getPage();
        $shortDescription = $page->getShortDescription();

        return $this->processTemplate($shortDescription);
    }

    /**
     * @return string
     */
    public function getLongDescription()
    {
        $page = $this->getPage();
        $longDescription = $page->getLongDescription();

        return $this->processTemplate($longDescription);
    }

    /**
     * @param string $data
     * @return string
     */
    protected function processTemplate($data)
    {
        $cmsHelper = $this->getCmsHelper();
        try {
            return $cmsHelper->getPageTemplateProcessor()->filter($data);
        } catch (Exception $exception) {
            Mage::logException($exception);
            return '';
        }
    }

    /**
     * @return Mage_Cms_Helper_Data
     */
    protected function getCmsHelper()
    {
        return Mage::helper('cms');
    }
}
