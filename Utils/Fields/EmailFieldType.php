<?php
namespace Tellaw\LeadsFactoryBundle\Utils\Fields;

/**
 * Field of type Email will be identified by the type <b>email</b> and will be used to input a content of type email.
 *
 * Exemple : &lt;field type="email" id="myemail"/>
 *
 * @package Tellaw\LeadsFactoryBundle\Utils\Fields
 */
class EmailFieldType extends AbstractFieldType
{

    public function getTestValue($dataType, $field)
    {

        if ($dataType == AbstractFieldType::$_DATATYPE_EMAIL) {
            return "test-fonctionnel@leadsfactory.com";
        }

    }

}
