<?php
namespace Tellaw\LeadsFactoryBundle\Utils\Fields;

use Tellaw\LeadsFactoryBundle\Utils\Fields\AbstractFieldType;

/**
 * Field of type ReferenceList will be identified by the type <b>reference-list</b> and will be used to display content of a reference list.
 *  *
 * <b>selection</b> : "multiple" or "single" will switch from Select field to checkbox field<br/>
 * <b>display</b> : "options" will force display of options instead of default selection (select)
 *
 * Exemple : &lt;field type="reference-list" selection="single" display="options"/>
 *
 * @package Tellaw\LeadsFactoryBundle\Utils\Fields
 */
class ReferenceFieldType extends AbstractFieldType {

    protected function createInstance () {
        return new ReferenceFieldType();
    }



}