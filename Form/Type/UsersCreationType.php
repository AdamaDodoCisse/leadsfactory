<?php
namespace Tellaw\LeadsFactoryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Tellaw\LeadsFactoryBundle\Entity\Users;

class UsersCreationType extends AbstractType
{

    private $entity = "users";

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
                'data_class' => 'Tellaw\LeadsFactoryBundle\Entity\Users',
            )
        );

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('firstname', null, array("required" => true));
        $builder->add('lastname', null, array("required" => true));
        $builder->add('login', null, array("required" => true));
        $builder->add('password', 'password', array("label" => "mot de passe", "required" => true));

        $builder->add('role', 'choice', array(
                'choices' => Users::$_ROLES,
                'label' => "Rôle",
                'required' => false
            )
        );

        $builder->add('scope');
        $builder->add('email', null, array("required" => true));

        $builder->add('save', 'submit');

    }

    public function getName()
    {
        return 'users';
    }

}
