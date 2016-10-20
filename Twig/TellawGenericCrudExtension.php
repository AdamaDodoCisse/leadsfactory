<?php
namespace Tellaw\LeadsFactoryBundle\Twig;

use Tellaw\LeadsFactoryBundle\Entity\ReferenceListRepository;
use Tellaw\LeadsFactoryBundle\Utils\FormUtils;

class TellawGenericCrudExtension extends \Twig_Extension
{
    /** @var  FormUtils */
    private $form_helper;

    /** @var  ReferenceListRepository */
    private $reference_list_repository;

    public function __construct()
    {

    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('getActionsLinks', array($this, 'getActionsLinks'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('getListItem', array($this, 'getListItem'), array('is_safe' => array('html'))),
        );
    }

    /**
     * Method used to extract links of actions
     * @param $params
     */
    public function getActionsLinks($params)
    {

    }

    /**
     * Method used to extract in List views the values of object attributes
     * @param $params
     */
    public function getListItem($params)
    {
        $object = $params["object"];

        $column = $params["column"];


        if (is_array($column)) {

            if (!array_key_exists("key", $column)) {
                return "";
            } else {

                $key = $params["column"]["key"];
                $value = $this->getobjectValue($object, $key);

            }

            if (array_key_exists("method", $column)) {
                try {
                    $method = $params["method"];
                    echo(call_user_func(array($object, $method), $value));
                } catch (\Exception $e) {
                    echo($e->getMessage());
                }
            } else if (array_key_exists("dateformat", $column)) {

                try {
                    echo($value->format($params["format"]));
                } catch (\Exception $e) {
                    echo($e->getMessage());
                }

            } else {
                echo($value);
            }


        } else {

            $key = $params["column"];
            echo($this->getobjectValue($object, $key));

        }

    }

    private function getobjectValue($object, $key)
    {


        if (strstr($key, ".")) {

            $methods = explode(".", $key);
            $lastObj = $object;
            for ($a = 0; $a < count($methods); $a++) {
                $method = $methods[$a];
                $obj = "get" . ucfirst($method);
                if ($lastObj != null) {
                    $lastObj = call_user_func(array($lastObj, $obj));
                }
            }

            return $lastObj;
        } else {
            try {
                $column = "get" . ucfirst($key);

                return call_user_func(array($object, $column));
            } catch (\Exception $e) {

            }
        }

    }

    public function getName()
    {
        return 'genericcrud_extension';
    }
}
