<?php
namespace Tellaw\LeadsFactoryBundle\Utils;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Utils\Fields\EmailFieldType;
use Tellaw\LeadsFactoryBundle\Utils\Fields\TextFieldType;
use Tellaw\LeadsFactoryBundle\Utils\Fields\ReferenceListFieldType;

class FormUtils {

    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer (ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->container;
    }

    /**
     *
     * Main method used to generate HTML Form based on the backend input
     *
     * @param $source Sources of the form to generate
     * @param $formId Id of the form in LF backend
     * @return String HTML of the generated form.
     */
    public function buildHtmlForm ( $source, $formId, $formObject ) {

        $tags = $this->parseTags( $source );

        foreach ($tags as $id => $tag) {

            //print_r ($tags);

            $htmlTag = $this->renderTag( $id, $tag );
            $source = str_replace( $tag["raw"]->asXML(), $htmlTag, $source );
        }

        $source = $this->setFormTag ( $source, $formId );
        $source = $this->setHiddenTags ( $source, $formId, $formObject );

        list ($isValid, $error_msg) = $this->checkFormValidity( $source );

        if ( $isValid )
            return $source;
        else
            return "form has errors";
    }

    /**
     *
     * Required tags
     * @id : Unique Id for a field in a form
     * @type : Type of data expected for the field
     * @validator : Type of data validation expected
     *
     */
    public function parseTags ( $source ) {

        $xml = simplexml_load_string( $source );
        $results = $xml->xpath('//field');

        $items = array();
        foreach ($results as $result) {

            $attributes = array();
            foreach ($result->attributes() as $attribute => $value) {
                $attributes[$attribute] = (string)$value;
            }

            //Class attribute
            $this->_setClassAttribute($attributes);

            //Add validation rules if needed
            if(isset($attributes['validator']))
                $this->_setValidationRules($attributes);

            $items[(string)$result['id']] = array ( "type"=>(string)$result['type'],
                                                    "attributes" => $attributes,
                                                    "raw" => $result);
            //if element has options
            if(isset($result->attributes()['data-list'])){
                $listCode = $result->attributes()['data-list']->__toString();
                $options = $this->getElementOptions($listCode);
                $items[(string)$result['id']]['options'] = $options;
            }

            //if validation is needed
            if(isset($result->attributes()['data-list'])){

            }
        }
        return $items;
    }

    public function renderTag( $id, $tag ) {

        $type = $tag["type"];

        //echo ("Tag Detected : ".$type);

        $type = strtolower($type);

        $fieldType = null;
        switch ($type) {
            case "email":
                $fieldType = EmailFieldType::getInstance();
                break;
            case "text":
                $fieldType = TextFieldType::getInstance();
                break;
            case "reference-list":
                $fieldType = ReferenceListFieldType::getInstance();
                break;
        }

        return $fieldType->renderToHtml ( $tag );

    }

    /**
     *
     * Todo : Method to implement to ensure the correct creation of the form.
     * this should check hidden tags and inputs.
     *
     * @param $source
     * @return array
     */
    public function checkFormValidity( $source ) {
        return array(true, null);
    }

    /**
     *
     * Method used to replace the form tag
     *
     * @param $source Source of the form to generate
     * @param $formId Id of the form in the LF
     * @return String modified form including the form tag
     */
    private function setFormTag ( $source, $formId ) {

        $request = Request::createFromGlobals();
        $currentUrl = $this->container->get('router')->generate("_client_post_form", array(), true);

        $action = $currentUrl;
        $method = "POST";

        $tag = "<form action='".$action."' method='".$method."'>";

        $source = str_replace ("<form>", $tag, $source);

        return $source;

    }

    /**
     *
     * Method used to add hidden tags, used to save informations of context to LF
     *
     * @param $source Source of the form to generate
     * @param $formId Id of the form in the LF
     * @return String modified form including the form tag
     */
    private function setHiddenTags ( $source, $formId, $formObject ) {

        $tags="
            <input type='hidden' name='lfFormId' id='lfFormId' value='".$formId."'/>
            <input type='hidden' name='lfForwardSuccess' id='lfForwardSuccess' value='' />
            <input type='hidden' name='lfForwardError' id='lfForwardError' value='' />
            <input type='hidden' name='lfFormType' id='lfFormType' value='".$formObject->getFormType()->getId()."'/>
            <input type='hidden' name='lfFormKey' id='lfFormKey' value=''/>
            </form>
        ";

        $source = str_replace ( "</form>", $tags, $source );

        return $source;

    }

    /**
     * Retrieve element options
     *
     * @param string $listCode
     * @return array mixed
     */
    public function getElementOptions($listCode)
    {
        $list = $this->getContainer()->get('doctrine')->getRepository('TellawLeadsFactoryBundle:ReferenceList')->findOneBy(array('code' => $listCode));
        $options = $list->getElements()->getValues();
        return $options;
    }

    /**
     * Set Attribute class. Merge default and user defined value
     *
     * @param array $attributes
     */
    private function _setClassAttribute(&$attributes)
    {
        $class = 'input input-'.$attributes['type'];
        if(isset($attributes['class'])){
            $class = $attributes['class'] . ' '. $class;
        }
        $attributes['class'] = $class;
    }

    /**
     * Add validation rules to class attributes
     * @see https://github.com/posabsolute/jQuery-Validation-Engine
     *
     * @param array $attributes
     */
    private function _setValidationRules(&$attributes)
    {
        $attributes['class'] .= ' validate['.$attributes['validator'].']';
    }

}
