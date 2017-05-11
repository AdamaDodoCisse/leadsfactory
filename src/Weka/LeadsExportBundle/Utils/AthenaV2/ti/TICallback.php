<?php

namespace Weka\LeadsExportBundle\Utils\AthenaV2\ti;

use Weka\LeadsExportBundle\Utils\AthenaV2\AthenaV2BaseMapping;

class TICallback extends AthenaV2BaseMapping {

    public function getType_demande ( $data ) {
        return "ti_webcallback";
    }
}