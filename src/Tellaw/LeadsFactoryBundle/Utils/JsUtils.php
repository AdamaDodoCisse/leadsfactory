<?
namespace Tellaw\LeadsFactoryBundle\Utils;

use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Utils\Fields\EmailFieldType;
use Tellaw\LeadsFactoryBundle\Utils\Fields\TextFieldType;
use Tellaw\LeadsFactoryBundle\Utils\Fields\ReferenceListFieldType;

class JsUtils {

    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    private $container;

    public function setContainer (\Symfony\Component\DependencyInjection\ContainerInterface $container) {
        $this->container = $container;
    }


    public function buildAndWrapForm ( $source, $formId, $formObject ) {

        $formUtils = $this->container->get ("form_utils");
        $formHtml = $formUtils->buildHtmlForm ( $source, $formId, $formObject );

        $jsForm = $this->wrapHtml( $formHtml );

        $jsForm .= $this->generateGetterAndSetters( $source );

        return $jsForm;

    }

    private function wrapHtml ( $html ) {

        $jsWrapOfForm = str_replace ("\"", "\\\"", $html);
        $jsWrapOfForm = str_replace("\t", '', $jsWrapOfForm); // remove tabs
        $jsWrapOfForm = str_replace("\n", '', $jsWrapOfForm); // remove new lines
        $jsWrapOfForm = str_replace("\r", '', $jsWrapOfForm); // remove carriage returns
        $jsWrap = "var frmObj=\"".$jsWrapOfForm."\";function displayFrm () {document.writeln(frmObj);}";

        return $jsWrap;

    }

    private function generateGetterAndSetters ( $source ) {

        $tags = $this->container->get ("form_utils")->parseTags( $source );

        $gettersAndSetters = "";

        foreach ($tags as $id => $tag) {
            $gettersAndSetters .= $this->buildGetterAndSetterForId( $id );
        }

        return $gettersAndSetters;

    }

    private function buildGetterAndSetterForId ( $id ) {
        return "function setLf".ucfirst($id)."(value){document.getElementById(\"lffield[".$id."]\").value=value;}function getLf".ucfirst($id)."(){return document.getElementById(\"lffield[".$id."]\").value;}";
    }

}