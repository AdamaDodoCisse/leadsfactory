<?php
namespace Tellaw\LeadsFactoryBundle\Utils\Fields;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Tellaw\LeadsFactoryBundle\Utils\Fields\AbstractFieldType;

/**
 * Field of type ReferenceList will be identified by the type <b>reference-list</b> and will be used to display content of a reference list.
 *  *
 * <b>selection</b> : "multiple" or "single" will switch from Select field to checkbox field<br/>
 * <b>display</b> : "options" will force display of options instead of default selection (select)
 * <b>data-list</b> : code of list in database
 *
 * Exemple : &lt;field type="reference-list" selection="single" display="options" data-list="salutation" />
 *
 * @package Tellaw\LeadsFactoryBundle\Utils\Fields
 */
class ReferenceListFieldType extends AbstractFieldType {

    protected function createInstance () {
        return new ReferenceFieldType();
    }

    /**
     * Render HTML
     *
     * @param Object $tag Tag object
     * @return string Html Content formatted
     */
    public function renderToHtml ( $tag ) {

        $id = $tag["attributes"]["id"];
        $optionsHtml = '';
        foreach($tag['options'] as $option){
            $optionsHtml .= '<option value="'.$option->getValue().'">'.$option->getName().'</option>';
        }
        $html = '<select id="lffield['.$id.']" name="lffield['.$id.']" '.$this->getAttributes( $tag ).'>'.$optionsHtml.'</select>';
        return $html;
    }

}