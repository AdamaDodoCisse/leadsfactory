<?php
namespace Tellaw\LeadsFactoryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ReferenceListType extends AbstractType
{

    private $entity = "referenceList";

    public function getEntity() {
        return $this->entity;
    }

    public function getPostRoute() {
        return "_".$this->getEntity()."_post";
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {

        $resolver->setDefaults(
            array(
                'data_class' => 'Tellaw\LeadsFactoryBundle\Entity\ReferenceList',
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

        $builder->add('attachment', 'file', array('mapped' => false, 'label' => 'Fichier de description de la liste', 'required' => false));

        $builder->add('json', new JsonType(), array('mapped' => false, 'label' => 'Elements de la liste', 'required' => false));

        $builder->add('save', 'submit');

    }

    public function getName()
    {
        return 'referenceList';
    }



}