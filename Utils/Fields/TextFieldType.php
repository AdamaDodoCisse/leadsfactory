<?php
namespace Tellaw\LeadsFactoryBundle\Utils\Fields;

use Tellaw\LeadsFactoryBundle\Utils\Fields\AbstractFieldType;

/**
 * Field of type Text will be identified by the type <b>text</b> and will be used to input a content of simple text.
 *
 * It can be multiline using the attribute <b>display</b> with value multiline. Default will be single line of content.
 *
 * Exemple : &lt;field type="text" id="multiline"/>
 *
 * @package Tellaw\LeadsFactoryBundle\Utils\Fields
 */
class TextFieldType extends AbstractFieldType
{

    public function getTestValue ( $dataType, $field ) {

        if ( $dataType == AbstractFieldType::$_DATATYPE_EMAIL ) {
            return "test-fonctionnel@leadsfactory.com";
        } else {
            if (isset($field["attributes"]["id"])) {
                return "text-".$field["attributes"]["id"];
            }else {
                return "text-".time();
            }
        }

    }

}
