<?php

namespace Tellaw\LeadsFactoryBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Tellaw\LeadsFactoryBundle\Entity\Form;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/testing")
 * @Cache(expires="tomorrow")
 */
class DefaultController extends Controller
{
    public function indexAction($name)
    {

        // crée une tâche et lui donne quelques données par défaut pour cet exemple
        $form = new Form();
        $form->setName('Write a blog post');
        $form->setDescription('tomorrow is the description test');
        $form = $this->createFormBuilder($task)
            ->add('name', 'text')
            ->add('description', 'text')
            ->add('save', 'submit')
            ->getForm();

        return $this->render('TellawLeadsFactoryBundle:Default:index.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/test/", name="_index")
     * @Template()
     */
    public function testAction()
    {
        // crée une tâche et lui donne quelques données par défaut pour cet exemple
        $form = new Form();
        $form->setName('Write a blog post');
        $form->setDescription('tomorrow is the description test');
        $form = $this->createFormBuilder($form)
            ->add('name', 'text')
            ->add('description', 'text')
            ->add('save', 'submit')
            ->getForm();

        return $this->render('TellawLeadsFactoryBundle:Default:test.html.twig', array('form' => $form->createView()));
    }

}
