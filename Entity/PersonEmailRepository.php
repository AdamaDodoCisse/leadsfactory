<?php

namespace Tellaw\LeadsFactoryBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * ExportRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PersonEmailRepository extends EntityRepository
{
    /**
     * An email e-mail is considered validated if it has a validation date
     *
     * @param string $email
     *
     * @return bool
     */
    public function isEmailValidated($email)
    {
        /** @var ClientEmail|null $entity */
        $entity = $this->findOneByEmail($email);
        if (is_null($entity)) {
            return false;
        }

        return !is_null($entity->getValidation());
    }
}