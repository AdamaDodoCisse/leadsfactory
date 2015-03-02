<?php
namespace Tellaw\LeadsFactoryBundle\Utils\Fields;

use Tellaw\LeadsFactoryBundle\Utils\Fields\AbstractFieldType;

class HiddenFieldType extends AbstractFieldType
{
    /**
     * Render HTML
     *
     * @param Object $tag Tag object
     * @return string Html Content formatted
     */
    public function renderToHtml ( $tag )
    {
        if (array_key_exists('id', $tag["attributes"])) {
            $id = $tag["attributes"]["id"];
        } else {
	        $id = false;
        }

        if (array_key_exists('name', $tag["attributes"])) {
            $name = $tag["attributes"]["name"];
        } else {
            $name = $id;
        }

        $html = '<input type="hidden"';
        $html .= ' name="lffield['.$name.']"';
        if ($id) {
            $html .= ' id="lffield['.$id.']"';
        }
        $html .= ' '.$this->getAttributes( $tag ).'/>';

        return $html;
    }
}
