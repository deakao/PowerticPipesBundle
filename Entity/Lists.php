<?php 
namespace MauticPlugin\PowerticPipesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\CoreBundle\Entity\FormEntity;
use Doctrine\Common\Collections\ArrayCollection;

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

  private $pipe_id;

  private $cards;

  private $pipe;
  
  
  public function __construct()
  {
    $this->cards = new ArrayCollection();
  }

  public static function loadMetadata(ORM\ClassMetadata $metadata)
  {
      $builder = new ClassMetadataBuilder($metadata);

      $builder->setTable('powertic_pipes_lists')
          ->setCustomRepositoryClass('MauticPlugin\PowerticPipesBundle\Entity\ListsRepository');

      $builder->createManyToOne('pipe', 'Pipes')
            ->inversedBy('pipes')
            ->addJoinColumn('pipe_id', 'id', false, false, 'CASCADE')
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

    public function setPipe($pipe_id)
    {
      $this->pipe_id = $pipe_id;
    }
    
}
