<?php
namespace Tellaw\LeadsFactoryBundle\Utils;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Tellaw\LeadsFactoryBundle\Entity\Leads;
use Tellaw\LeadsFactoryBundle\Entity\LeadsHistory;
use Tellaw\LeadsFactoryBundle\Entity\Users;

/**
 * Class HistoryUtils
 *
 * This util class is used to manage the hitory element related to the different type of objects
 *
 * @author Eric Wallet
 */
class EmailUtils implements ContainerAwareInterface {

    /**
     * @var ContainerInterface
     */
    protected $container;

    public static $_ACTION_AFFECT_LEADS = "Changement d'affectation pour la LEAD #%s";
    public static $_DETAILED_ACTION_AFFECT_LEADS = "Un utilisateur vient de modifier l'affectation d'une lead.";
    public static $_MESSAGE_AFFECT_LEADS = "Le %s %s %s vient de vous assigner la lead : %s";

    public static $_ACTION_THEME_LEADS = "Changement de thème pour une LEAD";
    public static $_DETAILED_ACTION_THEME_LEADS = "Un utilisateur vient de modifier le thème associé à une lead.";
    public static $_MESSAGE_THEME_LEADS = "Le %s %s %s vient de vous assigner la lead : %s";

    public static $_ACTION_TYPE_LEADS = "Changement de type pour une LEAD";
    public static $_DETAILED_ACTION_TYPE_LEADS = "Un utilisateur vient de modifier le type associé à une lead.";
    public static $_MESSAGE_TYPE_LEADS = "Le %s %s %s vient de modifier le type la lead : %s";

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null) {

        $this->container = $container;
        $this->logger = $this->container->get("logger");
    }

    /**
     * @param Users $destUser
     * @param $action   "Very short message to describe the action, could be a Verb"
     * @param $detailAction "Sentence to explain the action"
     * @param $message  "Detailed message related to the action"
     * @param $urlLead  "Url of the leads object the action is about"
     * @param $urlApplication   "Url of the Lead's factory Application"
     * @return mixed
     */
    public function sendUserNotification ( Users $destUser, $action, $detailAction, $message, $urlLead, $urlApplication ) {

        $toEmail = $destUser->getEmail();
        $toName = ucfirst($destUser->getFirstname()) . ' ' . ucfirst($destUser->getLastname());

        $to = array($toEmail => $toName);

        $prefUtils = $this->get('preferences_utils');
        $from = $email = $prefUtils->getUserPreferenceByKey('CORE_LEADSFACTORY_EMAIL_SENDER', $scopeId);

        $subject = "Lead's Factory : ".$action;

        $template = $this->renderView(
            'TellawLeadsFactoryBundle::emails/lead_notification.html.twig',
            array(
                "action" => $action,
                "detailAction" => $detailAction,
                "user" => $destUser,
                "message" => $message,
                "urlLead" => $urlLead,
                "urlApplication" => $urlApplication,
            )
        );

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($from)
            ->setTo($to)
            ->setBody($template, 'text/html');

        return $this->get('mailer')->send($message);

    }

}