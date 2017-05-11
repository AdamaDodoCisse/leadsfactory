<?php
namespace Tellaw\LeadsFactoryBundle\Utils;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Tellaw\LeadsFactoryBundle\Shared\AlertUtilsShared;

/**
 * Class AlertUtils
 *
 * This class intends to provide methods to check the status of objects and calculate its values
 *
 * @package Tellaw\LeadsFactoryBundle\Utils
 */
class AlertUtils extends AlertUtilsShared
{

    public static $_STATUS_UNKNOWN = 0;
    public static $_STATUS_OK = 1;
    public static $_STATUS_WARNING = 2;
    public static $_STATUS_ERROR = 3;
    public static $_STATUS_DATA_PROBLEM = 4; // Not used yet

    public $day = array('', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche');
    public $month = array('', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'aout', 'septembre', 'octobre', 'novembre', 'decembre');

    /** @var EntityManagerInterface */
    protected $entity_manager;

    /** @var array */
    protected $internal_email_patterns = array();

    public function __construct(EntityManagerInterface $entity_manager, array $internal_email_patterns, PreferencesUtils $preferencesUtils)
    {
        $this->entity_manager = $entity_manager;
        $this->internal_email_patterns = $internal_email_patterns;

    }

    /**
     * Method used to count leads for a specified day
     * @param $forms Array of forms
     * @param $minDate DateTime object
     * @return mixed
     */
    protected function getLeadsCountForForms($forms, $minDate)
    {

        foreach ($forms as $form) {
            $formIds[] = $form->getId();
        }

        // formated date time
        $formatedMinDate = $minDate->format('Y-m-d');

        // Count leads for the specified day
        $querybuilder = $this->entity_manager->createQueryBuilder();
        $querybuilder->select('count(l)')
            ->from('TellawLeadsFactoryBundle:Leads', 'l')
            ->where('l.form IN (:formIds)')
            ->andWhere('l.createdAt BETWEEN :minDate AND :maxDate')
            ->setParameter('formIds', $formIds)
            ->setParameter('minDate', $formatedMinDate . " 00:00:00")
            ->setParameter('maxDate', $formatedMinDate . " 23:59:59");

        $querybuilder = $this->excludeInternalLeads($querybuilder);

        return $querybuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param QueryBuilder $qb
     * @return QueryBuilder
     */
    protected function excludeInternalLeads(QueryBuilder $qb)
    {
        $i = 0;
        foreach ($this->internal_email_patterns as $pattern) {
            $qb->andWhere('l.email not like :pattern_' . $i)
                ->setParameter('pattern_' . $i, $pattern);
            ++$i;
        }

        return $qb;
    }
}
