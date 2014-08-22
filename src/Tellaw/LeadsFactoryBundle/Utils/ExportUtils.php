<?php
namespace Tellaw\LeadsFactoryBundle\Utils;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ExportUtils{

    const CSV_METHOD = 'csv';

    /**
     * @var array
     */
    static $export_methods = array(
        self::CSV_METHOD
    );

    /**
     * Check if form is configured for export
     *
     * @param $exportConfig
     * @return bool
     */
    public function hasScheduledExport($exportConfig)
    {
        $exportConfig = json_decode(trim($exportConfig), true);
        foreach(self::$export_methods as $type){
            if(array_key_exists($type, $exportConfig)){
                return true;
            }
        }
        return false;
    }

    /**
     * Check if export method is valid
     *
     * @param $method
     * @return bool
     */
    public function isValidExportMethod($method)
    {
        return in_array($method, self::$export_methods) ? true : false;
    }
}

