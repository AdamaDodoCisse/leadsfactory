<?php
namespace Tellaw\LeadsFactoryBundle\Utils;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Tellaw\LeadsFactoryBundle\Entity\Leads;
use Tellaw\LeadsFactoryBundle\Entity\LeadsHistory;

/**
 * Class HistoryUtils
 *
 * This util class is used to manage the hitory element related to the different type of objects
 *
 * @author Eric Wallet
 */
class HistoryUtils implements ContainerAwareInterface {

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null) {

        $this->container = $container;
        $this->logger = $this->container->get("logger");
    }

    /**
     * Method used to know if an object is useable with history management
     *
     * @param $object
     * @return bool
     */
    public function isAvailable ( $object ) {

        if ($object instanceof \Tellaw\LeadsFactoryBundle\Entity\Leads) {
            return true;
        }

        return false;

    }

    /**
     *
     * This method will push an element in the history
     *
     * @param $comment String message to log in the history. Message must have been prepared to String before calling this method
     * @param $user \Tellaw\LeadsFactoryBundle\Entity\Users object
     * @param object Object on which history log will apply to. The system will throw an Exception if the object type is not supported
     */
    public function push ( $logMessage, $user, $object ) {

        if (!$this->isAvailable( $object )) {
            throw new Exception ("Object is not supported for history management");
        }

        // This factory is used to build the correct history object depending of the history target
        if ($object instanceof Leads) {

            $history = new LeadsHistory();
            $history->setLead( $object );
            $history->setUser( $user );
            $history->setLog( $logMessage );
            $history->setCreatedAt( new \DateTime() );

            $em = $this->container->get('doctrine')->getManager();
            $em->persist($history);
            $em->flush();

        }

    }

}