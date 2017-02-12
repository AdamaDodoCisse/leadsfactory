<?php

namespace LeadsFactoryBundle\Controller\Admin;

use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/entity")
 */
class EntityFormController extends CoreController
{

    public function __construct() {}

    /**
     *
     * @Route("/form/list/{page}/{limit}/{keyword}", name="_form_list")
     * @Secure(roles="ROLE_USER")
     *
     */
    public function indexAction($page = 1, $limit = 10, $keyword = '')
    {

        $list = $this->getList('LeadsFactoryBundle:Form', $page, $limit, $keyword, array('user' => $this->getUser()));
        $bookmarks = $this->getDoctrine()->getRepository('LeadsFactoryBundle:Form')->getBookmarkedFormsForUser($this->getUser()->getId());

        // Get the full list to know the number of elements
        $fullList = $this->getList('LeadsFactoryBundle:Form', null, null, $keyword, array('user' => $this->getUser()));
        $numberOfElements = count($fullList);

        $formatedBookmarks = array();
        foreach ($bookmarks as $bookmark) {
            $formatedBookmarks[$bookmark->getForm()->getId()] = true;
        }

        $responseData = array();
        foreach ($list as $listItem) {

            if (array_key_exists($listItem->getId(),$formatedBookmarks)) {
                $isABookmark = true;
            } else {
                $isABookmark = false;
            }

            $responseData[] = array(
                "id" => $listItem->getId(),
                "name" => $listItem->getName(),
                "description" => $listItem->getDescription(),
                "isABookmark" => $isABookmark
            );
        }

        if ($numberOfElements > 0) {
            $numberOfPages = ceil($numberOfElements/$limit);
        } else {
            $numberOfPages = 0;
        }

        $response = array(
            'items' => $responseData,
            'scope' => $this->getUser()->getScope()->getId(),
            'numberOfElements' => $numberOfElements,
            'numberOfPages' => $numberOfPages,
            'query' => array (
                'page' => $page,
                'limit' => $limit,
                'keyword' => $keyword
            )
        );

        return new JsonResponse($response);

    }

}