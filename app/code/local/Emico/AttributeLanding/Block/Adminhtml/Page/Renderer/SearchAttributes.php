<?php

/**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico B.V. 2017
 */
class Emico_AttributeLanding_Block_Adminhtml_Page_Renderer_SearchAttributes extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $value =  $row->getData($this->getColumn()->getIndex());
        $searchAttributes = json_decode($value, true);
        $output = '';
        foreach ($searchAttributes as $attribute) {
            $output .= $attribute['attribute'] . ':' . $attribute['value'] . ' ';
        }
        return $output;
    }
}