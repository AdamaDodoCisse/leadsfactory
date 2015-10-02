<?php
namespace Tellaw\LeadsFactoryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MkgSegmentationType extends AbstractType
{

    private $searches = null;

    public function __construct ( $searches ) {
        $this->searches = $searches;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {

        $resolver->setDefaults(
            array(
                'data_class' => 'Tellaw\LeadsFactoryBundle\Entity\MkgSegmentation',
                'attr' => array('id' => 'form-form',
                    'onSubmit' => 'validateFormAction();'
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
        $builder->add('code' ,'choice', array('choices'  => $this->searches,'label' => 'Identifiant Kibana de la requete'));

        $builder->add('enabled',null, array('label' => 'Activation de l\'export automatique', 'required' => false));
        $builder->add('emails',null, array('label' => 'Emails des destinataires séparés par des point-virgules', 'required' => false));
        $builder->add('cronexpression',null, array('label' => 'Cron expression de l\'export', 'required' => false));

        $builder->add('confirmationemailssubjects', null, array('label' => 'Sujet du mail', 'required' => false));
        $builder->add('confirmationEmailSource', 'textarea', array('label' => 'Source du mail', 'required' => false));

        $builder->add('log', null, array ("read_only" => true ));

        $builder->add('save', 'submit');

    }

    public function getName()
    {
        return 'mkgSegmentation';
    }

}