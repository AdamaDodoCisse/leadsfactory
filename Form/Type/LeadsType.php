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


        $builder->add('exportdate','date', array( 'widget' => 'single_text', 'format' => 'dd-MM-yyyy'));
        $builder->add('log');
        $builder->add('utmcampaign');
        $builder->add('telephone');
        $builder->add('createdAt','date', array( 'widget' => 'single_text', 'format' => 'dd-MM-yyyy'));

        $builder->add('data', new JsonType(), array('label' => 'Données brutes'));

        $builder->add('save', 'submit');

    }

    public function getName()
    {
        return 'leads';
    }

}