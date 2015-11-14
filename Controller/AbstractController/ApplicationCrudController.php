<?php
namespace Tellaw\LeadsFactoryBundle\Controller\AbstractController;

/**
 * Class ApplicationCrudController
 *
 * Controller used to set application value for generic CRUD controller.
 *
 */
abstract class ApplicationCrudController extends AbstractGenericCrudController {

    public function setEntity() {}
    public function setFormType() {}

    public function setFormTemplate () {
        return "TellawLeadsFactoryBundle:entity/generic:edit.html.twig";
    }

    public function setListTemplate () {
        return "TellawLeadsFactoryBundle:entity/generic:list.html.twig";
    }

}