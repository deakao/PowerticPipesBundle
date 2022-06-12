<?php

namespace MauticPlugin\PowerticPipesBundle;

use Doctrine\DBAL\Schema\Schema;
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

  public static function onPluginUpdate(Plugin $plugin, MauticFactory $factory, $metadata = null, Schema $installedSchema = null)
  {
    $database       = $factory->getDatabase();
    $platform       = $database->getDatabasePlatform()->getName();
    $queries        = array();
    $fromVersion    = $plugin->getVersion();
    switch ($fromVersion) {
      case '0.5':
        $queries[] = 'ALTER TABLE ' . MAUTIC_TABLE_PREFIX . 'powertic_pipes ADD `fromStages` BOOLEAN NULL DEFAULT NULL AFTER `is_completed`';
        $queries[] = 'ALTER TABLE ' . MAUTIC_TABLE_PREFIX . 'powertic_pipes ADD `leadColumns` JSON NULL DEFAULT NULL AFTER `fromStages`';
        $queries[] = 'ALTER TABLE ' . MAUTIC_TABLE_PREFIX . 'powertic_pipes_cards ADD `value` FLOAT NULL DEFAULT NULL AFTER `lead_id`';
        break;
    }
    if (!empty($queries)) {
      $database->beginTransaction();
      try {
        foreach ($queries as $query) {
          $database->query($query);
        }
        $database->commit();
      } catch (\Exception $exception) {
        $database->rollBack();

        throw $exception;
      }
    }

    self::updatePluginSchema($metadata, $installedSchema, $factory);
  }
}
