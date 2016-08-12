<?php
namespace Tellaw\LeadsFactoryBundle\Controller\AbstractController;

/**
 * Class ApplicationCrudController
 *
 * Controller used to set application value for generic CRUD controller.
 *
 */
abstract class ApplicationCrudController extends AbstractGenericCrudController {

    protected function setEntity() {}
    protected function setFormType() {}

    protected function setFormTemplate () {
        return "TellawLeadsFactoryBundle:entity/generic:edit.html.twig";
    }

    protected function setListTemplate () {
        return "TellawLeadsFactoryBundle:entity/generic:list.html.twig";
    }

}