<?php

/**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico B.V. 2017
 */
class Emico_AttributeLanding_Block_Adminhtml_Page_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @return Emico_AttributeLanding_Model_Page
     */
    protected function _getModel()
    {
        return Mage::registry('current_attributelanding_page');
    }

    /**
     * @return Mage_Core_Helper_Abstract
     */
    protected function _getHelper()
    {
        return Mage::helper('emico_attributelanding');
    }

    /**
     * @return string
     */
    protected function _getModelTitle()
    {
        return 'Page';
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $model = $this->_getModel();
        $modelTitle = $this->_getModelTitle();
        $form = new Varien_Data_Form(
            [
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save'),
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            ]
        );

        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => $this->_getHelper()->__("$modelTitle Information"),
                'class' => 'fieldset-wide',
            ]
        );

        if ($model && $model->getId()) {
            $modelPk = $model->getResource()->getIdFieldName();
            $fieldset->addField(
                $modelPk,
                'hidden',
                [
                    'name' => $modelPk,
                ]
            );
        }

        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $field = $fieldset->addField('store_id', 'multiselect', array(
                'name' => 'stores[]',
                'label' => Mage::helper('cms')->__('Store View'),
                'title' => Mage::helper('cms')->__('Store View'),
                'required' => true,
                'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true)
            ));
            $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField('store_id', 'hidden', array(
                'name' => 'stores[]',
                'value' => Mage::app()->getStore(true)->getId()
            ));
            $model->setStoreId(Mage::app()->getStore(true)->getId());
        }


        $fieldset->addField(
            'active',
            'checkbox', [
                'name' => 'active',
                'label' => $this->_getHelper()->__('Active'),
                'data-form-part' => $this->getData('edit_form'),
                'onclick' => 'this.value = this.checked ? 1 : 0;',
            ]
        );

        $fieldset->addField(
            'url_path',
            'text',
            [
                'name' => 'url_path',
                'label' => $this->_getHelper()->__('URL Path'),
                'title' => $this->_getHelper()->__('The url slug to use'),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'type',
            'text',
            [
                'name' => 'type',
                'label' => $this->_getHelper()->__('Page type'),
                'title' => $this->_getHelper()->__('Define a type so you can easily query on pages matching this type'),
                'required' => false,
            ]
        );

        $fieldset->addField(
            'category_id',
            'text',
            [
                'name' => 'category_id',
                'label' => $this->_getHelper()->__('Category ID'),
            ]
        );

        $fieldset->addField(
            'title',
            'text', [
                'name' => 'title',
                'label' => $this->_getHelper()->__('Title'),
            ]
        );

        $fieldset->addField('header_image',
            'image', [
                'name' => 'header_image',
                'label' => $this->_getHelper()->__('Header image'),
                'required' => false
            ]);

        $fieldset->addField(
            'meta_title',
            'text', [
                'name' => 'meta_title',
                'label' => $this->_getHelper()->__('Meta title'),
            ]
        );

        $fieldset->addField(
            'meta_keywords',
            'text', [
                'name' => 'meta_keywords',
                'label' => $this->_getHelper()->__('Meta keywords'),
            ]
        );

        $fieldset->addField(
            'canonical_url',
            'text', [
                'name' => 'canonical_url',
                'label' => $this->_getHelper()->__('Canonical url'),
                'after_element_html' => 'Specify canonical url if needed, if left empty it will fall back the page url.'
            ]
        );

        $fieldset->addField(
            'meta_description',
            'text', [
                'name' => 'meta_description',
                'label' => $this->_getHelper()->__('Meta description'),
            ]
        );

        $robotOptions = Mage::getModel('adminhtml/system_config_source_design_robots')->toOptionArray();
        // Add empty options to robot options.
        array_unshift($robotOptions, ['value' => '', 'label' => '']);
        $fieldset->addField(
            'robots',
            'select',
            [
                'label' => $this->_getHelper()->__('Robots'),
                'values' => $robotOptions,
                'name' => 'robots',
                'after_element_html' => 'Specify robots if needed, if left empty it will fallback to site default setting.'
            ]
        );

        $fieldset->addField(
            'custom_layout_handle',
            'text', [
                'name' => 'custom_layout_handle',
                'label' => $this->_getHelper()->__('Extra layout handle'),
            ]
        );

        $fieldset->addField(
            'short_description',
            'editor', [
                'name' => 'short_description',
                'label' => $this->_getHelper()->__('Text above results'),
                'config' => Mage::getSingleton('cms/wysiwyg_config')->getConfig(),
            ]
        );

        $fieldset->addField(
            'long_description',
            'editor', [
                'name' => 'long_description',
                'label' => $this->_getHelper()->__('Text below results'),
                'config' => Mage::getSingleton('cms/wysiwyg_config')->getConfig(),
            ]
        );

        /** @var Varien_Data_Form_Element_Abstract $searchAttributesField */
        $searchAttributesField = $fieldset->addField(
            'search_attributes',
            'text', [
                'name' => 'search_attributes',
                'label' => $this->_getHelper()->__('Search attributes'),
            ]
        );

        $fieldset->addField(
            'hide_selected_filter_group',
            'checkbox', [
                'name' => 'hide_selected_filter_group',
                'label' => $this->_getHelper()->__('Hide selected filters'),
                'data-form-part' => $this->getData('edit_form'),
                'onclick' => 'this.value = this.checked ? 1 : 0;',
            ]
        );

        $renderer = $this->getLayout()->createBlock('emico_attributelanding/adminhtml_page_edit_field_attributeSelection');
        $searchAttributesField->setRenderer($renderer);

        if (Mage::helper('core')->isModuleEnabled('Emico_Tweakwise')) {
            $fieldset->addField('tweakwise_template', 'select', [
                'label' => $this->_getHelper()->__('Tweakwise template'),
                'values' => Mage::getModel('emico_tweakwise/system_config_source_template')->toOptionArray(),
                'name' => 'tweakwise_template',
                'required' => true,
                'comment' => 'Attribuut moet filterbaar zijn in geselecteerde template'
            ]);
        }

        if ($model) {
            $form->setValues($model->getData());
            $searchAttributesField->setValue($model->getSearchAttributes());
            if ($model->getHideSelectedFilterGroup() === null) {
                $form->getElement('hide_selected_filter_group')->setIsChecked(true)->setValue('1');
            } else {
                $form->getElement('hide_selected_filter_group')->setIsChecked($model->getHideSelectedFilterGroup());
            }
            $form->getElement('active')->setIsChecked($model->getActive());
        }
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}
