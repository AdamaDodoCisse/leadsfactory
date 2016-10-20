<?php
namespace Tellaw\LeadsFactoryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ReferenceListElementType extends AbstractType
{

    private $entity = "referenceListElement";

    public function getEntity()
    {
        return $this->entity;
    }

    public function getPostRoute()
    {
        return "_" . $this->getEntity() . "_post";
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {

        $resolver->setDefaults(
            array(
                'data_class' => 'Tellaw\LeadsFactoryBundle\Entity\ReferenceListElement',
            )
        );

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name');
        $builder->add('value');
        $builder->add('parent');

        $builder->add('save', 'submit');

    }

    public function getName()
    {
        return 'referenceListElement';
    }

}
