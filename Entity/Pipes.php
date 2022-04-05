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

}