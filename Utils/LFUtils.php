<?php
namespace Tellaw\LeadsFactoryBundle\Utils;

use Symfony\Component\HttpFoundation\Request;
use Tellaw\LeadsFactoryBundle\Entity\ReferenceListElement;
use Tellaw\LeadsFactoryBundle\Entity\UserPreferences;

class LFUtils {

    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    private $container;

    public function setContainer (\Symfony\Component\DependencyInjection\ContainerInterface $container) {
        $this->container = $container;
    }

    /**
     * Method used to update elements of the Reference List
     * @param $jsonElements
     */
    public function updateListElements ( $jsonElements ) {

        $jsonObject = json_decode( $jsonElements, true );
        $validIdByLevels = array();

        // Step 1 : Get referring lists
        $lists = $jsonObject["lists"];
        $childs = $jsonObject["elements"];

        $level = 0;

        // Step 2 : Update list
        if (count ($childs) > 0) list ($validIdByLevels, $lists) = $this->updateChilds( $childs, $lists, $validIdByLevels, $level, null );

        $this->deleteOldChilds( $validIdByLevels, $lists );

    }

    private function updateChilds ( $childs, $lists, $validIdByLevels, $level, $parentId ) {

        /**
         *
         * Two cases :
         *
         * 1) Item has already an ID => We update the item
         * 2) Item has no ID, it is a new One, we have to create IT.
         *
         */

        $em = $this->container->get("doctrine")->getManager();

        foreach ( $childs as $key => $child ) {

            if ( array_key_exists( "id", $child ) ) {

                // Case of existing item, just update it.
                $item = $em->getRepository('TellawLeadsFactoryBundle:ReferenceListElement')->find( $child["id"] );
                $item->setName ( $child["name"] );
                $item->setValue ( $child["value"] );
                $em->flush();

                // Get this to keep in memory treated items to delete old ones.
                $validIdByLevels[$lists[$level]][$child["id"]] = true;


            } else {

                // Case of new Item, create it
                $item = new ReferenceListElement();
                $item->setName( $child["name"] );
                $item->setValue ( $child["value"] );

                $referenceList = $em->getRepository('TellawLeadsFactoryBundle:ReferenceList')->find( $lists[$level] );
                $item->setReferenceList ( $referenceList );

                if ( $parentId != null ) {

                    $parent = $em->getRepository('TellawLeadsFactoryBundle:ReferenceListElement')->find( $parentId );
                    $item->setParent ( $parent );

                }
                $em->persist($item);
                $em->flush();

                $validIdByLevels[$lists[$level]][$item->getId()] = true;

            }

            /**
             * Digging into childrens
             */
            $parentIdTmp = $item->getId();
            if (array_key_exists( "childrens",$child  )) {
                if (count ($child["childrens"]) > 0) {
                    list ($validIdByLevels, $lists) = $this->updateChilds( $child["childrens"], $lists, $validIdByLevels, $level+1, $parentIdTmp );
                }
            }

        }

        return array ($validIdByLevels, $lists);

    }

    /**
     * This method compares data readden from JSON and delete childs.
     * @param $validIdByLevels
     * @param $lists
     */
    public function deleteOldChilds ( $validIdByLevels, $lists ) {

        // Reverse list to delete from the end. This is to avoid constraint violation
        array_reverse( $lists );

        $em = $this->container->get('doctrine')->getManager();

        foreach ( $lists as $list ) {

            $sql = 'SELECT id FROM `ReferenceListElement` WHERE referencelist_id = :listId';
            $query = $em->getConnection()->prepare($sql);
            $query->bindValue('listId', $list);
            $query->execute();
            $results = $query->fetchAll();

            foreach ($results as $result) {

                if ( !array_key_exists( $result["id"], $validIdByLevels[$list] )) {

                    $object =  $this->container->get('doctrine')->getRepository('TellawLeadsFactoryBundle:ReferenceListElement')->find($result["id"]);

                    $em = $this->container->get('doctrine')->getManager();
                    $em->remove($object);
                    $em->flush();

                    echo ("Delete : ".$result["id"]. " - ");
                }

            }

        }

    }

    public function getListOfLists ( $listId, $lists = array()  ) {

        if ( !in_array( $listId, $lists )) {
            $lists[] = $listId;
        }

        //$lists = array();
        $listId = $this->getLinkedLists( $listId );

        if ($listId)  {
            $lists[] = $listId;
            $lists = $this->getListOfLists( $listId, $lists );
        }

        return $lists;

    }

    public function getLinkedLists ( $listId ) {

        $em = $this->container->get('doctrine')->getManager();

        $sql = 'SELECT referencelist_id FROM `ReferenceListElement` WHERE parent_id IN (SELECT id FROM `ReferenceListElement` WHERE referencelist_id = :listId ) GROUP BY referencelist_id';

        $query = $em->getConnection()->prepare($sql);
        $query->bindValue('listId', $listId);
        $query->execute();
        $results = $query->fetchAll();

        if (count ($results) > 0) {
            $id = $results[0]["referencelist_id"];
            return $id;
        } else {
            return false;
        }

    }

    public function getUserPreferences () {

        $session = $this->container->get('session');

        if ($session->has ('user-preferences')) {
            return $session->get('user-preferences');

        } else {
            $userPreferences = new UserPreferences();
            $session->set ('user-preferences', $userPreferences);
            $session->save();

            return $userPreferences;

        }

    }

    public function setUserPreferences ( $userPreferences ) {
        $session = $this->container->get('session');
        $session->set ('user-preferences', $userPreferences);
        $session->save();

    }

}

