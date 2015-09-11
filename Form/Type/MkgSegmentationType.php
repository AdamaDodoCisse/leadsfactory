<?php
namespace Tellaw\LeadsFactoryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MkgSegmentationType extends AbstractType
{

    public function setDefaultOptions(OptionsResolverInterface $resolver) {

        $resolver->setDefaults(
            array(
                'data_class' => 'Tellaw\LeadsFactoryBundle\Entity\MkgSegmentation',
                'attr' => array('id' => 'form-form'
                )
            )
        );

    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('name',null, array('label' => 'Nom de l\'export'));
        $builder->add('description',null, array('label' => 'Description'));

        $builder->add('nbDays',null, array('label' => 'Nombre de jours à embarquer dans l\'export'));

        $builder->add('scope',null, array('label' => 'Scope de l\'export (aucun impact sur la requete)'));
        $builder->add('code' ,null, array('label' => 'Identifiant Kibana de la requete') );

        $builder->add('save', 'submit');

    }

    public function getName()
    {
        return 'mkgSegmentation';
    }

}