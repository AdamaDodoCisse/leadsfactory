<?php
namespace Weka\LeadsExportBundle\Utils\AthenaV2\weka;

use Weka\LeadsExportBundle\Utils\AthenaV2\AthenaV2BaseMapping;

class WekaAdvMail extends AthenaV2BaseMapping {
    
    public function getType_demande ( $data ) {
        return "email";
    }
}