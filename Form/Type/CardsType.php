<?php
namespace MauticPlugin\PowerticPipesBundle\Form\Type;

use Mautic\CoreBundle\Form\Type\AbstractFormStandardType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class CardsType extends AbstractFormStandardType
{

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
