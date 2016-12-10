<?php
namespace LeadsFactoryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MkgSegmentationType extends AbstractType
{

    private $searches = null;

    public function __construct($searches)
    {
        $this->searches = $searches;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {

        $resolver->setDefaults(
            array(
                'data_class' => 'LeadsFactoryBundle\Entity\MkgSegmentation',
                'attr' => array('id' => 'form-form',
                    'onSubmit' => 'validateFormAction();'
                )
            )
        );
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('name', null, array('label' => 'Nom de l\'export'));
        $builder->add('description', null, array('label' => 'Description'));

        $builder->add('config', new JsonType(), array('label' => 'Configuration', 'required' => false));

        $builder->add('scope', null, array('label' => 'Scope de l\'export (aucun impact sur la requete)'));
        $builder->add('query_code', 'choice', array('choices' => $this->searches, 'label' => 'Identifiant Kibana de la requete'));

        $builder->add('save', 'submit', array('label' => 'Enregistrer'));
    }

    public function getName()
    {
        return 'mkgSegmentation';
    }

}
