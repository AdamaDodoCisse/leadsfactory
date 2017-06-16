<?php
namespace Tellaw\LeadsFactoryBundle\Utils;

use Doctrine\ORM\Query;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Acl\Exception\Exception;
use Tellaw\LeadsFactoryBundle\DependencyInjection\TimeConfiguratorAwareInterface;
use Tellaw\LeadsFactoryBundle\Entity\Form as FormEntity;
use Tellaw\LeadsFactoryBundle\Entity\ReferenceListRepository;
use Tellaw\LeadsFactoryBundle\Utils\Fields\FieldFactory;


class FormUtils implements TimeConfiguratorAwareInterface, ContainerAwareInterface
{
    /** @var ReferenceListRepository */
    protected $reference_list_repository;

    /** @var Router */
    protected $router;

    /** @var FieldFactory */
    protected $field_factory;

    /** @var \DateTime */
    protected $time;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ReferenceListRepository $reference_list_repository, Router $router, FieldFactory $field_factory)
    {
        $this->reference_list_repository = $reference_list_repository;
        $this->router = $router;
        $this->field_factory = $field_factory;
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }


    public function setTime(\DateTime $time)
    {
        $this->time = $time;
    }

    /**
     * Main method used to generate HTML Form based on the backend input
     *
     * @param FormEntity $form
     * @throws \Symfony\Component\Security\Acl\Exception\Exception
     * @return String HTML of the generated form.
     */
    public function buildHtmlForm(FormEntity $form)
    {
        $html = $form->getSource();

        $tags = $this->parseTags($html);

        foreach ($tags as $id => $tag) {

            $matches = null;

            $htmlTag = $this->renderTag($id, $tag);

            $pattern = "#<field .*id=\"" . $id . "\".*/>#";
            preg_match($pattern, $html, $matches);
            if (!$matches) throw new Exception ("Unable to replace TAG ID : " . $id);

            $html = str_replace($matches[0], $htmlTag, $html);
        }

        $html = $this->setFormTag($html);
        $html = $this->setHiddenTags($form, $html);
        $html = $this->setJsTag($form, $html);

        list ($isValid, $error_msg) = $this->checkFormValidity($html);

        if ($isValid)
            return $html;
        else
            return "form has errors";
    }

    /**
     * Required tags
     *
     * @deprecated
     *
     * @param $source
     * @validator : Type of data validation expected
     * @return array
     */
    public function parseTags($source)
    {

        $xml = simplexml_load_string($source);
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
            if (isset($attributes['validator']))
                $this->_setValidationRules($attributes);

            $items[(string)$result['id']] = array("type" => (string)$result['type'],
                "attributes" => $attributes,
                "raw" => $result);

            //List case
            //if list is slave
            if (isset($attributes['data-parent']) && isset($attributes['data-list'])) {

                $items[(string)$result['id']]['options'] = false;

            } elseif (isset($attributes['data-list'])) {

                $listCode = $attributes['data-list'];
                $options = $this->getElementOptions($listCode);
                $items[(string)$result['id']]['options'] = $options;

            }
        }

        return $items;
    }

    /**
     *
     * Method used to render a field tag
     *
     * @param $id
     * @param $tag
     * @return string
     */
    public function renderTag($id, $tag)
    {
        $type = strtolower($tag['type']);
        $field = $this->field_factory->createFromType($type);

        return $field->renderToHtml($tag);
    }

    /**
     *
     * this should check hidden tags and inputs.
     *
     * @param $source
     * @return array
     */
    public function checkFormValidity($source)
    {
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
        $currentUrl = $this->router->generate("_client_post_form", array(), true);

        $action = $currentUrl;
        $method = "POST";

        $pattern = "/<form (.*)>/";
        $tag = "<form action='" . $action . "' method='" . $method . "' $1>";
        $html = preg_replace($pattern, $tag, $html);

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
        $tags = $this->getHiddenTags($form);
        $tags .= "</form>";
        $html = str_replace("</form>", $tags, $html);

        return $html;
    }

    /**
     * @param FormEntity $form
     *
     * @return string
     */
    public function getHiddenTags($form)
    {
        $tags = "
            <input type='hidden' name=\"lffield[utmcampaign]\" id=\"lffield[utmcampaign]\" value='" . $form->getUtmcampaign() . "'/>
            <input type='hidden' name='lfFormId' id='lfFormId' value='" . $form->getId() . "'/>
            <input type='hidden' name='lfFormKey' id='lfFormKey' value='FORM__KEY'/>
        ";

        if (!is_null($form->getFormType())) {
            $tags .= "<input type='hidden' name='lfFormType' id='lfFormType' value='" . $form->getFormType()->getId() . "'/>";
        }

        return $tags;
    }

    private function setJsTag($form, $html)
    {
        $html .= "\n<script>" . $form->getScript() . "</script>";

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
        $list = $this->reference_list_repository->findOneBy(array('code' => $listCode));
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


        $class = 'input input-' . $attributes['type'];
        if (isset($attributes['class'])) {
            $class = $attributes['class'] . ' ' . $class;
        }

        if (!empty($attributes['data-parent'])) {
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
        $attributes['class'] .= ' validate[' . $attributes['validator'] . ']';
    }

    public function getFormKey($formId, $hourOffset = 0)
    {
        $date = $this->time;

        if ($hourOffset > 0) {
            $date->sub(new \DateInterval('PT' . $hourOffset . 'H'));
        }

        $hour = $date->format("H");
        $day = $date->format("d");
        $month = $date->format("m");
        $year = $date->format("Y");

        $salt = "fac0ry" . $month . $hour . $year . "l3a" . $formId . "ds" . $day;
        $form_key = md5($salt);

        return $form_key;
    }

    public function checkFormKey($md5, $formId)
    {
        if ($md5 == $this->getFormKey($formId)) {
            return true;
        } else if ($md5 == $this->getFormKey($formId, '1')) {
            return true;
        } else {
            return false;
        }

    }

    /**
     *
     * Return data-list ID used in a field of a form.
     *
     * @param $fieldId
     * @param $formId
     * @return null
     */
    public function getUsedReferenceListByFieldId($fieldId, $formId)
    {

        $datas = $this->getFieldsAsArrayByFormId($formId);

        if (isset ($datas[$fieldId])) {

            if (isset ($datas[$fieldId]["attributed"]["data-list"])) {
                return $datas[$fieldId]["attributed"]["data-list"];
            } else {
                return null;
            }

        } else {
            return null;
        }

    }

    /**
     *
     * Method used to extract an array of fields from source with only reference lists elements
     *
     * @param $formId
     * @return array
     */
    public function getReferenceListsFieldsByFormId($formId)
    {

        $datas = $this->getFieldsAsArrayByFormId($formId);
        $fields = array();
        foreach ($datas as $id => $field) {
            if ($field["type"] == "reference-list" || $field["type"] == "linked-reference-list") {
                $fields[$id] = $field;
            }
        }

        return $fields;
    }

    /**
     *
     * Method used to extract all fields from source by form CODE
     *
     * @param $form_code
     * @return array
     */
    public function getFieldsAsArrayByFormCode($form_code)
    {
        $form = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:Form')->findOneByCode($form_code);

        return $this->getFieldsAsArray($form->getSource());
    }

    /**
     *
     * Method used to extract all fields from source by form ID
     *
     * @param $formId
     * @return array
     */
    public function getFieldsAsArrayByFormId($formId)
    {

        $form = $this->container->get('leadsfactory.form_repository')->find($formId);

        $fields = $this->getFieldsAsArray($form->getSource());

        return $fields;

    }

    /**
     *
     * Method used to extract all fields from source
     * This method must be used on TWIG FORMS
     *
     * @param $form_source
     * @return array
     */
    public function getFieldsAsArray($form_source)
    {

        $result = preg_match_all("/field\(([^)]*)\)/", $form_source, $matches);

        $fields = array();

        foreach ($matches[1] as $item) {
            $content = str_replace("'", "\"", $item);
            $item = json_decode($content, true);

            if (isset ($item["attributes"]["id"])) {
                $item = $this->_parseFieldConfig($item);
                $fields[$item["attributes"]["id"]] = $item;
            } else if (isset ($item["attributes"]["name"])) {
                $fields[$item["attributes"]["name"]] = $item;
            }

        }

        return $fields;
    }

    /**
     * Add default field config if not set locally
     *
     * @param array $item
     * @return array
     */
    private function _parseFieldConfig($item)
    {
        $fieldDefaults = $this->container->get('doctrine')->getManager()
            ->getRepository('TellawLeadsFactoryBundle:Field')
            ->findOneByCodeAsArray($item["attributes"]["id"]);

        if (!empty($fieldDefaults)) {
            $fieldDefaults = array_slice($fieldDefaults, 3);
            foreach ($fieldDefaults as $name => $value) {

                if (empty($item['attributes'][$name])) {
                    $item['attributes'][$name] = $value;
                }
            }
        }

        return $item;
    }

    public function getApiKey($form)
    {
        $salt = 'fac0ry_f0rm' . $form->getsecureKey() . '_id_' . $form->getId();

        return md5($salt);
    }

    public function checkApiKey($form, $key)
    {
        return $key == $this->getApiKey($form) ? true : false;
    }

    /**
     * Retourne la valeur frontend d'un champ de lead
     *
     * @param array $field
     * @param string $value
     * @return string
     */
    public function getFieldFrontendValue($field, $value)
    {
        $type = $field['type'];
        if (in_array($type, array('reference-list', 'linked-reference-list'))) {
            $listCode = $field['attributes']['data-list'];
            $value = $this->container->get('leadsfactory.reference_list_element_repository')->getNameUsingListCode($listCode, $value);
        }

        return $value;
    }

    /**
     * Pre process lead data before save
     *
     * @param $formId
     * @param $data
     * @return mixed
     */
    public function preProcessData($formId, $data)
    {
        $fields = $this->getFieldsAsArrayByFormId($formId);
        foreach ($fields as $key => $field) {
            $type = $field['type'];
            if (in_array($type, array('reference-list', 'linked-reference-list'))) {
                $listCode = $field['attributes']['data-list'];
                $data[$key . '_label'] = $this->container->get('leadsfactory.reference_list_element_repository')->getNameUsingListCode($listCode, $data[$key]);
            }
        }

        return $data;
    }

    /**
     * Returns the user's scope forms list options
     *
     * @return array
     */
    public function getUserFormsOptions()
    {
        $forms = $this->container->get('doctrine')->getRepository('TellawLeadsFactoryBundle:Form')->getForms();
        $options = array('' => 'Sélectionnez un formulaire');
        $user_scope = $this->container->get('security.context')->getToken()->getUser()->getScope();
        foreach ($forms as $form) {
            if ($user_scope && $form->getscope() != $user_scope) {
                continue;
            }
            $options[$form->getId()] = $form->getName();
        }

        return $options;
    }
}
