<?php
// weka-leadsfactory-master\vendor\tellaw\leadsfactory\Tellaw\LeadsFactoryBundle\Entity\ReferenceListElementRepository.php
namespace Tellaw\LeadsFactoryBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Psr\Log\LoggerInterface;

/**
 * ReferenceListElementRepository
 *
 */
class ReferenceListElementRepository extends EntityRepository {

    /** @var LoggerInterface */
    private $logger;

    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    /**
     * Retourne le libellé correspondant à la valeur d'une option
     *
     * @param string $list_code
     * @param string $element_value
     *
     * @return string
     */

    public function getNameUsingListCode($list_code, $element_value, $return_type = 0)
    {
        if (empty($element_value)) {
            return '';
        }
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e.name')
            ->from('TellawLeadsFactoryBundle:ReferenceListElement', 'e')
            ->join('e.referenceList', 'l')
            ->where('l.code = :code')
            ->andWhere('e.value = :value')
            ->setParameter('code', $list_code)
            ->setParameter('value', $element_value)
        ;
        $query = $qb->getQuery();

        try {
            if (!$return_type) {
                $result = $query->getSingleScalarResult();
            } else{
                $result = $query->getResult();
            }
        } catch(\Exception $e) {
            //$this->logger->warning($e->getMessage());
            $result = '';
        }
        return $result;
    }

    /**
     * @deprecated
     *
     * @param $list_code
     * @param $element_value
     * @return string
     */
    public function getNameUsingListCodeAndValue($list_code, $element_value) {
        return $this->getNameUsingListCode($list_code, $element_value, 1);
    }
    
    public function getValueUsingListCodeAndName($list_code, $element_value) {
        if (empty($element_value)) {
            return '';
        }

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e.value')
                ->from('TellawLeadsFactoryBundle:ReferenceListElement', 'e')
                ->join('e.referenceList', 'l')
                ->andWhere('e.referencelist_id = l.id')
                ->where('l.code = :code')
                ->andWhere('e.name = :name')
                ->setParameter('code', $list_code)
                ->setParameter('name', $element_value)
        ;
        
        $query = $qb->getQuery();
        try {
            $result = $query->getResult();
        } catch (\Exception $e) {
            $result = '';
        }        
        return $result;
    }
}
