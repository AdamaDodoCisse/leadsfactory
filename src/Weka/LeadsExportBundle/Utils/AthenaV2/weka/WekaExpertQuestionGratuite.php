<?php
namespace Weka\LeadsExportBundle\Utils\AthenaV2\weka;

use Weka\LeadsExportBundle\Utils\AthenaV2\AthenaV2BaseMapping;

class WekaExpertQuestionGratuite extends AthenaV2BaseMapping {

    public function getDetail_demande($data)
    {
        $comment = parent::getDetail_demande($data);
        
        if(!empty($data['date'])){
            $comment .= "\nDate de rappel souhaitée : ".$data['date'];
            if(!empty($data['plage_horaire'])){
                $comment .= " - ".$data['plage_horaire'];
            }
        }

        return $comment;
    }
}
