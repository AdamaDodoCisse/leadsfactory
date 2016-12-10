<?php

namespace LeadsFactoryBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Services_Twilio_Twiml;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use LeadsFactoryBundle\Shared\CoreController;

class TwilioController extends CoreController
{

    /**
     * @Route("/call")
     */
    public function callAction(Request $request)
    {
        $logger = $this->get('logger');

        $phone = $request->query->get('phone');

        $logger->info('Phone : ' . $phone);

        if (!in_array(substr($phone, 1, 2), array('32', '33', '41'))
            && !in_array(substr($phone, 1, 3), array('352', '377'))
        ) {
            $logger->info('Refused to call ' . $phone);

            return new Response('Numéro invalide');
        }

        $twilio = $this->get('twilio.api');

        $twimlUrl = $this->generateUrl('_twilio_twiml', array(), true);

        $message = $twilio->account->calls->create(
            '+33975186520', // From a Twilio number in your account
            $phone, // Text any number
            $twimlUrl
        );

        $logger->info($message);

        return new Response($message->sid);
    }

    /**
     * @Route("/twiml", name="_twilio_twiml")
     */
    public function twimlAction()
    {
        $code = $this->getValidationCode();

        $twiml = new Services_Twilio_Twiml();

        $twiml->say('Bonjour,', array('language' => 'fr-FR'));
        $twiml->say('Votreu demande d\'information sera enregistrée lorsque vous aurez saisi le code suivant,', array('language' => 'fr-FR'));
        $twiml->say('Le code est : ,', array('language' => 'fr-FR'));
        $twiml->pause(array('length' => 1));
        foreach (str_split($code) as $digit) {
            if ($digit == '0') {
                $twiml->say('zéro', array('language' => 'fr-FR'));
            } else {
                $twiml->say($digit, array('language' => 'fr-FR'));
            }
        }
        $twiml->say('Je répète : ,', array('language' => 'fr-FR'));
        foreach (str_split($code) as $digit) {
            if ($digit == '0') {
                $twiml->say('zéro', array('language' => 'fr-FR'));
            } else {
                $twiml->say($digit, array('language' => 'fr-FR'));
            }
        }

        $twiml->pause(array('length' => 1));
        $twiml->say('Merci d\'utiliser les services des éditions tékniques de l\'ingénieur', array('language' => 'fr-FR'));

        $response = new Response($twiml);
        $response->headers->set('Content-Type', 'application/xml');

        return $response;
    }

    /**
     * @Route("/validate")
     */
    public function validateAction(Request $request)
    {
        $logger = $this->get('logger');

        $code = $request->query->get('code');
        $valid = (string)$code === (string)$this->getValidationCode();
        $response = new Response(json_encode(array('validate' => $valid)));
        $response->headers->set('Content-Type', 'application/json');

        $logger->info('validate : ' . $valid);

        return $response;
    }

    protected function getValidationCode()
    {
        return floor(intval(date('dm')) / 2);
    }

}
