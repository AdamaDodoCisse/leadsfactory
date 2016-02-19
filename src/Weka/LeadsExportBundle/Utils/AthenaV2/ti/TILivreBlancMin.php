<?php

namespace Weka\LeadsExportBundle\Utils\AthenaV2\ti;

use Weka\LeadsExportBundle\Utils\AthenaV2\AthenaV2BaseMapping;

class TILivreBlancMin extends AthenaV2BaseMapping {

    public function getType_demande ( $data ) {
        return "ti_livreblanc_min";
    }
}