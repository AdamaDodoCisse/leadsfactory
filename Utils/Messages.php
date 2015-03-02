<?php
namespace Tellaw\LeadsFactoryBundle\Utils;

use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class Messages
 * @package Tellaw\LeadsFactoryBundle\Utils
 * Class used to share status messages in the back office application
 */
class Messages
{
    public static $_TYPE_SUCCESS = 1;
    public static $_TYPE_ERROR = 2;
    public static $_MESSAGES_POOL_NAME = "message_pool";

    /** @var Session */
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function pushMessage($messageType, $messageTitle, $messageContent)
    {
        if ($this->session->has(Messages::$_MESSAGES_POOL_NAME)) {
            $poolOfMessages = $this->session->get ( Messages::$_MESSAGES_POOL_NAME );
        } else {
            $poolOfMessages = array();
        }

        $poolOfMessages[] = array (
            "type" => $messageType,
            "title" => $messageTitle,
            "message" => $messageContent
        );

        $this->session->set ( Messages::$_MESSAGES_POOL_NAME, $poolOfMessages );
    }

    public function pullMessages($parentRoute = null)
    {
        if ($this->session->has(Messages::$_MESSAGES_POOL_NAME)) {
            $poolOfMessages = $this->session->get(Messages::$_MESSAGES_POOL_NAME);
            $this->session->remove(Messages::$_MESSAGES_POOL_NAME);
        } else {
            $poolOfMessages = null;
        }

        return $poolOfMessages;
    }
}
