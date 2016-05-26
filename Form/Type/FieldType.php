<?php
namespace Tellaw\LeadsFactoryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Tellaw\LeadsFactoryBundle\Entity\Users;

class FieldType extends AbstractType
{

    private $entity;

    public function __construct()
    {
        $this->entity = 'field';
    }

    public function getEntity() {
        return $this->entity;
    }

    public function getPostRoute() {
        return "_".$this->getEntity()."_post";
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {

        $resolver->setDefaults(
            array(
                'data_class' => 'Tellaw\LeadsFactoryBundle\Entity\Field',
            )
        );

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('code');
        $builder->add('description');
        $builder->add('testvalue', null, array('label' => 'Valeur de test'));

        $builder->add('save', 'submit');
    }

    public function getName()
    {
        return 'field';
    }

}