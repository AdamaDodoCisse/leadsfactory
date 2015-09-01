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
    
    public function getNameUsingListCode($list_code, $element_value) {
        if (empty($element_value)) {
            return '';
        }
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e.name')
                ->from('TellawLeadsFactoryBundle:ReferenceListElement', 'e')
                ->join('TellawLeadsFactoryBundle:ReferenceList', 'l')
                ->where('l.code = :code')
                ->andWhere('e.referencelist_id = l.value')
                ->andWhere('e.value = :value')
                ->setParameter('code', $list_code)
                ->setParameter('value', $element_value)
        ;
        $query = $qb->getQuery();

        try {
            $result = $query->getSingleScalarResult();
        } catch (\Exception $e) {
            $result = '';
        }
        return $result;
    }
    
    public function getNameUsingListCodeAndValue($list_code, $element_value) {
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
            $result = $query->getResult();            
        } catch (\Exception $e) {
            $result = '';
        }
        return $result;
    }
    
    public function getValueUsingListCodeAndName($list_code, $element_value) {
        if (empty($element_value)) {
            return '';
        }
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e.value')
                ->from('TellawLeadsFactoryBundle:ReferenceListElement', 'e')
                ->join('e.referenceList', 'l')
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
