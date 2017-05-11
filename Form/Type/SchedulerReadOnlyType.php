<?php

namespace Tellaw\LeadsFactoryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SchedulerReadOnlyType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('enabled')
            ->add('id', null, array("read_only" => true))
            ->add('name', null, array("read_only" => true))
            ->add('commandsAsString', null, array("read_only" => true))
            ->add('serviceName', null, array("read_only" => true))
            ->add('cronexpression')
            ->add('log', null, array("read_only" => true))
            ->add('save', 'submit');
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Tellaw\LeadsFactoryBundle\Entity\CronTask'
        ));
    }

    public function getPostRoute()
    {
        return "_scheduler_post";
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tellaw_leadsfactorybundle_crontask';
    }
}
