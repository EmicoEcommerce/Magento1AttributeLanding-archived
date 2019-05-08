<?php

/**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico B.V. 2017
 */
class Emico_AttributeLanding_Block_Adminhtml_Page_Edit_Field_AttributeSelection extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn(
            'attribute',
            [
                'label' => Mage::helper('adminhtml')->__('Attribute'),
                'style' => 'width:120px',
            ]
        );
        $this->addColumn(
            'value',
            [
                'label' => Mage::helper('adminhtml')->__('Value'),
                'style' => 'width:120px',
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add filter');
        parent::__construct();
    }
}