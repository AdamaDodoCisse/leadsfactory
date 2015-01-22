<?php
namespace Tellaw\LeadsFactoryBundle\Utils\Fields;

use Tellaw\LeadsFactoryBundle\Utils\Fields\AbstractFieldType;

class HiddenFieldType extends AbstractFieldType {

    protected function createInstance () {
        return new HiddenFieldType();
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
        return '<input type="hidden" name="lffield['.$id.']" id="lffield['.$id.']" '.$this->getAttributes( $tag ).'/>';
    }

}