<?php

namespace ZF\Doctrine\ORM\QueryBuilder\Filter;

use Doctrine\ORM\QueryBuilder;

class InFilter extends AbstractFilter
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

        if (! isset($option['alias'])) {
            $option['alias'] = 'row';
        }

        $format = isset($option['format']) ? $option['format'] : null;

        $queryValues = array();
        foreach ($option['values'] as $value) {
            $queryValues[] = $this->typeCastField(
                $metadata,
                $option['field'],
                $value,
                $format,
                $doNotTypecastDatetime = true
            );
        }

        $parameter = uniqid('a');
        $queryBuilder->$queryType(
            $queryBuilder
                ->expr()
                ->in($option['alias'] . '.' . $option['field'], ':' . $parameter)
        );
        $queryBuilder->setParameter($parameter, $queryValues);
    }
}
