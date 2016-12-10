<?php
namespace LeadsFactoryBundle\Utils\Fields;

/**
 * Field of type File will be identified by the type <b>file</b> and will be used to upload files(s).
 *
 * @package LeadsFactoryBundle\Utils\Fields
 */
class FileFieldType extends AbstractFieldType
{

    public function getTestValue($dataType, $field)
    {

        if (isset($field["attributes"]["id"])) {
            return "file-" . $field["attributes"]["id"];
        } else {
            return "file-" . time();
        }

    }

    /**
     * Render HTML
     *
     * @param Object $tag Tag object
     * @return string Html Content formatted
     */
    public function renderToHtml($tag)
    {
        $id = $tag["attributes"]["id"];
        $name = $tag["attributes"]["name"];

        return '<input type="file" name="lffield[' . $name . ']" id="lffield[' . $id . ']" ' . $this->getAttributes($tag) . '/>';
    }

}
