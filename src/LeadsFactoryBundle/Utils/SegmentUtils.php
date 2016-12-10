<?php
/**
 * Created by PhpStorm.
 * User: seth
 * Date: 09/12/15
 * Time: 11:38
 */

namespace LeadsFactoryBundle\Utils;

use Doctrine\ORM\Mapping\Entity;
use LeadsFactoryBundle\Entity\MkgSegment;


class SegmentUtils
{
    /**
     * @param $request
     * @param MkgSegment $segment
     */
    public static function addFilterConfig(&$request, MkgSegment $segment)
    {
        $variables = array();
        $query_json = json_decode($request, true);
        $query = $query_json['query']['query_string']['query'];
        $range = $query_json['filter']['range']['createdAt'];
        $sort[] = array("createdAt" => array("order" => "desc"));

        $configs = json_decode($segment->getFilter());

        foreach ($configs as $alias => $config) {
            $variables[$alias] = $config;
        }
        $variables['nbdays'] = $segment->getNbDays();
        $variables['dateStart'] = $segment->getDateStart();
        $variables['dateEnd'] = $segment->getDateEnd();

        // Add text variable filter
        self::fillRequestWithTextVariable($query, $variables);

        // Add range filter
        self::fillRequestWithRangeVariable($range, $variables);

        $query_json['query']['query_string']['query'] = $query;
        $query_json['filter']['range']['createdAt'] = $range;

        $request = json_encode($query_json);
    }

    /**
     * @param $range
     * @param $variables
     */
    public static function fillRequestWithRangeVariable(&$range, $variables)
    {
        $new_range['time_zone'] = "";
        if (isset($range['time_zone']))
            $new_range['time_zone'] = $range['time_zone'];
        if ($variables['nbdays'] != '0') {
            $new_range['lte'] = 'now';
            $new_range['gte'] = date("Y-m-d", time() - (84600 * $variables['nbdays']));
            $range = $new_range;
        } else if ($variables['dateStart'] && $variables['dateEnd']) {
            $new_range['gte'] = $variables['dateStart']->format("Y-m-d");
            $new_range['lte'] = $variables['dateEnd']->format("Y-m-d");
            $range = $new_range;
        }
    }


    /**
     * @param $query
     * @param $variables
     */
    public static function fillRequestWithTextVariable(&$query, $variables)
    {
        if (preg_match_all('#(@[\w-]*@)#ui', $query, $matches)) {
            if (count($matches) > 1) {
                $matches = $matches[0];
                foreach ($matches as $match) {
                    $field_name = str_replace("@", '', $match);
                    if (isset($variables[$field_name])) {
                        $query = str_replace($match, $variables[$field_name], $query);
                    } else {
                        $query = str_replace($match, "*", $query);
                    }
                }
            }
        }
    }
}
