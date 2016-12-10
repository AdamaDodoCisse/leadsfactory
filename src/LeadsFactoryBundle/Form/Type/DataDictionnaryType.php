<?php
namespace LeadsFactoryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DataDictionnaryType extends AbstractType
{

    private $entity = "dataDictionnary";

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
                'data_class' => 'LeadsFactoryBundle\Entity\DataDictionnary',
                'attr' => array('id' => 'form-form',
                    'onSubmit' => 'validateFormAction();'
                )
            )
        );

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('code');
        $builder->add('name');
        $builder->add('description');
        $builder->add('scope');
        $builder->add('save', 'submit');

    }

    public function getName()
    {
        return 'dataDictionnary';
    }


}
