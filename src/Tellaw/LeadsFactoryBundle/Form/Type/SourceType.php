<?php

namespace Tellaw\LeadsFactoryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SourceType extends AbstractType {

public function setDefaultOptions(OptionsResolverInterface $resolver) {

}

public function getParent() {
    return 'textarea';
}

public function getName() {
    return 'source';
}
}