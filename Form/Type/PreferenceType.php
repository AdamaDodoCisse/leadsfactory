<?php
namespace Tellaw\LeadsFactoryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PreferenceType extends AbstractType
{

    private $entity = "preference";

    public function getEntity() {
        return $this->entity;
    }

    public function getPostRoute() {
        return "_".$this->getEntity()."_post";
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {

        $resolver->setDefaults(
            array(
                'data_class' => 'Tellaw\LeadsFactoryBundle\Entity\Preference'
            )
        );

    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('keyval', null, array('label' => 'Clée'));
        $builder->add('value', null, array('label' => 'Valeur'));

        $builder->add ( 'scope', null, array('label' => 'Scope du formulaire') );

        $builder->add('save', 'submit');

    }

    public function getName()
    {
        return 'preference';
    }

}