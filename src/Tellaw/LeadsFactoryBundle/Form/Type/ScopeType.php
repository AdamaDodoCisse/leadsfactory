<?php

namespace Tellaw\LeadsFactoryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ScopeType extends AbstractType
{

    private $entity = "form";

    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code')
            ->add('name')
            ->add('save', 'submit')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Tellaw\LeadsFactoryBundle\Entity\Scope'
        ));
    }

    public function getPostRoute()
    {
        return "_".$this->getEntity()."_post";
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tellaw_leadsfactorybundle_scope';
    }
}
