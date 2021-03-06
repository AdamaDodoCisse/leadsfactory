<?php
/**
 * Created by PhpStorm.
 * User: tellaw
 * Date: 20/06/15
 * Time: 07:59
 */

namespace Tellaw\LeadsFactoryBundle\Shared;

use Tellaw\LeadsFactoryBundle\Entity\Form as FormEntity;
use Tellaw\LeadsFactoryBundle\Utils\StringHelper;

class JsUtilsShared
{

    public function buildAndWrapForm(FormEntity $formObject)
    {
        /** @var \Tellaw\LeadsFactoryBundle\Utils\FormUtils $formUtils */
        $formUtils = $this->container->get("form_utils");
        $formHtml = $formUtils->buildHtmlForm($formObject);

        $jsForm = $this->wrapHtml($formHtml);

        $jsForm .= $this->generateGetterAndSetters($formObject->getSource());

        return $jsForm;
    }

    private function wrapHtml($html)
    {

        $jsWrapOfForm = str_replace("\"", "\\\"", $html);
        $jsWrapOfForm = str_replace("\t", '', $jsWrapOfForm); // remove tabs
        $jsWrapOfForm = str_replace("\n", '', $jsWrapOfForm); // remove new lines
        $jsWrapOfForm = str_replace("\r", '', $jsWrapOfForm); // remove carriage returns
        $jsWrap = "var leadsfactory = new Object();\r\nleadsfactory.render= function() { var frmObj=\"" . $jsWrapOfForm . "\";document.writeln(frmObj);};\r\n";

        return $jsWrap;

    }

    private function generateGetterAndSetters($source)
    {

        $tags = $this->container->get("form_utils")->parseTags($source);

        $gettersAndSetters = "";

        foreach ($tags as $id => $tag) {
            $gettersAndSetters .= $this->buildGetterAndSetterForId($id);
        }

        return $gettersAndSetters;

    }

    private function buildGetterAndSetterForId($id)
    {
        $camelId = StringHelper::camelize($id);

        return "leadsfactory.set" . $camelId . "= function (value){document.getElementById(\"lffield[" . $id . "]\").value=value;};\r\nleadsfactory.get" . $camelId . " = function(){return document.getElementById(\"lffield[" . $id . "]\").value;};\r\n";
    }

}
