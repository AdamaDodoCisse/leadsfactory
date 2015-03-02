<?php
namespace Tellaw\LeadsFactoryBundle\Utils\Fields;

use Tellaw\LeadsFactoryBundle\Utils\Fields\AbstractFieldType;

class TextareaFieldType extends AbstractFieldType
{
    /**
     * Render HTML
     *
     * @param Object $tag Tag object
     * @return string Html Content formatted
     */
    public function renderToHtml ( $tag )
    {
        $id = $tag["attributes"]["id"];
        return '<textarea name="lffield['.$id.']" id="lffield['.$id.']"></textarea>';
        //return "<input type='text' name='lffield[".$id."]' id='lffield[".$id."]' value='' ".$this->getAttributes( $tag )." />";
    }
}
