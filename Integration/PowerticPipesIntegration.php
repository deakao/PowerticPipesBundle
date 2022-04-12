<?php

namespace MauticPlugin\PowerticPipesBundle\Integration;

use Mautic\PluginBundle\Integration\AbstractIntegration;

class PowerticPipesIntegration extends AbstractIntegration
{
    const PLUGIN_NAME = 'PowerticPipes';

    public function getName()
    {
        return self::PLUGIN_NAME;
    }

    public function getDisplayName()
    {
        return 'Powertic Pipes';
    }

    /**
     * Return's authentication method such as oauth2, oauth1a, key, etc.
     *
     * @return string
     */
    public function getAuthenticationType()
    {
        // Just use none for now and I'll build in "basic" later
        return 'none';
    }
}
