<?php
namespace Tellaw\LeadsFactoryBundle\Utils;

use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Utils\Fields\EmailFieldType;
use Tellaw\LeadsFactoryBundle\Utils\Fields\TextFieldType;
use Tellaw\LeadsFactoryBundle\Utils\Fields\ReferenceListFieldType;
use Tellaw\LeadsFactoryBundle\Entity\Form as FormEntity;

class JsUtils {

    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    private $container;

    public function setContainer (\Symfony\Component\DependencyInjection\ContainerInterface $container) {
        $this->container = $container;
    }


    public function buildAndWrapForm(FormEntity $formObject)
    {
        /** @var \Tellaw\LeadsFactoryBundle\Utils\FormUtils $formUtils */
        $formUtils = $this->container->get ("form_utils");
        $formHtml = $formUtils->buildHtmlForm ( $formObject );

        $jsForm = $this->wrapHtml( $formHtml );

        $jsForm .= $this->generateGetterAndSetters( $formObject->getSource() );

        return $jsForm;
    }

    private function wrapHtml ( $html ) {

        $jsWrapOfForm = str_replace ("\"", "\\\"", $html);
        $jsWrapOfForm = str_replace("\t", '', $jsWrapOfForm); // remove tabs
        $jsWrapOfForm = str_replace("\n", '', $jsWrapOfForm); // remove new lines
        $jsWrapOfForm = str_replace("\r", '', $jsWrapOfForm); // remove carriage returns
        $jsWrap = "var leadsfactory = new Object();\r\nleadsfactory.render= function() { var frmObj=\"".$jsWrapOfForm."\";document.writeln(frmObj);};\r\n";

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
        $camelId = StringHelper::camelize($id);
        return "leadsfactory.set".$camelId."= function (value){document.getElementById(\"lffield[".$id."]\").value=value;};\r\nleadsfactory.get".$camelId." = function(){return document.getElementById(\"lffield[".$id."]\").value;};\r\n";
    }

}
