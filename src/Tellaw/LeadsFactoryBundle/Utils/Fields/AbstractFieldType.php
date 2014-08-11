<?php
namespace Tellaw\LeadsFactoryBundle\Utils;

abstract class AbstractFieldType {

    // Generic list of attributes to ignore for tags
    private $attributesToIgnore = array ("type", "id", "validator");

    // List to override in implementations
    private $customAttributesToIgnore = array();

    // List of validators usable by the system
    private $validatorList = array ( "notempty" );

    // List of custom tag validators
    private $customValidatorList = array ( "notempty" );

    /**
     * Method used to render to html a field
     * @param $tag
     * @return String $html
     */
    public function renderToHtml ( $tag ) {

        $id = $tag["attributes"]["id"];
        return "<input type='text' name='lffield[".$id."]' id='lffield[".$id."]' ".$this->getAttributes( $tag )." />";

    }

    /**
     * Return the list of tags required to be ignored
     * @return array
     */
    protected function getAttributesToIgnore () {
        return array_merge( $this->attributesToIgnore, $this->customAttributesToIgnore );
    }

    /**
     * Return the list of validators available
     * @return array
     */
    protected function getValidatorsList () {
        return array_merge( $this->validatorList, $this->customValidatorList );
    }


    /**
     * Return true if parameter tag has been declared to ignore list
     * @param $tag
     * @return bool
     */
    protected function isAttributeToIgnore ( $tag ) {

        if ( !in_array (strtolower( $tag ), $this->getAttributesToIgnore() ) ) {
            return false;
        } else {
            return true;
        }

    }

    /**
     * get Attributes list for current tag and return an HTML view.
     * @param $tag
     * @return string
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
     * Here start validators usable by multiple tags.
     *
     */
    protected function isValidFor_not_empty ( $value ) {

        if (trim($value)!="")
            return true;
        else
            return false;

    }

}