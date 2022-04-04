<?php
namespace MauticPlugin\PowerticPipesBundle\Security\Permissions;

use Symfony\Component\Form\FormBuilderInterface;
use Mautic\CoreBundle\Security\Permissions\AbstractPermissions;

/**
 * Class PipesPermissions.
 */
class PipesPermissions extends AbstractPermissions
{
    /**
     * {@inheritdoc}
     */
    public function __construct($params)
    {
        parent::__construct($params);
        $this->addStandardPermissions('powerticpipes');

    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'powerticpipes';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface &$builder, array $options, array $data)
    {
        $this->addStandardFormFields('powerticpipes', 'pipes', $builder, $data);

    }
}