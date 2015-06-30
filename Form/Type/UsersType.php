<?php
namespace Tellaw\LeadsFactoryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Tellaw\LeadsFactoryBundle\Entity\Users;

class UsersType extends AbstractType
{

    private $entity = "users";

    public function getEntity() {
        return $this->entity;
    }

    public function getPostRoute() {
        return "_".$this->getEntity()."_post";
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {

        $resolver->setDefaults(
            array(
                'data_class' => 'Tellaw\LeadsFactoryBundle\Entity\Users',
            )
        );

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('firstname');
        $builder->add('lastname');
        $builder->add('login');
        // $builder->add('password', 'password', array ("label" => "mot de passe", "required" => false));

        $builder->add('role', 'choice', array(
            'choices'  =>  Users::$_ROLES,
            'label' => "Rôle",
            'required' => false
            )
        );

        $builder->add('scope');

        $builder->add('save', 'submit');

    }

    public function getName()
    {
        return 'users';
    }

}