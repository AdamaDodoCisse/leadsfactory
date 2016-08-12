<?php

namespace Tellaw\LeadsFactoryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RulesType extends AbstractType
{

    public function getParent()
    {
        return 'textarea';
    }

    public function getName()
    {
        return 'rules';
    }
}
