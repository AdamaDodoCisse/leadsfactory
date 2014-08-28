<?php
/**
 * @package
 * @author: nicolas
 * @since:  22/08/14
 */

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
     * @return string
     * @throws Exception
     */
    protected function getExportPath()
    {
        //$basePath = $this->getContainer()->get('kernel')->locateResource('@TellawLeadsFactoryBundle');
        $basePath = $this->getContainer()->get('kernel')->getRootDir();
        $path = $basePath . DIRECTORY_SEPARATOR . $this->exportDir;

        if(!file_exists($path)){
            if(!mkdir($path, '0755')){
                throw new Exception('Error : can\'t create the export directory');
            }
        }
        return $path;
    }

} 