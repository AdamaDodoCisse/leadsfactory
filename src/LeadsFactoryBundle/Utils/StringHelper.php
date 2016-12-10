<?php
/**
 * Created by Olivier Lombard
 * @author olombard
 * Date: 14/10/14
 */

namespace LeadsFactoryBundle\Utils;


class StringHelper
{
    public static function camelize($s)
    {
        $s = str_replace(array('-', '_'), ' ', $s);
        $s = ucwords($s);
        $s = str_replace(array(' '), '', $s);

        return $s;
    }
}
