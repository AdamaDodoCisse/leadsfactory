<?php
namespace Weka\LeadsExportBundle\Utils\AthenaV2\weka;

use Weka\LeadsExportBundle\Utils\AthenaV2\AthenaV2BaseMapping;

class WekaIntegral extends AthenaV2BaseMapping {
    
    public function getType_demande ( $data ) {
        return "wk_di_integral";
    }
}