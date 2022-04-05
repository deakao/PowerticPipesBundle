<?php 
namespace MauticPlugin\PowerticPipesBundle\Model;

use Doctrine\DBAL\Query\QueryBuilder;
use Mautic\CoreBundle\Model\AjaxLookupModelInterface;
use Mautic\CoreBundle\Model\FormModel;
use MauticPlugin\PowerticPipesBundle\Entity\Lists;
use MauticPlugin\PowerticPipesBundle\Form\Type\ListsType;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class CardsModel extends FormModel implements AjaxLookupModelInterface
{
  public function createForm($entity, $formFactory, $action = null, $options = [])
    {
        if (!$entity instanceof Cards) {
            throw new MethodNotAllowedHttpException(['Cards']);
        }
        if (!empty($action)) {
            $options['action'] = $action;
        }

        return $formFactory->create(CardsType::class, $entity, $options);
    }

    public function getRepository()
    {
        return $this->em->getRepository('PowerticPipesBundle:Cards');
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissionBase()
    {
        return 'powerticpipes:lists';
    }

    /**
     * Get a specific entity or generate a new one if id is empty.
     *
     * @param $id
     *
     * @return Lists|null
     */
    public function getEntity($id = null)
    {
        if (null === $id) {
            $entity = new Lists();
        } else {
            $entity = parent::getEntity($id);
        }

        return $entity;
    }

  public function getLookupResults($type, $filter = '', $limit = 10, $start = 0, $options = [])
  {
    $results = [];
    return $results;
  }
}