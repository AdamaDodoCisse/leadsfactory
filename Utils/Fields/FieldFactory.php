<?php
/**
 * Created by Olivier Lombard
 * @author olombard
 * Date: 02/03/15
 */
 

namespace Tellaw\LeadsFactoryBundle\Utils\Fields;


class FieldFactory
{
    public function createFromType($type)
    {
        switch ($type) {
            case "email":
                return new EmailFieldType();
            case "reference-list":
                return new ReferenceListFieldType();
            case "textarea":
                return new TextareaFieldType();
            case "checkbox":
                return new CheckboxFieldType();
            case "radio":
                return new RadioFieldType();
            case "linked-reference-list":
                return new LinkedReferenceListFieldType();
            case "hidden":
                return new HiddenFieldType();
            case "text":
            default:
                return new TextFieldType();
        }
    }
}
