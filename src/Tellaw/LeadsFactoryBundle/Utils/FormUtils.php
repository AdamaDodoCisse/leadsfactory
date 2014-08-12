<?
namespace Tellaw\LeadsFactoryBundle\Utils;

use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Utils\Fields\EmailFieldType;
use Tellaw\LeadsFactoryBundle\Utils\Fields\TextFieldType;
use Tellaw\LeadsFactoryBundle\Utils\Fields\ReferenceListFieldType;

class FormUtils {

    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    private $container;

    public function setContainer (\Symfony\Component\DependencyInjection\ContainerInterface $container) {
        $this->container = $container;
    }

    /**
     *
     * Main method used to generate HTML Form based on the backend input
     *
     * @param $source Sources of the form to generate
     * @param $formId Id of the form in LF backend
     * @return String HTML of the generated form.
     */
    public function buildHtmlForm ( $source, $formId ) {

        $tags = $this->parseTags( $source );

        foreach ($tags as $id => $tag) {

            print_r ($tags);

            $htmlTag = $this->renderTag( $id, $tag );
            $source = str_replace( $tag["raw"]->asXML(), $htmlTag, $source );
        }

        $source = $this->setFormTag ( $source, $formId );
        $source = $this->setHiddenTags ( $source, $formId );

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

            $items[(string)$result['id']] = array ( "type"=>(string)$result['type'],
                                                    "attributes" => $attributes,
                                                    "raw" => $result);

        }

        return $items;

    }

    public function renderTag( $id, $tag ) {

        $type = $tag["type"];

        echo ("Tag Detected : ".$type);

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
    private function setHiddenTags ( $source, $formId ) {

        $tags="
            <input type='hidden' name='lfFormId' id='lfFormId' value='".$formId."'/>
            <input type='hidden' name='lfForward' id='lfForward' />
            <input type='hidden' name='lfFormKey' id='lfFormKey' value=''/>
            </form>
        ";

        $source = str_replace ( "</form>", $tags, $source );

        return $source;

    }

}