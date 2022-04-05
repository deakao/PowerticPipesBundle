<?php 
namespace MauticPlugin\PowerticPipesBundle\Model;

use Doctrine\DBAL\Query\QueryBuilder;
use Mautic\CoreBundle\Model\AjaxLookupModelInterface;
use Mautic\CoreBundle\Model\FormModel;
use MauticPlugin\PowerticPipesBundle\Entity\Lists;
use MauticPlugin\PowerticPipesBundle\Form\Type\ListsType;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class ListsModel extends FormModel implements AjaxLookupModelInterface
{
  public function createForm($entity, $formFactory, $action = null, $options = [])
    {
        if (!$entity instanceof Lists) {
            throw new MethodNotAllowedHttpException(['Lists']);
        }
        if (!empty($action)) {
            $options['action'] = $action;
        }

        return $formFactory->create(ListsType::class, $entity, $options);
    }

    public function getRepository()
    {
        return $this->em->getRepository('PowerticPipesBundle:Lists');
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