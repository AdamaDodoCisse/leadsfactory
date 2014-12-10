<?php
namespace Tellaw\LeadsFactoryBundle\Utils;

use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class Messages
 * @package Tellaw\LeadsFactoryBundle\Utils
 * Class used to share status messages in the back office application
 */
class Messages {

    public static $_TYPE_SUCCESS = 1;
    public static $_TYPE_ERROR = 2;

    public static $_MESSAGES_POOL_NAME = "message_pool";


    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    private $container;

    public function setContainer (\Symfony\Component\DependencyInjection\ContainerInterface $container) {
        $this->container = $container;
    }

    public function pushMessage ( $messageType, $messageTitle, $messageContent ) {

        /** @var $session Symfony\Component\HttpFoundation\Session\Session */
        $session = $this->container->get("session");

        if ( $session->has ( Messages::$_MESSAGES_POOL_NAME ) ) {
            $poolOfMessages = $session->get ( Messages::$_MESSAGES_POOL_NAME );
        } else {
            $poolOfMessages = array();
        }

        $poolOfMessages[] = array ( "type" => $messageType,
                                    "title" => $messageTitle,
                                    "message" => $messageContent );

        $session->set ( Messages::$_MESSAGES_POOL_NAME, $poolOfMessages );

        //var_dump ($session);die();

    }

    public function pullMessages ( $parentRoute = null) {

        $session = $this->container->get("session");

        if ( $session->has ( Messages::$_MESSAGES_POOL_NAME ) ) {
            $poolOfMessages = $session->get(Messages::$_MESSAGES_POOL_NAME);
            $session->remove(Messages::$_MESSAGES_POOL_NAME);

        } else {
            $poolOfMessages = null;
        }



        return $poolOfMessages;

    }

}