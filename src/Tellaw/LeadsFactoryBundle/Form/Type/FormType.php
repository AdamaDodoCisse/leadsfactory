<?php
namespace Tellaw\LeadsFactoryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FormType extends AbstractType
{

    private $entity = "form";

    public function getEntity() {
        return $this->entity;
    }

    public function getPostRoute() {
        return "_".$this->getEntity()."_post";
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {

        $resolver->setDefaults(
            array(
                'data_class' => 'Tellaw\LeadsFactoryBundle\Entity\Form',
            )
        );

    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name');
        $builder->add('description');

        $builder->add('source');
        $builder->add('style');
        $builder->add('script');

        $builder->add ( 'formType' );

        $builder->add('save', 'submit');

    }

    public function getName()
    {
        return 'form';
    }

}