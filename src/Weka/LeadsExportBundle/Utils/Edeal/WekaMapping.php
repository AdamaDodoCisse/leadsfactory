<?php
/**
 * Created by Olivier Lombard
 * @author olombard
 * Date: 02/04/15
 */
 

namespace Weka\LeadsExportBundle\Utils\Edeal;


class WekaMapping extends BaseMapping
{
    public function getCpwPaysCode($data)
    {
        return 'FR';
    }

    public function getEntCtrCode($data)
    {
        return 'FR';
    }

    public function getPerCtrCode($data)
    {
        return 'FR';
    }
}
