<?php
/**
 * Created by PhpStorm.
 * User: seth
 * Date: 07/12/15
 * Time: 16:11
 */

namespace Tellaw\LeadsFactoryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SegmentConfigType extends AbstractType
{

    private $configs = null;
    private $targetUrl = null;

    public function __construct($configs, $targetUrl)
    {
        $this->configs = $configs;
        $this->targetUrl = $targetUrl;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {

        $resolver->setDefaults(
            array(
                'attr' => array('id' => 'form-form',
                    'onSubmit' => 'submitFormAction();'
                )
            )
        );
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->setAction($this->targetUrl);
        $builder->setMethod('POST');
        foreach ($this->configs as $key => $config) {
            $builder->add($key, 'text', array('label' => $config['label'], 'data' => $config['default']));
        }
        $builder->add('save', 'submit', array('label' => 'Générer le segment'));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'segmentConfig';
    }
}
