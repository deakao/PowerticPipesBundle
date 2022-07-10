<?php
namespace MauticPlugin\PowerticPipesBundle\Form\Type;


use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Mautic\CoreBundle\Form\Type\FormButtonsType;
use Mautic\CoreBundle\Form\Type\YesNoButtonGroupType;
use Symfony\Component\Form\FormBuilderInterface;

class CardsType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, [
            'label'      => 'mautic.core.name',
            'label_attr' => [
                'class' => 'control-label',
            ], 'attr' => [
                'class' => 'form-control',
            ], ]);

        $builder->add('description', TextareaType::class, [
            'label'      => 'mautic.core.description',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => ['class' => 'form-control editor'],
            'required'   => false,
        ]);

        $builder->add('value', TextType::class, [
            'label'      => 'plugin.powerticpipes.card.value',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => ['class' => 'form-control'],
            'required'   => false,
        ]);

        $choices = [];
        $choice_value = null;
        if($options['data'] and $options['data']->getLead()){
            $lead = $options['data']->getLead();
            $choices[$lead->getFirstName(). ' '.$lead->getLastName(). '('.$lead->getEmail().')'] = $lead->getId();
            $choice_value = $lead->getId();
        }

        $builder->add('lead', ChoiceType::class, [
            'label'      => 'mautic.lead.contact',
            'label_attr' => [
                'class' => 'control-label',
            ], 
            'attr' => [
                'class' => 'form-control',
            ], 
            'required'   => false,
            'choices' => $choices,
            'data' => $choice_value
        ]);
            
        
        $builder->add('buttons', FormButtonsType::class);
    }


    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MauticPlugin\PowerticPipesBundle\Entity\Cards',
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'cards';
    }
}
