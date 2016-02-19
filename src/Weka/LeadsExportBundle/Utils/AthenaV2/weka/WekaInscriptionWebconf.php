<?php

namespace Weka\LeadsExportBundle\Utils\AthenaV2\weka;

use Weka\LeadsExportBundle\Utils\AthenaV2\AthenaV2BaseMapping;

class WekaInscriptionWebconf extends AthenaV2BaseMapping
{
    public function getType_demande ( $data ) {
        return "wk_inscriptions_webconf";
    }
}