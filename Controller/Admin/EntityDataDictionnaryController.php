<?php

namespace Tellaw\LeadsFactoryBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Entity\DataDictionnaryElement;
use Tellaw\LeadsFactoryBundle\Entity\ReferenceList;
use Tellaw\LeadsFactoryBundle\Form\Type\DataDictionnaryType;
use Tellaw\LeadsFactoryBundle\Shared\CoreController;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Tellaw\LeadsFactoryBundle\Utils\Messages;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/entity")
 * @Cache(expires="tomorrow")
 */
class EntityDataDictionnaryController extends CoreController
{

    public function __construct () {
        parent::__construct();
    }

    /**
     *
     * @Route("/dataDictionnary/list/{page}/{limit}/{keyword}", name="_dataDictionnary_list")
     * @Secure(roles="ROLE_USER")
     *
     */
    public function indexAction($page=1, $limit=10, $keyword='')
    {

        if ($this->get("core_manager")->isDomainAccepted ()) {
            return $this->redirect($this->generateUrl('_security_licence_error'));
        }

        $list = $this->getList ('TellawLeadsFactoryBundle:DataDictionnary', $page, $limit, $keyword, array ('user'=>$this->getUser()) );
        //$forms = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:ReferenceList')->findAll();

        return $this->render(
            'TellawLeadsFactoryBundle:entity/DataDictionnary:entity_referenceList_list.html.twig',
            array(
                'elements'      => $list['collection'],
                'pagination'    => $list['pagination'],
                'limit_options' => $list['limit_options']
            )
        );

    }

    /**
     * @Route("/dataDictionnary/new", name="_dataDictionnary_new")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function newAction( Request $request )
    {

        $type = new DataDictionnaryType();

        $form = $this->createForm(  $type,
            null,
            array(
                'method' => 'POST'
            )
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            // fait quelque chose comme sauvegarder la tâche dans la bdd

            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();

            return $this->redirect($this->generateUrl('_dataDictionnary_list'));
        }

        return $this->render('TellawLeadsFactoryBundle:entity/DataDictionnary:entity_referenceList_new.html.twig', array(  'form' => $form->createView(),
                                                                                                    'title' => "Création d'un dictionnaire",
                                                                                                    'refernceListId' => '-1'));
    }

    /**
     * @Route("/dataDictionnary/buildfile/id/{id}", name="_dataDictionnary_build_file")
     * @Secure(roles="ROLE_USER")
     * @Method("GET")
     */
    public function buildfileAction ( $id ) {

        $fileName = '../datas/json-lists/'.$id.'.json';
        $fs = new Filesystem();
        $formData = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:DataDictionnary')->find($id);
        $referingLists = $this->get("lf.utils")->getListOfLists( $id );
        $json = $formData->getJson($referingLists);

        $fs->dumpFile( $fileName , $json);

        $messagesUtils = $this->container->get("messages.utils");
        $messagesUtils->pushMessage( Messages::$_TYPE_SUCCESS, "Liste de référence", "Le fichier à été généré avec succès : ".$fileName );

        return new Response("ok",200,array('Content-Type'=>'text/plain'));

    }

    /**
     * @Route("/dataDictionnary/edit/{id}", name="_dataDictionnary_edit")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function editAction( Request $request, $id )
    {

        /**
         * This is the new / editing action
         */

        // crée une tâche et lui donne quelques données par défaut pour cet exemple
        $formData = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:DataDictionnary')->find($id);

        $type = new DataDictionnaryType();

        $form = $this->createForm(  $type,
                                    $formData,
                                    array(
                                        'method' => 'POST'
                                    )
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();

            return $this->redirect($this->generateUrl('_dataDictionnary_list'));
        }

        $formData->getElements();

        return $this->render('TellawLeadsFactoryBundle:entity/DataDictionnary:entity_referenceList_edit.html.twig',
                                array(  'form' => $form->createView(),
                                        'elements'=> $formData->getElements(),
                                        'title' => "Edition d'un dictionnaire",
                                        'referenceListId' => $id));

    }

    /**
     * @Route("/dataDictionnary/delete/id/{id}", name="_dataDictionnary_delete")
     * @Secure(roles="ROLE_USER")
     * @Method("GET")
     * @Template()
     */
    public function deleteAction ( $id ) {

        /**
         * This is the deletion action
         */
        $object = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:DataDictionnary')->find($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($object);
        $em->flush();

        return $this->redirect($this->generateUrl('_dataDictionnary_list'));

    }

    private function saveJsonFeed ( $json ) {

        $elements = json_decode ( $json, true );

        foreach ($elements as $element) {

        }

    }

    /**
     * @Route("/dataDictionnary/deleteElement/id/{id}/{dataDictionnaryId}", name="_dataDictionnary_deleteElement")
     * @Secure(roles="ROLE_USER")
     * @Method("GET")
     * @Template()
     */
    public function deleteElementAction ( Request $request, $id, $dataDictionnaryId ) {

        $object = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:DataDictionnaryElement')->find($id);



        $em = $this->getDoctrine()->getManager();
        $em->remove($object);
        $em->flush();

        return $this->redirect($this->generateUrl('_dataDictionnary_edit', array ('id' => $dataDictionnaryId)));

    }

    /**
     * @Route("/dataDictionnary/addElementModal/{referenceListId}", name="_dataDictionnary_addElementModal")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function addElementWidgetAction (Request $request, $dataDictionnaryId) {
        return $this->render('TellawLeadsFactoryBundle:entity/DataDictionnary:modal.html.twig', array ("referenceListId" => $dataDictionnaryId));
    }

    /**
     * @Route("/dataDictionnary/addElement", name="_dataDictionnary_addElement")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function saveElementAction ( Request $request ) {

        $name = $request->request->get('name');
        $value = $request->request->get('value');

        $dataDictionnaryId = $request->request->get('dataDictionnaryId');

        // Valid datas
        if ( trim ($name) == "" || trim ($value) == "" || trim ($dataDictionnaryId) == "" ) {
            // Error, forward back to form with error message
        }

        // Add item to reference list elements
        $dataDictionnary = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:DataDictionnary')->find($dataDictionnaryId);

        $element = new DataDictionnaryElement();
        $element->setName( $name );
        $element->setValue( $value );
        $element->setDataDictionnary( $dataDictionnary );
        $dataDictionnary->getElements()->add ( $element );

        $em = $this->getDoctrine()->getManager();
        $em->persist($element);
        $em->persist($dataDictionnary);
        $em->flush();

        // Forward request to list controller
        return $this->redirect($this->generateUrl('_dataDictionnary_edit', array ('id' => $dataDictionnaryId)));

    }

    /**
     * @Route("/dataDictionnary/sortElements", name="_dataDictionnary_sortElements")
     * @Secure(roles="ROLE_USER")
     */
    public function sortElementsAjaxAction ( Request $request ) {

        $elements = $request->request->get("element");
        $rank = 0;

        $em = $this->getDoctrine()->getManager();

        foreach ( $elements as $element ){
            $rank = $rank +10;
            $element = $this->get('leadsfactory.datadictionnary_element_repository')->find($element);
            $element->setRank($rank);
            $em->flush();
        }


        return new Response('<html><body>ok !</body></html>');

    }

    /**
     * @Route("/dataDictionnary/updateElement", name="_dataDictionnary_updateElement")
     * @Secure(roles="ROLE_USER")
     */
    public function updateElementAjaxAction ( Request $request ) {

        $listId = $request->request->get("listid");
        $id = $request->request->get("id");
        $text = $request->request->get("text");
        $value = $request->request->get("value");
        $enabled = $request->request->get("enabled");

        if (trim($id) != "" && $id != 0) {
            //var_dump("update");
            $em = $this->getDoctrine()->getManager();
            $element = $this->get('leadsfactory.datadictionnary_element_repository')->find($id);
            $element->setName( $text );
            $element->setValue ( $value );
            $element->setStatus ( $enabled );
            $em->flush();

        } else {
            //var_dump("create");
            $dictionnary = $this->get('leadsfactory.datadictionnary_repository')->find($listId);
            $em = $this->getDoctrine()->getManager();
            $element = new DataDictionnaryElement();
            $element->setDataDictionnary($dictionnary);
            $element->setName( $text );
            $element->setValue ( $value );
            $element->setStatus ( $enabled );
            $em->persist($element);
            //var_dump($element);
            $em->flush();
        }
        //var_dump($element);
        return new Response('Enregistré');
    }

    /**
     * @Route("/dataDictionnary/loadElement", name="_dataDictionnary_loadElement")
     * @Secure(roles="ROLE_USER")
     */
    public function loadElementAjaxAction ( Request $request ) {

        $id = $request->request->get("id");

        if (trim($id) != "" && $id != 0) {
            $element = $this->get('leadsfactory.datadictionnary_element_repository')->find($id);
            $response = array ();
            $response["id"] = $id;
            $response["name"] = $element->getName();
            $response["objvalue"] = $element->getValue();
            $response["enabled"] = $element->getStatus();
            return new Response ( json_encode($response) );
        }

        return null;

    }

    /**
     * @Route("/dataDictionnary/loadElementsTable", name="_dataDictionnary_loadElementsTable")
     * @Secure(roles="ROLE_USER")
     * @Template
     */
    public function loadElementsTableAjaxAction ( Request $request ) {

        $listId = $request->request->get("listid");

        if (trim($listId) != "" && $listId != 0) {
            $elements = $this->get('leadsfactory.datadictionnary_repository')->getElementsByOrder( $listId, "rank", "ASC", true );
        } else {
            $list = new ReferenceList();
            $elements = $list->getElements();
        }

        return $this->render('TellawLeadsFactoryBundle:entity/DataDictionnary:entity_referenceList_elements.html.twig',
            array(
                'elements'=> $elements ));
    }

}
