<?php

namespace Weka\LeadsExportBundle\Utils\Athena;

abstract class AbstractMapping {

    const ENTRY_POINT = 'gateway';

    abstract public function getMappingArray();

    abstract public function getSource();

    abstract public function getAthenaPrivateKey();

    abstract public function getAthenaRequestKey();

    public function getEntryPoint()
    {
        return self::ENTRY_POINT;
    }


    public function getAuthKey ($athenaKeyValue) {

        $toHash = $this->getSource() . "-" . $this->getAthenaPrivateKey() . "-" . $athenaKeyValue;
        $key = md5( $toHash );
        return $key;

    }

    /**
     * Formate le numéro de téléphone
     *
     * @param string $number
     * @return string
     */
    public function formatPhone($number){
        $cleanNumber = str_replace(array(' ', '+'), array('', '00'), $number);
        return $cleanNumber;
    }
} 