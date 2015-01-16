<?php
namespace Tellaw\LeadsFactoryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FormType extends AbstractType
{

    private $entity = "form";

    public function getEntity() {
        return $this->entity;
    }

    public function getPostRoute() {
        return "_".$this->getEntity()."_post";
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {

        $resolver->setDefaults(
            array(
                'data_class' => 'Tellaw\LeadsFactoryBundle\Entity\Form',
                'attr' => array('id' => 'form-form',
                            'onSubmit' => 'validateFormAction();'
                )
            )
        );

    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('name');
        $builder->add('description');

        $builder->add('code',null, array('label' => 'Code (identifiant technique) du formulaire'));

	    $builder->add('secure_key',null, array('label' => 'Code de sécurité'));

        $builder->add('utmcampaign',null, array('label' => 'Code action par défaut'));

        $builder->add ( 'formType',null, array('label' => 'Type du formulaire') );

        $builder->add('source', new SourceType(), array('label' => 'Source Pseudo HTML', 'required' => false));
        $builder->add('script', new ScriptType(), array('label' => 'Javascript', 'required' => false));

        $builder->add('exportConfig', new JsonType(), array('label' => 'Export config', 'required' => false));
        $builder->add('alertRules', new RulesType(), array('label' => 'Alertes', 'required' => false));

        $builder->add('confirmationEmailSource', new Source2Type(), array('label' => 'Confirmation email Source HTML', 'required' => false));

        $builder->add('save', 'submit');

    }

    public function getName()
    {
        return 'form';
    }

}