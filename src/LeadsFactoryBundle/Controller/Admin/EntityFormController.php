<?php

namespace LeadsFactoryBundle\Controller\Admin;

use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use LeadsFactoryBundle\Entity\Form;
use LeadsFactoryBundle\Form\Type\FormType;
use LeadsFactoryBundle\Shared\CoreController;
use LeadsFactoryBundle\Utils\FunctionnalTestingUtils;

/**
 * @Route("/entity")
 */
class EntityFormController extends CoreController
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     *
     * @Route("/form/list/{page}/{limit}/{keyword}", name="_form_list")
     * @Secure(roles="ROLE_USER")
     *
     */
    public function indexAction($page = 1, $limit = 10, $keyword = '')
    {

        if ($this->get("core_manager")->isDomainAccepted()) {
            return $this->redirect($this->generateUrl('_security_licence_error'));
        }

        $list = $this->getList('TellawLeadsFactoryBundle:Form', $page, $limit, $keyword, array('user' => $this->getUser()));
        $bookmarks = $this->get('leadsfactory.form_repository')->getBookmarkedFormsForUser($this->getUser()->getId());

        $formatedBookmarks = array();
        foreach ($bookmarks as $bookmark) {
            $formatedBookmarks[$bookmark->getForm()->getId()] = true;
        }

        return $this->render(
            'TellawLeadsFactoryBundle:entity/Form:entity_list.html.twig',
            array(
                'elements' => $list['collection'],
                'pagination' => $list['pagination'],
                'limit_options' => $list['limit_options'],
                'bookmarks' => $formatedBookmarks
            )
        );

    }

    /**
     * @Route("/form/new", name="_form_new")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function newAction(Request $request)
    {
        $form = $this->createForm(
            new FormType(),
            null,
            array('method' => 'POST')
        );

        $formEntity = new Form();

        $form->handleRequest($request);
        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();

            return $this->redirect($this->generateUrl('_form_list'));
        }

        return $this->render(
            'TellawLeadsFactoryBundle:entity/Form:entity_edit.html.twig',
            array(
                'form' => $form->createView(),
                'formObj' => $formEntity,
                'title' => "Création d'un formulaire",
                'screenofResult' => null,
                'screenofForm' => null
            )
        );
    }


    /**
     * Preview of a TWIG FORM
     *
     * @Route("/preview/twig/{code}", name="_client_twig_preview")
     * @ParamConverter("form")
     */
    public function getTwigFormPreviewAction(Form $form)
    {
        return $this->render(
            'TellawLeadsFactoryBundle:Front:display_twig_form.html.twig',
            array('form' => $form)
        );
    }

    /**
     * @Route("/form/edit/{id}", name="_form_edit")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function editAction(Request $request, $id)
    {
        $testUtils = $this->get("functionnal_testing.utils");
        $testUtils->setIsWebMode(true);

        $formEntity = $this->get('leadsfactory.form_repository')->find($id);

        $form = $this->createForm(
            new FormType(),
            $formEntity,
            array('method' => 'POST')
        );

        $form->handleRequest($request);
        if ($form->isValid()) {

            $cacheFileName = "../app/cache/templates/" . $id . ".js";
            if (file_exists($cacheFileName)) {
                unlink($cacheFileName);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();
        }

        // Get screenshots
        $screenofForm = $testUtils->getScreenPathOfForm($formEntity, true);
        $screenofResult = $testUtils->getScreenPathOfResult($formEntity, true);

        return $this->render(
            'TellawLeadsFactoryBundle:entity/Form:entity_edit.html.twig',
            array(
                'id' => $id,
                'funtionnalTestEnabled' => $testUtils->isFormTestable($formEntity),
                'code' => $formEntity->getCode(),
                'formObj' => $formEntity,
                'form' => $form->createView(),
                'title' => "Edition d'un formulaire",
                'screenofResult' => $screenofResult,
                'screenofForm' => $screenofForm
            )
        );
    }

    /**
     * @Route("/form/delete/id/{id}", name="_form_delete")
     * @Secure(roles="ROLE_USER")
     * @Method("GET")
     * @Template()
     */
    public function deleteAction($id)
    {

        /**
         * This is the deletion action
         */
        $object = $this->get('leadsfactory.form_repository')->find($id);

        $cacheFileName = "../app/cache/templates/" . $id . ".js";
        if (file_exists($cacheFileName)) {
            unlink($cacheFileName);
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($object);
        $em->flush();

        return $this->redirect($this->generateUrl('_form_list'));

    }

    /**
     * @Route("/form/duplicate/id/{id}", name="_form_duplicate")
     * @Secure(roles="ROLE_USER")
     * @Method("GET")
     * @Template()
     */
    public function duplicateAction($id)
    {
        $old = $this->get('leadsfactory.form_repository')->find($id);

        $em = $this->getDoctrine()->getManager();
        $new = clone $old;
        $new->setCode('');
        $em->persist($new);
        $em->flush();

        return $this->redirect($this->generateUrl('_form_edit', array('id' => $new->getId())));
    }

    /**
     * Method used to run test
     *
     * @Route("/runtest/{id}/{step}", name="_form_runtest")
     * @Secure(roles="ROLE_USER")
     * @Method("GET")
     * @Template()
     */
    public function runtestAction($id, $step = 1)
    {

        $testUtils = $this->get("functionnal_testing.utils");

        $form = $this->get('leadsfactory.form_repository')->find($id);
        $logger = $this->get('logger');
        $testUtils->setLogger($logger);
        $testUtils->setIsWebMode(true);

        $formId = $form->getConfig();
        if (isset($formId ["configuration"]["functionnalTestingEnabled"]) && $formId ["configuration"]["functionnalTestingEnabled"] == true) {

            switch ($step) {
                case FunctionnalTestingUtils::$_STEP_1_CREATE_CASPER_SCRIPT:
                    echo("<h2>Etape 1/4 : Création de script de test</h2>");
                    \flush();
                    $status = $testUtils->runByStep(FunctionnalTestingUtils::$_STEP_1_CREATE_CASPER_SCRIPT, $form);
                    if (!$status) {
                        throw new \Exception ("Unable to create Casper Script");
                    }
                    echo("Script créé avec succès");

                    return $this->redirectToRoute('_form_runtest', array("id" => $id, "step" => FunctionnalTestingUtils::$_STEP_2_EXECUTE_CASPER_SCRIPT));
                    break;

                case FunctionnalTestingUtils::$_STEP_2_EXECUTE_CASPER_SCRIPT:
                    echo("<h2>Etape 2/4 : debut du test fonctionnel</h2>");
                    \flush();
                    list ($status, $log) = $testUtils->runByStep(FunctionnalTestingUtils::$_STEP_2_EXECUTE_CASPER_SCRIPT, $form);
                    $this->get("session")->set("functionnalTestingStatus", $status);
                    $this->get("session")->set("functionnalTestingLog", $log);
                    echo("Test terminé");

                    return $this->redirectToRoute('_form_runtest', array("id" => $id, "step" => FunctionnalTestingUtils::$_STEP_3_EVALUATE_LEADS));
                    break;

                case FunctionnalTestingUtils::$_STEP_3_EVALUATE_LEADS:
                    echo("<h2>Etape 3/4 : Validation des données en base</h2>");
                    \flush();
                    $status = $this->get("session")->get("functionnalTestingStatus");
                    $log = $this->get("session")->get("functionnalTestingLog");
                    $statusOfTest = $testUtils->runByStep(FunctionnalTestingUtils::$_STEP_3_EVALUATE_LEADS, $form, $status, $log);
                    $this->get("session")->set("functionnalTestingStatusOfTest", $statusOfTest);
                    echo("Fin de validation");

                    return $this->redirectToRoute('_form_runtest', array("id" => $id, "step" => FunctionnalTestingUtils::$_STEP_4_PERSIST_RESULTS));
                    break;

                case FunctionnalTestingUtils::$_STEP_4_PERSIST_RESULTS:
                    echo("<h2>Etape 4/4 : Enregistrement des résultats</h2>");
                    \flush();
                    $status = $this->get("session")->get("functionnalTestingStatus");
                    $log = $this->get("session")->get("functionnalTestingLog");
                    $statusOfTest = $this->get("session")->get("functionnalTestingStatusOfTest");

                    $testUtils->runByStep(FunctionnalTestingUtils::$_STEP_4_PERSIST_RESULTS, $form, $status, $log, $statusOfTest);
                    break;

            }

            $logger->info("Traitement de la page de test : " . $form->getUrl());
            $testUtils->run($form);

        } else {
            $logger->info("Le formulaire n'est pas configuré pour réaliser les tests fonctionnels");
        }

        return $this->render(
            'TellawLeadsFactoryBundle:entity/Form:functionnal-test.html.twig',
            array(
                'id' => $id,
                'formObj' => $form
            )
        );
    }

}
