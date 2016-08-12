<?php
namespace Tellaw\LeadsFactoryBundle\Utils\Fields;

class CheckboxFieldType extends AbstractFieldType
{
    /**
     * Render HTML
     *
     * @param Object $tag Tag object
     * @return string Html Content formatted
     */
    public function renderToHtml($tag)
    {
        $id = $tag["attributes"]["id"];

        return '<input type="checkbox" name="lffield[' . $id . ']" id="lffield[' . $id . ']" ' . $this->getAttributes($tag) . '/>';
    }
}
