<?php 
namespace MauticPlugin\PowerticPipesBundle\Model;

use Doctrine\DBAL\Query\QueryBuilder;
use Mautic\CoreBundle\Model\AjaxLookupModelInterface;
use Mautic\CoreBundle\Model\FormModel;
use MauticPlugin\PowerticPipesBundle\Entity\Cards;
use MauticPlugin\PowerticPipesBundle\Form\Type\CardsType;
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
        return 'powerticpipes:cards';
    }

    /**
     * Get a specific entity or generate a new one if id is empty.
     *
     * @param $id
     *
     * @return Cards|null
     */
    public function getEntity($id = null)
    {
        if (null === $id) {
            $entity = new Cards();
        } else {
            $entity = parent::getEntity($id);
        }

        return $entity;
    }

    public function getFromList($list_id, $limit = 10, $offset = 0)
    {
        $qb = $this->em->createQueryBuilder()
            ->select('c, lc')
            ->from('PowerticPipesBundle:Cards', 'c')
            ->leftJoin('c.lead', 'lc')
            ->where('c.list = '.$list_id)
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $qb->getQuery()->getArrayResult();
    }

    public function getCountFromList($list_id)
    {
        $qb = $this->em->createQueryBuilder()
            ->select('count(c.id)')
            ->from('PowerticPipesBundle:Cards', 'c')
            ->where('c.list = '.$list_id);

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getLookupResults($type, $filter = '', $limit = 10, $start = 0, $options = [])
    {
        $results = [];
        return $results;
    }

    public function getEntityFromLead($lead_id, $list_id)
    {
        $qb = $this->em->createQueryBuilder()
            ->select('c')
            ->from('PowerticPipesBundle:Cards', 'c')
            ->where('c.list = '.$list_id)
            ->andWhere('c.lead = '.$lead_id);
        return $qb->getQuery()->getArrayResult();
    }
}