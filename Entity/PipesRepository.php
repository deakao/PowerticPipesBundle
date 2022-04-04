<?php

namespace MauticPlugin\PowerticPipesBundle\Entity;

use Mautic\CoreBundle\Entity\CommonRepository;

/**
 * ListsRepository
 */
class PipesRepository extends CommonRepository
{

    public function getEntities($args = array())
    {
        $q = $this->getEntityManager()
            ->createQueryBuilder()
            ->select($this->getTableAlias() . '')
            ->from('PowerticPipesBundle:Pipes', $this->getTableAlias(), $this->getTableAlias() . '.id');

        $args['qb'] = $q;

        return parent::getEntities($args);
    }

    /**
     * {@inheritdoc}
     */
    public function getTableAlias()
    {
        return 'powertic_pipes';
    }

    /**
     * @param QueryBuilder $q
     * @param              $filter
     *
     * @return array
     */
    protected function addCatchAllWhereClause($q, $filter)
    {
        return $this->addStandardCatchAllWhereClause($q, $filter, [
            $this->getTableAlias().'.name',
            $this->getTableAlias().'.description',
        ]);
    }
    /**
     * @param QueryBuilder $q
     * @param              $filter
     *
     * @return array
     */
    protected function addSearchCommandWhereClause($q, $filter)
    {
        return $this->addStandardSearchCommandWhereClause($q, $filter);
    }

    
}