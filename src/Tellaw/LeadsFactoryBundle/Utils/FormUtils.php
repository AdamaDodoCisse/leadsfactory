<?php
namespace Tellaw\LeadsFactoryBundle\Utils;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Acl\Exception\Exception;
use Tellaw\LeadsFactoryBundle\Utils\Fields\CheckboxFieldType;
use Tellaw\LeadsFactoryBundle\Utils\Fields\EmailFieldType;
use Tellaw\LeadsFactoryBundle\Utils\Fields\HiddenFieldType;
use Tellaw\LeadsFactoryBundle\Utils\Fields\LinkedReferenceListFieldType;
use Tellaw\LeadsFactoryBundle\Utils\Fields\TextareaFieldType;
use Tellaw\LeadsFactoryBundle\Utils\Fields\TextFieldType;
use Tellaw\LeadsFactoryBundle\Utils\Fields\ReferenceListFieldType;
use Tellaw\LeadsFactoryBundle\Entity\Form as FormEntity;

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
     * Main method used to generate HTML Form based on the backend input
     *
     * @param FormEntity $form
     * @throws \Symfony\Component\Security\Acl\Exception\Exception
     * @return String HTML of the generated form.
     */
    public function buildHtmlForm (FormEntity $form)
    {
        $html = $form->getSource();

        $tags = $this->parseTags($html);

        foreach ($tags as $id => $tag) {

            $matches = null;

            $htmlTag = $this->renderTag( $id, $tag );

            $pattern = "#<field .*id=\"".$id."\".*/>#";
            preg_match ( $pattern, $html, $matches );
            if ( !$matches ) throw new Exception ("Unable to replace TAG ID : ".$id);

            $html = str_replace( $matches[0], $htmlTag, $html );
        }

        $html = $this->setFormTag ($html);
        $html = $this->setHiddenTags ($form, $html);

        list ($isValid, $error_msg) = $this->checkFormValidity( $html );

        if ( $isValid )
            return $html;
        else
            return "form has errors";
    }

    /**
     * Required tags
     *
     * @param $source
     * @validator : Type of data validation expected
     * @return array
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

            //List case
            //if list is slave
            if(isset($attributes['data-parent']) && isset($attributes['data-list'])){

                $items[(string)$result['id']]['options'] = false;

            }elseif(isset($attributes['data-list'])){

                $listCode = $attributes['data-list'];
                $options = $this->getElementOptions($listCode);
                $items[(string)$result['id']]['options'] = $options;

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
            case "textarea":
                $fieldType = TextareaFieldType::getInstance();
                break;
            case "checkbox":
                $fieldType = CheckboxFieldType::getInstance();
                break;
            case "linked-reference-list":
                $fieldType = LinkedReferenceListFieldType::getInstance();
                break;
            case "hidden":
                $fieldType = HiddenFieldType::getInstance();
                break;
            default:
                $fieldType = TextFieldType::getInstance();

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
     * Method used to replace the form tag
     *
     * @param string $html
     * @return String modified form including the form tag
     */
    private function setFormTag($html)
    {
        $currentUrl = $this->container->get('router')->generate("_client_post_form", array(), true);

        $action = $currentUrl;
        $method = "POST";

        $pattern = "/<form (.*)>/";
        $tag = "<form action='".$action."' method='".$method."' $1>";
        $html = preg_replace ( $pattern, $tag, $html );

        return $html;
    }

    /**
     * Method used to add hidden tags, used to save informations of context to LF
     *
     * @param $html Source of the form to generate
     * @param FormEntity $form
     * @return String modified form including the form tag
     */
    private function setHiddenTags($form, $html)
    {
        $tags="
            <input type='hidden' name=\"lffield[utmcampaign]\" id=\"lffield[utmcampaign]\" value='".$form->getUtmcampaign()."'/>
            <input type='hidden' name='lfFormId' id='lfFormId' value='".$form->getId()."'/>
            <input type='hidden' name='lfFormType' id='lfFormType' value='".$form->getFormType()->getId()."'/>
            <input type='hidden' name='lfFormKey' id='lfFormKey' value='".$this->getFormKey($form->getId())."'/>
            </form>
        ";

        $html = str_replace ( "</form>", $tags, $html );
        return $html;
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
     * Set Attribute class. Merge default and user defined values
     *
     * @param array $attributes
     */
    private function _setClassAttribute(&$attributes)
    {


        $class = 'input input-'.$attributes['type'];
        if(isset($attributes['class'])){
            $class = $attributes['class'] . ' '. $class;
        }

        if(!empty($attributes['data-parent'])){
            $class = 'child-list ' . $class;
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

    public function getFormKey ($formId, $hourOffset = 0) {

        $date = date_create();

        if ( $hourOffset > 0 ) {
            $date->add ( new \DateInterval('P'.$hourOffset.'H') );
        }

        $hour   = $date->format ("H");
        $day    = $date->format ("d");
        $month  = $date->format ("m");
        $year   = $date->format ("Y");

        $salt = "fac0ry".$month.$hour.$year."l3a".$formId."ds".$day;
        return md5 ( $salt );

    }

    public function checkFormKey ( $md5, $formId ) {

        if ($md5 == $this->getFormKey( $formId )) {
            return true;
        } else if ($md5 == $this->getFormKey( $formId, '-1' )) {
            return true;
        } else
            return false;

    }

}
