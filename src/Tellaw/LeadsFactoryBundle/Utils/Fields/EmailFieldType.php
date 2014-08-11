<?php
namespace Tellaw\LeadsFactoryBundle\Utils;

use Tellaw\LeadsFactoryBundle\Utils\AbstractFieldType;

class EmailFieldType extends AbstractFieldType {

    private static $_instance = null;

    public static function getInstance () {

        if(is_null(self::$_instance)) {
            self::$_instance = new EmailFieldType();
        }

        return self::$_instance;

    }

}