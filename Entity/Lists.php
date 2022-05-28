<?php 
namespace MauticPlugin\PowerticPipesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\CoreBundle\Entity\FormEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Mautic\StageBundle\Entity\Stage;

class Lists extends FormEntity
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
   * @var int
   */
  private $sort;

  
  /**
   * @var ArrayCollection
   */
  private $cards;

  /**
   * @var \MauticPlugin\PowerticPipesBundle\Entity\Pipes
   */
  private $pipe;

  /**
   * @var \Mautic\StageBundle\Entity\Stage
   */
  private $stage;
  
  public function __construct()
  {
    $this->cards = new ArrayCollection();
  }

  public static function loadMetadata(ORM\ClassMetadata $metadata)
  {
      $builder = new ClassMetadataBuilder($metadata);

      $builder->setTable('powertic_pipes_lists')
          ->setCustomRepositoryClass('MauticPlugin\PowerticPipesBundle\Entity\ListsRepository')
          ->addIndex(['sort'], 'sort');

      $builder->createManyToOne('pipe', 'Pipes')
            ->inversedBy('pipes')
            ->addJoinColumn('pipe_id', 'id', false, false, 'CASCADE')
            ->build();

      $builder->createOneToMany('cards', 'Cards')
            ->mappedBy('list')
            ->addJoinColumn('id', 'list_id', true, false, 'CASCADE')
            ->build();

      $builder->createField('sort', 'integer')
            ->columnName('sort')
            ->nullable()
            ->build();

      $builder->createManyToOne('stage', Stage::class)
            ->inversedBy('stages')
            ->addJoinColumn('stage_id', 'id', true, false, 'CASCADE')
            ->build();

      $builder->addIdColumns('name', false);
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
     * @return Lists
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function setPipe($pipe)
    {
      $this->pipe = $pipe;
      return $this;
    }

    public function getPipe()
    {
      return $this->pipe;
    }

    public function setSort($sort)
    {
      $this->sort = (int) $sort;
      return $this;
    }

    public function getSort()
    {
      return $this->sort;
    }

    public function setStage($stage)
    {
      $this->stage = $stage;
      return $this;
    }
    
    public function getStage()
    {
      return $this->stage;
    }
}
