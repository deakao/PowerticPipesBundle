<?php

namespace MauticPlugin\PowerticPipesBundle\Model;

use Doctrine\DBAL\Query\QueryBuilder;
use Mautic\CoreBundle\Model\AjaxLookupModelInterface;
use Mautic\CoreBundle\Model\FormModel;
use MauticPlugin\PowerticPipesBundle\Entity\Pipes;
use MauticPlugin\PowerticPipesBundle\Form\Type\PipesType;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class PipesModel extends FormModel implements AjaxLookupModelInterface
{

  public function createForm($entity, $formFactory, $action = null, $options = [])
    {
        if (!$entity instanceof Pipes) {
            throw new MethodNotAllowedHttpException(['Pipes']);
        }
        if (!empty($action)) {
            $options['action'] = $action;
        }

        return $formFactory->create(PipesType::class, $entity, $options);
    }

    public function getRepository()
    {
        return $this->em->getRepository('PowerticPipesBundle:Pipes');
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissionBase()
    {
        return 'powerticpipes:pipes';
    }

    /**
     * Get a specific entity or generate a new one if id is empty.
     *
     * @param $id
     *
     * @return Pipe|null
     */
    public function getEntity($id = null)
    {
        if (null === $id) {
            $entity = new Pipes();
        } else {
            $entity = parent::getEntity($id);
        }

        return $entity;
    }

    public function getFull($id)
    {
        $qb = $this->em->createQueryBuilder()
                    ->select('p, l, c, lc')
                    ->from('PowerticPipesBundle:Pipes', 'p')
                    ->leftJoin('p.lists', 'l')
                    ->leftJoin('l.cards', 'c')
                    ->leftJoin('c.lead', 'lc')
                    ->orderBy('l.sort, c.sort')
                    ->where('p.id = '.$id);
     
            
        return $qb->getQuery()->getArrayResult();
    }

  public function getLookupResults($type, $filter = '', $limit = 10, $start = 0, $options = [])
  {
    $results = [];
    return $results;
  }

}
