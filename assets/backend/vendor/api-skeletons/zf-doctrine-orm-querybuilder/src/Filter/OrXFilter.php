<?php

namespace ZF\Doctrine\ORM\QueryBuilder\Filter;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;

class OrXFilter extends AbstractFilter
{
    public function filter(QueryBuilder $queryBuilder, $metadata, $option)
    {
        if (isset($option['where'])) {
            if ($option['where'] === 'and') {
                $queryType = 'andWhere';
            } elseif ($option['where'] === 'or') {
                $queryType = 'orWhere';
            }
        }

        if (! isset($queryType)) {
            $queryType = 'andWhere';
        }

        $orX = $queryBuilder->expr()->orX();
        $em = $queryBuilder->getEntityManager();
        $qb = $em->createQueryBuilder();

        foreach ($option['conditions'] as $condition) {
            $filter = $this->getFilterManager()
                ->get(
                    strtolower($condition['type']),
                    array($this->getFilterManager())
                );
            $filter->filter($qb, $metadata, $condition);
        }

        $dqlParts = $qb->getDqlParts();
        $orX->addMultiple($dqlParts['where']->getParts());
        $queryBuilder->setParameters(
            new ArrayCollection(array_merge_recursive(
                $queryBuilder->getParameters()->toArray(),
                $qb->getParameters()->toArray()
            ))
        );

        $queryBuilder->$queryType($orX);
    }
}
