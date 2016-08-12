<?php
namespace Tellaw\LeadsFactoryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EntrepriseType extends AbstractType
{

    private $entity = "entreprise";

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
                'data_class' => 'Tellaw\LeadsFactoryBundle\Entity\Entreprise',
                'attr' => array('id' => 'form-person'
                )
            )
        );

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('name');

        $builder->add('save', 'submit');

    }

    public function getName()
    {
        return 'person';
    }

}
