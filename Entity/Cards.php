<?php 
namespace MauticPlugin\PowerticPipesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\CoreBundle\Entity\FormEntity;
use Doctrine\Common\Collections\ArrayCollection;

class Cards extends FormEntity
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
  * @var string
  */
  private $description;

  private $list;
  
  
  public function __construct()
  {
    $this->pipes = new ArrayCollection();
  }

  public static function loadMetadata(ORM\ClassMetadata $metadata)
  {
    $builder = new ClassMetadataBuilder($metadata);

    $builder->setTable('powertic_pipes_cards')
          ->setCustomRepositoryClass('MauticPlugin\PowerticPipesBundle\Entity\CardsRepository')
          ->addIndex(['sort'], 'sort');

    $builder->createManyToOne('list', 'Lists')
            ->inversedBy('lists')
            ->addJoinColumn('list_id', 'id', false, false, 'CASCADE')
            ->build();

    $builder->createField('sort', 'integer')
            ->columnName('sort')
            ->nullable()
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
     * @return Lists
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

    public function setSort($sort)
    {
      $this->sort = (int) $sort;
      return $this;
    }

    public function getSort()
    {
      return $this->sort;
    }
}
