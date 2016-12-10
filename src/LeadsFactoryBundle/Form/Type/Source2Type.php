<?php

namespace LeadsFactoryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class Source2Type extends AbstractType
{

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {

    }

    public function getParent()
    {
        return 'textarea';
    }

    public function getName()
    {
        return 'source2';
    }
}
