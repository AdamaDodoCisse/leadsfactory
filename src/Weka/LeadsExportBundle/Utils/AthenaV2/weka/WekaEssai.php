<?php

namespace Weka\LeadsExportBundle\Utils\AthenaV2\weka;

use Weka\LeadsExportBundle\Utils\AthenaV2\AthenaV2BaseMapping;

class WekaEssai extends AthenaV2BaseMapping {
    
    public function getType_demande ( $data ) {
        return "essai";
    }
}