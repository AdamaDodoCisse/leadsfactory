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
    public function setFormTemplate() {}
    public function setListTemplate() {}

}