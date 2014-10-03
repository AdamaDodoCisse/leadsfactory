<?php
namespace Tellaw\LeadsFactoryBundle\Utils\Fields;

use Tellaw\LeadsFactoryBundle\Utils\Fields\AbstractFieldType;

/**
 * Field of type ReferenceList will be identified by the type <b>reference-list</b> and will be used to display content of a reference list.
 *
 * <b>multiple</b> : "multiple"<br/>
 * <b>display</b> :   display mode : select|checkbox|radio
 * <b>data-list</b> : list alias in database
 *
 * Exemple : &lt;field type="reference-list" display="options" data-list="salutation" />
 *
 * @package Tellaw\LeadsFactoryBundle\Utils\Fields
 */
class ReferenceListFieldType extends AbstractFieldType {

    protected function createInstance () {
        return new ReferenceFieldType();
    }

    private $_defaultDisplay = 'select';

    /**
     * Render HTML
     *
     * @param Object $tag Tag object
     * @return string Html Content formatted
     */
    public function renderToHtml ( $tag )
    {
        $displayMode = isset($tag['attributes']['display']) ? $tag['attributes']['display'] : $this->_defaultDisplay;

        switch($displayMode){
            case 'select':
                return $this->_getSelectHtml($tag);
            case 'checkbox':
                return $this->_getCheckboxHtml($tag);
            case 'radio':
                return $this->_getRadioHtml($tag);
            default:
                return $this->_getSelectHtml($tag);
        }

    }

    /**
     * Render options as select element
     *
     * @param $tag
     * @return string
     */
    private function _getSelectHtml($tag)
    {
        $id = $tag['attributes']['id'];
        $name = isset($tag['attributes']['multiple']) ? 'lffield['.$id.'][]' : 'lffield['.$id.']';
        $optionsHtml = '';
        foreach($tag['options'] as $option){
            $optionsHtml .= '<option value="'.$option->getValue().'">'.$option->getName().'</option>';
        }
        $html = '<select id="lffield['.$id.']" name="'.$name.'" '.$this->getAttributes( $tag ).'>'.$optionsHtml.'</select>';
        return $html;
    }

    /**
     * Render options as checkboxes
     *
     * @param $tag
     * @return string
     */
    private function _getCheckboxHtml($tag)
    {
        $id = $tag['attributes']['id'];
        $html = '';
        foreach($tag['options'] as $option){
            $html .= '<li class="'.$id.'-item">
                        <label for="lffield['.$option->getValue().'-'.$option->getId().']">'.$option->getName().'</label>
                        <input class="input input-checkbox" name="lffield['.$id.'][]" id="lffield['.$option->getValue().'-'.$option->getId().']" type="checkbox" value="'.$option->getValue().'"/>
                      </li>';
        }
        return '<ul id="'.$id.'">'.$html.'<ul>';
    }

    /**
     * Render options as radio buttons
     *
     * @param $tag
     * @return string
     */
    private function _getRadioHtml($tag)
    {
        $id = $tag['attributes']['id'];
        $html = '';
        foreach($tag['options'] as $option){
            $html .= '<li class="'.$id.'-item">
                        <label for="lffield['.$option->getValue().'-'.$option->getId().']">'.$option->getName().'</label>
                        <input class="input input-radio" name="lffield['.$id.']" id="lffield['.$option->getValue().'-'.$option->getId().']" type="radio" value="'.$option->getValue().'"/>
                      </li>';
        }
        return '<ul id="'.$id.'">'.$html.'<ul>';
    }

}