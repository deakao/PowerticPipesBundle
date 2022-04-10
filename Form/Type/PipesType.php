<?php
namespace MauticPlugin\PowerticPipesBundle\Form\Type;

use Mautic\CoreBundle\Form\Type\AbstractFormStandardType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Mautic\CoreBundle\Security\Permissions\CorePermissions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Mautic\CoreBundle\Form\Type\FormButtonsType;
use Mautic\CoreBundle\Form\Type\YesNoButtonGroupType;
use Symfony\Component\Form\FormBuilderInterface;

class PipesType extends AbstractType
{

    /**
     * @var CorePermissions
     */
    private $security;

    public function __construct(CorePermissions $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('description', TextareaType::class, [
            'label'      => 'mautic.core.description',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => ['class' => 'form-control editor'],
            'required'   => false,
        ]);
        $builder->add('name', TextType::class, [
            'label'      => 'mautic.core.name',
            'label_attr' => [
                'class' => 'control-label',
            ], 'attr' => [
                'class' => 'form-control',
            ], ]);

        if (!empty($options['data']) && $options['data'] instanceof Stage) {
            $readonly = !$this->security->hasEntityAccess(
                'powerticpipes:pipes:publishown',
                'powerticpipes:pipes:publishother',
                $options['data']->getCreatedBy()
            );

            $data = $options['data']->isPublished(false);
        } elseif (!$this->security->isGranted('powerticpipes:pipes:publishown')) {
            $readonly = true;
            $data     = false;
        } else {
            $readonly = false;
            $data     = true;
        }
        $builder->add('isPublished', YesNoButtonGroupType::class, [
            'data' => $data,
            'attr' => [
                'readonly' => $readonly,
            ],
        ]);

        $builder->add('fromStages', YesNoButtonGroupType::class, [
            'label' => 'plugin.powerticpipes.pipes.fromstages',
            'data'  => false,
        ]);
        $builder->add('buttons', FormButtonsType::class);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MauticPlugin\PowerticPipesBundle\Entity\Pipes',
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pipes';
    }
}
