<?php

namespace Tellaw\LeadsFactoryBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Entity\ReferenceListElement;
use Tellaw\LeadsFactoryBundle\Form\Type\ReferenceListType;
use Tellaw\LeadsFactoryBundle\Form\Type\ReferenceListElementType;
use Tellaw\LeadsFactoryBundle\Utils\LFUtils;

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
class EntityReferenceListController extends AbstractEntityController
{

    /**
     *
     * @Route("/referenceList/list/{page}/{limit}/{keyword}", name="_referenceList_list")
     * @Secure(roles="ROLE_USER")
     *
     */
    public function indexAction($page=1, $limit=10, $keyword='')
    {

        $list = $this->getList ('TellawLeadsFactoryBundle:ReferenceList', $page, $limit, $keyword, array () );
        //$forms = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:ReferenceList')->findAll();

        return $this->render(
            'TellawLeadsFactoryBundle:entity/ReferenceList:entity_referenceList_list.html.twig',
            array(
                'elements'      => $list['collection'],
                'pagination'    => $list['pagination'],
                'limit_options' => $list['limit_options']
            )
        );

    }

    /**
     * @Route("/referenceList/new", name="_referenceList_new")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function newAction( Request $request )
    {

        $type = new ReferenceListType();

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

            return $this->redirect($this->generateUrl('_referenceList_list'));
        }

        return $this->render('TellawLeadsFactoryBundle:entity/ReferenceList:entity_referenceList_new.html.twig', array(  'form' => $form->createView(),
                                                                                                    'title' => "Création d'une liste de référence",
                                                                                                    'refernceListId' => '-1'));
    }

    /**
     * @Route("/referenceList/buildfile/id/{id}", name="_referenceList_build_file")
     * @Secure(roles="ROLE_USER")
     * @Method("GET")
     */
    public function buildfileAction ( $id ) {

        $fileName = '../datas/json-lists/'.$id.'.json';

        $fs = new Filesystem();
        $formData = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:ReferenceList')->find($id);
        $referingLists = $this->get("lf.utils")->getListOfLists( $id );
        $json = $formData->getJson($referingLists);

        $fs->dumpFile( $fileName , $json);

        $messagesUtils = $this->container->get("messages.utils");
        $messagesUtils->pushMessage( Messages::$_TYPE_SUCCESS, "Liste de référence", "Le fichier à été généré avec succès : ".$fileName );

        return new Response("ok",200,array('Content-Type'=>'text/plain'));

    }

    /**
     * @Route("/referenceList/importfile/id/{id}", name="_referenceList_import_file")
     * @Secure(roles="ROLE_USER")
     * @Method("GET")
     */
    public function importfileAction ( $id ) {

        $fileName = '../datas/json-lists/'.$id.'.json';

        $content = implode ('', file ( $fileName ));

        $this->get("lf.utils")->updateListElements( $jsonElements );

        $messagesUtils = $this->container->get("messages.utils");
        $messagesUtils->pushMessage( Messages::$_TYPE_SUCCESS, "Liste de référence", "Le fichier à été importé avec succès : ".$fileName );

        return new Response("ok",200,array('Content-Type'=>'text/plain'));

    }

    /**
     * @Route("/referenceList/edit/{id}", name="_referenceList_edit")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function editAction( Request $request, $id )
    {

        /**
         * This is the new / editing action
         */

        // crée une tâche et lui donne quelques données par défaut pour cet exemple
        $formData = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:ReferenceList')->find($id);

        $type = new ReferenceListType();

        $form = $this->createForm(  $type,
                                    $formData,
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

            // Traitement des éléments de la liste
            //$jsonElements = $form->get('json')->getData();
            //$this->get("lf.utils")->updateListElements( $jsonElements );

            $file = $form->get('attachment')->getData()->openFile('r');
            $jsonElements = "";
            while (!$file->eof()) {
                $jsonElements .= $file->current();
                //do what you have to do with $line...
                $file->next();
            }

            $this->get("lf.utils")->updateListElements( $jsonElements );

            return $this->redirect($this->generateUrl('_referenceList_list'));
        }

        /*
        $referingLists = $this->get("lf.utils")->getListOfLists( $id );
        $form->get('json')->setData( $formData->getJson($referingLists) );
        */
        $formData->getElements();

        return $this->render('TellawLeadsFactoryBundle:entity/ReferenceList:entity_referenceList_edit.html.twig',
                                array(  'form' => $form->createView(),
                                        'elements'=> $formData->getElements(),
                                        'title' => "Edition d'une liste de référence",
                                        'referenceListId' => $id));

    }

    /**
     * @Route("/referenceList/delete/id/{id}", name="_referenceList_delete")
     * @Secure(roles="ROLE_USER")
     * @Method("GET")
     * @Template()
     */
    public function deleteAction ( $id ) {

        /**
         * This is the deletion action
         */
        $object = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:ReferenceList')->find($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($object);
        $em->flush();

        return $this->redirect($this->generateUrl('_referenceList_list'));

    }

    private function saveJsonFeed ( $json ) {

        $elements = json_decode ( $json, true );

        foreach ($elements as $element) {

        }

    }

    /**
     * @Route("/referenceList/deleteElement/id/{id}/{referenceListId}", name="_referenceList_deleteElement")
     * @Secure(roles="ROLE_USER")
     * @Method("GET")
     * @Template()
     */
    public function deleteElementAction ( Request $request, $id, $referenceListId ) {

        $object = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:ReferenceListElement')->find($id);



        $em = $this->getDoctrine()->getManager();
        $em->remove($object);
        $em->flush();

        return $this->redirect($this->generateUrl('_referenceList_edit', array ('id' => $referenceListId)));

    }

    /**
     * @Route("/referenceList/addElementModal/{referenceListId}", name="_referenceList_addElementModal")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function addElementWidgetAction (Request $request, $referenceListId) {
        return $this->render('TellawLeadsFactoryBundle:entity/ReferenceList:modal.html.twig', array ("referenceListId" => $referenceListId));
    }

    /**
     * @Route("/referenceList/addElement", name="_referenceList_addElement")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function saveElementAction ( Request $request ) {

        $name = $request->request->get('name');
        $value = $request->request->get('value');

        $referenceListId = $request->request->get('referenceListId');

        // Valid datas
        if ( trim ($name) == "" || trim ($value) == "" || trim ($referenceListId) == "" ) {
            // Error, forward back to form with error message
        }

        // Add item to reference list elements
        $referenceList = $this->getDoctrine()->getRepository('TellawLeadsFactoryBundle:ReferenceList')->find($referenceListId);

        $referenceListElement = new ReferenceListElement();
        $referenceListElement->setName( $name );
        $referenceListElement->setValue( $value );
        $referenceListElement->setReferenceList( $referenceList );
        $referenceList->getElements()->add ( $referenceListElement );

        $em = $this->getDoctrine()->getManager();
        $em->persist($referenceListElement);
        $em->persist($referenceList);
        $em->flush();

        // Forward request to list controller
        return $this->redirect($this->generateUrl('_referenceList_edit', array ('id' => $referenceListId)));

    }

}