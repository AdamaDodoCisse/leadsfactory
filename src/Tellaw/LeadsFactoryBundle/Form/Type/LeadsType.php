<?php
namespace Tellaw\LeadsFactoryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LeadsType extends AbstractType
{

    private $entity = "leads";

    public function getEntity() {
        return $this->entity;
    }

    public function getPostRoute() {
        return "_".$this->getEntity()."_post";
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {

        $resolver->setDefaults(
            array(
                'data_class' => 'Tellaw\LeadsFactoryBundle\Entity\Leads',
            )
        );

    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('firstname');
        $builder->add('lastname');


    }

    public function getName()
    {
        return 'leads';
    }

}