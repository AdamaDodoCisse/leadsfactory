<?php

namespace Weka\LeadsExportBundle\Utils\AthenaV2\ti;

use Weka\LeadsExportBundle\Utils\AthenaV2\AthenaV2BaseMapping;

class TICallback extends AthenaV2BaseMapping {

    public function getType_demande ( $data ) {
        return "ti_webcallback";
    }


    public function getTelephone($data) {
        switch ($data['pays'])
        {
            case 'FR':
                $data['phone'] = '+33' . $data['phone'];
                break;
            case 'BE':
                $data['phone'] = '+32' . $data['phone'];
                break;
            case 'MC':
                $data['phone'] = '+377' . $data['phone'];
                break;
            case 'LU':
                $data['phone'] = '+352' . $data['phone'];
                break;
            case 'CH':
                $data['phone'] = '+41' . $data['phone'];
        }
        return $data['phone'];
    }
}