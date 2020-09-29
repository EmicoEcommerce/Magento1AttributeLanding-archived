<?php
/**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico B.V. 2017
 */

/**
 * Class Emico_AttributeLanding_Model_Page
 *
 * @method $this setUrlPath(string $path);
 * @method $this setActive(bool $active);
 * @method bool getActive();
 * @method string getMetaTitle();
 * @method $this setMetaTitle(string $metaTitle);
 * @method string getMetaDescription();
 * @method $this setMetaDescription(string $metaDescription);
 * @method string getMetaKeywords();
 * @method $this setMetaKeywords(string $metaKeywords);
 * @method string getShortDescription();
 * @method $this setShortDescription(string $shortDescription);
 * @method string getLongDescription();
 * @method $this setLongDescription(string $shortDescription);
 * @method string getTitle();
 * @method $this setTitle(string $title);
 * @method $this setSearchAttributes(array $searchAttributes);
 * @method $this setCategoryId(int $categoryId);
 * @method int getCategoryId();
 * @method $this setHideSelectedFilterGroup(bool $hideSelectedFilterGroup);
 * @method bool getHideSelectedFilterGroup();
 * @method bool getOverviewImage();
 * @method $this setOverviewImage(string $filename);
 * @method string getOverviewDescription();
 * @method $this setOverviewDescription(string $description);
 * @method $this setRobots(string $robots);
 * @method $this setCanonicalUrl(string $canonicalUrl)
 *
 */
class Emico_AttributeLanding_Model_Page extends Mage_Core_Model_Abstract
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'emico_attributelanding_page';

    /**
     * Model cache tag for clear cache in after save and after delete
     *
     * When you use true - all cache will be clean
     *
     * @var string || true
     */
    protected $_cacheTag = 'emico_attributelanding_page';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('emico_attributelanding/page');
    }

    /**
     * @param string $urlKey
     * @return $this
     */
    public function loadByUrl($urlKey)
    {
        $this->getResource()->loadByUrl($this, $urlKey);
        return $this;
    }

    /**
     * @return array
     */
    public function getSearchAttributes()
    {
        $data = parent::getData('search_attributes');
        if (!$data) {
            return [];
        }
        return Mage::helper('core')->jsonDecode($data);
    }

    /**
     * @return array
     */
    public function getSearchAttributesKvp()
    {
        $params = [];
        foreach ($this->getSearchAttributes() as $attribute) {
            if (!isset($params[$attribute['attribute']])) {
                $params[$attribute['attribute']] = [];
            }
            $params[$attribute['attribute']][] = $attribute['value'];
        }
        return $params;
    }

    /**
     * @return bool
     */
    public function isAllowedForStore()
    {
        if (!$this->getActive()) {
            return false;
        }

        $allowedStores = $this->getData('store_id');

        if (in_array(0, $allowedStores, false)) {
            return true;
        }

        return in_array(Mage::app()->getStore()->getId(), $allowedStores, false);
    }

    /**
     * {@inheritDoc}
     */
    public function setData($key, $value = null)
    {
        if ($key == 'search_attributes' && is_array($value)) {
            $value = Mage::helper('core')->jsonEncode($value);
        }

        return parent::setData($key, $value);
    }

    /**
     * @return string
     */
    public function getCanonicalUrl()
    {
        $canonicalUrl = $this->getData('canonical_url');
        if ($canonicalUrl) {
            return $canonicalUrl;
        }

        return Mage::getBaseUrl() . $this->getUrlPath();
    }

    /**
     * @return string
     */
    public function getUrlPath()
    {
        $urlPath = $this->getData('url_path');
        if (substr($urlPath, 0, 1) !== '/') {
            $urlPath = '/' . $urlPath;
        }
        return $urlPath;
    }

    /**
     * @return string|null
     */
    public function getRobots()
    {
        return this->getData('robots')?? Mage::getStoreConfig('design/head/default_robots');
    }
}
