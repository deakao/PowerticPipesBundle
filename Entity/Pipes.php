<?php

namespace MauticPlugin\PowerticPipesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\CoreBundle\Entity\FormEntity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Pipes
 */
class Pipes extends FormEntity
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var ArrayCollection
    */
    private $lists;

    
    private $fromStages;

    private $is_completed;

    private $leadColumns;

    private $import_status;

    public function __construct()
    {
        $this->lists = new ArrayCollection();
    }

    /**
     * @param ORM\ClassMetadata $metadata
     */
    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable('powertic_pipes')
            ->setCustomRepositoryClass('MauticPlugin\PowerticPipesBundle\Entity\PipesRepository');
        
        $builder->createOneToMany('lists', 'Lists')
            ->mappedBy('pipe')
            ->addJoinColumn('id', 'pipe_id', true, false, 'CASCADE')
            ->build();

        $builder->createField('fromStages', 'boolean')
            ->columnName('fromStages')
            ->build();
        
        $builder->createField('leadColumns', 'json')
            ->columnName('leadColumns')
            ->build();

        $builder->createField('is_completed', 'boolean')
            ->columnName('is_completed')
            ->build();

        $builder->createField('import_status', 'json')
            ->columnName('import_status')
            ->build();

        $builder->addIdColumns();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    public function getImportStatus()
    {
        return $this->import_status;
    }

    public function setImportStatus($import_status)
    {
        $this->import_status = $import_status;
        return $this;
    }

    /**
     * @param mixed $name
     *
     * @return Pipes
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Pipes
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function getLists()
    {
        return $this->lists;
    }

    public function getFromStages()
    {
        return $this->fromStages;
    }

    public function setFromStages($stage)
    {
        $this->fromStages = $stage;
        return $this;
    }

    public function setIsCompleted($is_completed)
    {
      $this->is_completed = $is_completed;
      return $this;
    }

    public function getIsCompleted()
    {
      return $this->is_completed;
    }

    public function setLeadColumns($leadColumns)
    {
        $this->leadColumns = $leadColumns;
        return $this;
    }

    public function getLeadColumns()
    {
        return $this->leadColumns;
    }
}