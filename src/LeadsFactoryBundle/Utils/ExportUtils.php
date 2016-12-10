<?php
namespace LeadsFactoryBundle\Utils;

use Cron\CronExpression;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraints\DateTime;
use LeadsFactoryBundle\Entity\ClientEmailRepository;
use LeadsFactoryBundle\Shared\ExportUtilsShared;
use LeadsFactoryBundle\Utils\Export\AbstractMethod;

class ExportUtils extends ExportUtilsShared
{

    public static $_EXPORT_NOT_PROCESSED = 0;
    public static $_EXPORT_SUCCESS = 1;
    public static $_EXPORT_ONE_TRY_ERROR = 2;
    public static $_EXPORT_MULTIPLE_ERROR = 3;
    public static $_EXPORT_NOT_SCHEDULED = 4;
    const EXPORT_EMAIL_NOT_CONFIRMED = 5;

    /**
     * Email notifications settings
     */
    const NOTIFICATION_DEFAULT_FROM = 'leadsfactory@domain.com';
    const NOTIFICATION_DEFAULT_TEMPLATE = 'emails:notification_default.html.twig';

    /**
     * @var array
     */
    private $_methods = array();

    /**
     * @var string
     */
    private $_defaultCronExp = "0 * * * *";

    /** @var  ClientEmailRepository */
    private $client_email_repository;


    public function __construct(ClientEmailRepository $client_email_repository)
    {
        $this->client_email_repository = $client_email_repository;
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->container;
    }

    public function addMethod(AbstractMethod $method, $alias)
    {
        $this->_methods[$alias] = $method;
    }

    public function getMethod($alias)
    {
        return $this->_methods[$alias];
    }

    /**
     * @param $config
     * @return bool
     */
    public function hasScheduledExport($config)
    {
        return (isset($config['export']) && is_array($config['export'])) ? true : false;
    }

    /**
     * Check if export method is valid
     *
     * @param $method
     * @return bool
     */
    public function isValidExportMethod($method)
    {
        return array_key_exists($method, $this->_methods) ? true : false;
    }


}

