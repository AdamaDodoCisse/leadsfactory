<?php

namespace Tellaw\LeadsFactoryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class JsonType extends AbstractType {

public function getParent() {
    return 'textarea';
}

public function getName() {
    return 'json';
}
}