<?php

namespace MauticPlugin\PowerticPipesBundle;

use Mautic\PluginBundle\Bundle\PluginBundleBase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\PluginBundle\Entity\Plugin;

class PowerticPipesBundle extends PluginBundleBase
{

  static public function onPluginInstall(Plugin $plugin, MauticFactory $factory, $metadata = null, $installedSchema = null)
  {
      if ($metadata !== null) {
          self::installPluginSchema($metadata, $factory);
      }
      
  }
}