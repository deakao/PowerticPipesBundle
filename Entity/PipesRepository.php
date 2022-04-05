<?php

namespace MauticPlugin\PowerticPipesBundle\Entity;

use Mautic\CoreBundle\Entity\CommonRepository;

/**
 * ListsRepository
 */
class PipesRepository extends CommonRepository
{

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