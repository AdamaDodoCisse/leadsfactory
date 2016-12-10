<?php
namespace LeadsFactoryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FieldType extends AbstractType
{

    private $entity;

    public function __construct()
    {
        $this->entity = 'field';
    }

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
                'data_class' => 'LeadsFactoryBundle\Entity\Field',
                'attr' => array('id' => 'form-form',
                    'onSubmit' => 'validateFormAction();'
                )
            )
        );

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('code');
        $builder->add('description');
        $builder->add('testvalue', new JsonType(), array('label' => 'Valeur de test'));
        $builder->add('save', 'submit');
    }

    public function getName()
    {
        return 'field';
    }

}
