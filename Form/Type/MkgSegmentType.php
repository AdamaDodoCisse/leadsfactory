<?php
/**
 * Created by PhpStorm.
 * User: seth
 * Date: 07/12/15
 * Time: 17:07
 */

namespace Tellaw\LeadsFactoryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class MkgSegmentType extends AbstractType
{
    private $filter = null;
    private $filter_json = null;
    private $segmentation_id = null;

    public function __construct ( $filter, $segmentation_id ) {
        $this->filter_json = $filter;
        $this->segmentation_id = $segmentation_id;

        $filter = json_decode($filter);
        $this->filter = 'Aucune donnée.';
        if ($filter) {
            $this->filter = "Les valeurs suivantes sont injectées dans votre requête : \n";
            foreach ($filter as $k => $d) {
                $this->filter .= "- ".$k." = ".$d."\n";
            }
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {

        $resolver->setDefaults(
            array(
                'data_class' => 'Tellaw\LeadsFactoryBundle\Entity\MkgSegment',
                'attr' => array('id' => 'form-form',
                    'onSubmit' => 'validateFormAction();'
                )
            )
        );
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('name',null, array('label' => 'Nom du segment'));

        $builder->add('filter_txt','textarea', array('label' => 'Filtre appliqué', 'required' => false, 'data'=>$this->filter, 'read_only' => true));
        $builder->add('filter','hidden', array('data'=>$this->filter_json));

        $builder->add('segmentation', 'hidden', array('data'=>$this->segmentation_id, 'by_reference' => false));

        $builder->add('nb_days',null, array('label' => 'Nombre de jours (laisser "0" pour utiliser les dates)', 'data' => '0'));

        $builder->add('date_start','date', array('label' => 'Date de debut'));
        $builder->add('date_end','date', array('label' => 'Date de Fin'));

        $builder->add('enabled',null, array('label' => 'Activation de l\'export automatique', 'required' => false));

        $builder->add('emails',null, array('label' => 'Emails des destinataires séparés par des point-virgules', 'required' => false));

        $builder->add('cronexpression',null, array('label' => 'Cron expression de l\'export', 'required' => false));

        $builder->add('confirmationemailssubjects', null, array('label' => 'Sujet du mail', 'required' => false));

        $builder->add('confirmationEmailSource', 'textarea', array('label' => 'Source du mail', 'required' => false));

        $builder->add('log', null, array ("read_only" => true ));

        $builder->add('save', 'submit', array('label'=>'Enregistrer'));
    }

    public function getName()
    {
        return 'mkgSegment';
    }

}