<?php
namespace Tellaw\LeadsFactoryBundle\Utils\Fields;

abstract class AbstractFieldType {

    /**
     * Generic list of attributes to ignore for tags
     * @var array $attributesToIgnore Defines attributes which should not be copied to html output for the field
     */
    private $attributesToIgnore = array ("type", "id", "validator");

    /**
     * List of attributes to ignore specifics to the current tag
     * @var array $customAttributesToIgnore Defines custom attributes for the current tag to be ignored
     */
    private $customAttributesToIgnore = array();

    /**
     * @var array $validatorList List of default validators that the system can use
     */
    private $validatorList = array ( "notempty" );

    /**
     * @var array $customValidatorList Specific validators for the current field
     */
    private $customValidatorList = array ( "notempty" );

    /**
     * @var Object $_instance Object instance, mustn't be called directly, please use getInstance static method.
     */
    private static $_instance = null;

    protected abstract function createInstance();

    public static function getInstance () {

        if(is_null(self::$_instance)) {
            self::$_instance = createInstance();
        }

        return self::$_instance;

    }

    /**
     * Method used to render to html a field
     * @param Object $tag Tag object
     * @return string Html Content formatted
     */
    public function renderToHtml ( $tag ) {

        $id = $tag["attributes"]["id"];
        return "<input type='text' name='lffield[".$id."]' id='lffield[".$id."]' ".$this->getAttributes( $tag )." />";

    }

    /**
     * Return the list of tags required to be ignored
     * @return array merged lists of attributes to ignore
     */
    protected function getAttributesToIgnore () {
        return array_merge( $this->attributesToIgnore, $this->customAttributesToIgnore );
    }

    /**
     * Return the list of validators available
     * @return array merged list of available validators
     */
    protected function getValidatorsList () {
        return array_merge( $this->validatorList, $this->customValidatorList );
    }


    /**
     * Return true if parameter tag has been declared to ignore list
     * @param string $tag Tag string
     * @return bool true if the tag must be ignored, and false if it should be copied to output
     */
    protected function isAttributeToIgnore ( $tag ) {

        if ( !in_array (strtolower( $tag ), $this->getAttributesToIgnore() ) ) {
            return false;
        } else {
            return true;
        }

    }

    /**
     * Get the attributes list for current tag and return an HTML view.
     * @param array $tag content tag
     * @return string string of attributes to include in the tag
     */
    protected function getAttributes ( $tag ) {

        $htmlAttributes = "";

        foreach ( $tag["attributes"] as $key=>$attribute ) {

            if ( !$this->isAttributeToIgnore( $key ) ) {
                $htmlAttributes .= $key."='".$attribute."' ";
            }

        }

        return trim($htmlAttributes);

    }

    /***
     *
     * Validation method for assert NotEmpty
     * @param string String to test
     * @return bool true if not empty.
     *
     */
    protected function isValidFor_not_empty ( $value ) {

        if (trim($value)!="")
            return true;
        else
            return false;

    }

}