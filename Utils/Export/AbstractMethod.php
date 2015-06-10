<?php

namespace Tellaw\LeadsFactoryBundle\Utils\Export;

use Tellaw\LeadsFactoryBundle\Entity\Export;


abstract class AbstractMethod {

    protected $exportDir = 'Export';

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /*
     *
     */
    public function setContainer (\Symfony\Component\DependencyInjection\ContainerInterface $container) {
        $this->container = $container;
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->container;
    }

    abstract protected function export($jobs, $form);

    /**
     * Retourne le chemin du dossier d'export
     *
     * @throws \Exception
     * @return string
     */
    protected function getExportPath()
    {
        $basePath = $this->getContainer()->get('kernel')->getRootDir();
        $path = $basePath . DIRECTORY_SEPARATOR . $this->exportDir;

        if(!file_exists($path)){
            if(!mkdir($path, '0755')){
                throw new \Exception('Error : can\'t create the export directory');
            }
        }
        return $path;
    }

	/**
	 * Teste la validité de l'email
	 *
	 * @param $lead
	 * @param $email
	 *
	 * @return mixed
	 */
	public function isEmailValidated($lead, $email)
	{
		return $this->getContainer()->get('leadsfactory.client_email_repository')->isEmailValidated($email);
	}

    /**
     * @param $reason String
     * @param $form Form
     * @param $export Export
     */
    private function notifyOfExportIssue ( $reason, $form, $export, $currentStatus, $newStatus ) {



    }

} 