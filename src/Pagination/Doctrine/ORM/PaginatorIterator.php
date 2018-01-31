<?php declare(strict_types=1);

namespace Fazland\ApiPlatformBundle\Pagination\Doctrine\ORM;

use Doctrine\ORM\QueryBuilder;
use Fazland\ApiPlatformBundle\Doctrine\ObjectIterator;
use Fazland\ApiPlatformBundle\Doctrine\Traits\IteratorTrait;
use Fazland\ApiPlatformBundle\Pagination\Orderings;
use Fazland\ApiPlatformBundle\Pagination\PaginatorIterator as BaseIterator;

final class PaginatorIterator extends BaseIterator implements ObjectIterator
{
    use IteratorTrait;

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var null|int
     */
    private $_totalCount;

    public function __construct(QueryBuilder $queryBuilder, $orderBy)
    {
        $this->queryBuilder = clone $queryBuilder;
        $this->apply(null);

        parent::__construct([], $orderBy);
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        if (null === $this->_totalCount) {
            $queryBuilder = clone $this->queryBuilder;
            $alias = $queryBuilder->getRootAliases()[0];

            $this->_totalCount = (int) $queryBuilder->select('COUNT(DISTINCT '.$alias.')')
                ->getQuery()
                ->getSingleScalarResult();
        }

        return $this->_totalCount;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        parent::next();

        $this->_current = null;
        $this->_currentElement = parent::current();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        parent::rewind();

        $this->_current = null;
        $this->_currentElement = parent::current();
    }

    protected function getObjects(): array
    {
        $queryBuilder = clone $this->queryBuilder;
        $alias = $queryBuilder->getRootAliases()[0];

        foreach ($this->orderBy as $key => list($field, $direction)) {
            $method = 0 == $key ? 'orderBy' : 'addOrderBy';
            $queryBuilder->{$method}($alias.'.'.$field, strtoupper($direction));
        }

        $limit = $this->pageSize;
        if (null !== $this->token) {
            $limit += $this->token->getOffset();

            $mainOrder = $this->orderBy[0];
            $direction = Orderings::SORT_ASC === $mainOrder[1] ? '>=' : '<=';
            $queryBuilder->andWhere($alias.'.'.$mainOrder[0].' '.$direction.' :timeLimit');
            $queryBuilder->setParameter('timeLimit', $this->token->getTimestamp());
        }

        $queryBuilder->setMaxResults($limit);

        return $queryBuilder->getQuery()->getResult();
    }
}