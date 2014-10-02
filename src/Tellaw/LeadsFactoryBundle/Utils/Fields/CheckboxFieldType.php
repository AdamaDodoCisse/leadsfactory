<?php
namespace Tellaw\LeadsFactoryBundle\Utils\Fields;

use Tellaw\LeadsFactoryBundle\Utils\Fields\AbstractFieldType;

class CheckboxFieldType extends AbstractFieldType {

    protected function createInstance () {
        return new TextareaFieldType();
    }

    /**
     * Render HTML
     *
     * @param Object $tag Tag object
     * @return string Html Content formatted
     */
    public function renderToHtml ( $tag )
    {
        $id = $tag["attributes"]["id"];
        return '<input type="checkbox" name="lffield['.$id.']" id="lffield['.$id.']"/>';
    }

}