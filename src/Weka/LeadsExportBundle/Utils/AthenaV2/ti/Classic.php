<?php

namespace Weka\LeadsExportBundle\Utils\AthenaV2\ti;

use Weka\LeadsExportBundle\Utils\AthenaV2\AthenaV2BaseMapping;

/**
 * User: Eric Wallet
 * Date: 17/06/2015
 * Time: 16:45
 */

class Classic extends AthenaV2BaseMapping {

    public function getType_demande ( $data ) {
        return "classic";
    }
}