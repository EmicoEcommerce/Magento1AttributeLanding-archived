<?php
/**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico B.V. 2017
 */
class Emico_AttributeLanding_Block_Adminhtml_Page extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Emico_AttributeLanding_Block_Adminhtml_Page constructor.
     */
    public function __construct()
    {
        $this->_blockGroup      = 'emico_attributelanding';
        $this->_controller      = 'adminhtml_page';
        $this->_headerText      = $this->__('AttributeLanding pages overview');
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getUrl('*/*/new');
    }

}

