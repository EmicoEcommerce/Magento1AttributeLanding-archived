<?php
/**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico B.V. 2017
 */
class Emico_AttributeLanding_Block_Adminhtml_Page_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Emico_AttributeLanding_Block_Adminhtml_Page_Edit constructor.
     */
    public function __construct()
    {
        $this->_objectId = 'page_id';
        parent::__construct();
        $this->_blockGroup      = 'emico_attributelanding';
        $this->_controller      = 'adminhtml_page';
        $this->_mode            = 'edit';
        $modelTitle = $this->_getModelTitle();
        $this->_updateButton('save', 'label', $this->_getHelper()->__("Save $modelTitle"));
        $this->_addButton(
            'saveandcontinue',
            [
                'label'     => $this->_getHelper()->__('Save and Continue Edit'),
                'onclick'   => 'saveAndContinueEdit()',
                'class'     => 'save',
            ],
            -100
        );

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    /**
     * @return Mage_Core_Helper_Abstract
     */
    protected function _getHelper()
    {
        return Mage::helper('emico_attributelanding');
    }

    /**
     * @return Emico_AttributeLanding_Model_Page
     */
    protected function _getModel()
    {
        return Mage::registry('current_attributelanding_page');
    }

    /**
     * @return string
     */
    protected function _getModelTitle()
    {
        return 'Page';
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderText()
    {
        $model = $this->_getModel();
        $modelTitle = $this->_getModelTitle();
        if ($model && $model->getId()) {
           return $this->_getHelper()->__("Edit $modelTitle (ID: {$model->getId()})");
        } else {
           return $this->_getHelper()->__("New $modelTitle");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/index');
    }

    /**
     * {@inheritdoc}
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', array($this->_objectId => $this->getRequest()->getParam($this->_objectId)));
    }

    /**
     * {@inheritdoc}
     */
    public function getSaveUrl()
    {
        $this->setData('form_action_url', 'save');
        return $this->getFormActionUrl();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }
}
