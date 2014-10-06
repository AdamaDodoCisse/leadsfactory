<?php
namespace Tellaw\LeadsFactoryBundle\Utils\Fields;

use Tellaw\LeadsFactoryBundle\Utils\Fields\AbstractFieldType;

/**
 * Field of type ReferenceList will be identified by the type <b>reference-list</b> and will be used to display content of a reference list.
 *
 * <b>data-list</b> : list alias in database
 * <b>data-parent</b> : parent list. If not set, the element is supposed to be parent.
 *
 * Exemple : &lt;field type="linkedreference-list" data-list="salutation" data-parent="list2" />
 *
 * @package Tellaw\LeadsFactoryBundle\Utils\Fields
 */
class LinkedReferenceListFieldType extends AbstractFieldType {

    protected function createInstance () {
        return new LinkedReferenceFieldType();
    }

    /**
     * Render options
     *
     * @param $tag
     * @return string
     */
    public function renderToHtml ($tag)
    {
        $id = $tag['attributes']['id'];
        $name = isset($tag['attributes']['multiple']) ? 'lffield['.$id.'][]' : 'lffield['.$id.']';

        $options = $tag['options'];
        $optionsHtml = '';

        if(isset($tag['attributes']['data-default']))
            $optionsHtml = '<option value="">'.$tag['attributes']['data-default'].'</option>';

        if($options !== false){
            foreach($options as $option){
                $optionsHtml .= '<option value="'.$option->getValue().'">'.$option->getName().'</option>';
            }
        }

        $html = '<select id="lffield['.$id.']" name="'.$name.'" '.$this->getAttributes( $tag ).'>'.$optionsHtml.'</select>';

        return $html;
    }

}