<?php
namespace Tellaw\LeadsFactoryBundle\Utils\Fields;

use Tellaw\LeadsFactoryBundle\Utils\Fields\AbstractFieldType;

class RadioFieldType extends AbstractFieldType
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
	    $name = $tag["attributes"]["name"];
        return '<input type="radio" name="lffield['.$name.']" id="lffield['.$id.']" '.$this->getAttributes( $tag ).'/>';
    }
}
