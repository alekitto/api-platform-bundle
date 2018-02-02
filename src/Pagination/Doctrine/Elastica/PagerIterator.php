<?php declare(strict_types=1);

namespace Fazland\ApiPlatformBundle\Pagination\Doctrine\Elastica;

use Elastica\Query;
use Fazland\ApiPlatformBundle\Doctrine\ObjectIterator;
use Fazland\ApiPlatformBundle\Doctrine\Traits\IteratorTrait;
use Fazland\ApiPlatformBundle\Pagination\Orderings;
use Fazland\ApiPlatformBundle\Pagination\PagerIterator as BaseIterator;
use Fazland\ODM\Elastica\Search\Search;

final class PagerIterator extends BaseIterator implements ObjectIterator
{
    use IteratorTrait;

    /**
     * @var Search
     */
    private $search;

    /**
     * @var null|int
     */
    private $_totalCount;

    public function __construct(Search $search, $orderBy)
    {
        $this->search = clone $search;
        $this->apply(null);

        parent::__construct([], $orderBy);
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        if (null === $this->_totalCount) {
            $this->_totalCount = $this->search->count();
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

    /**
     * {@inheritdoc}
     */
    protected function getObjects(): array
    {
        $search = clone $this->search;

        $sort = [];
        foreach ($this->orderBy as list($field, $direction)) {
            $sort[$field] = $direction;
        }

        $query = new Query\BoolQuery();
        $searchQuery = $search->getQuery();
        if ($searchQuery->hasParam('query')) {
            $query->addFilter($searchQuery->getQuery());
        }

        $limit = $this->pageSize;
        if (null !== $this->token) {
            $limit += $this->token->getOffset();

            $mainOrder = $this->orderBy[0];
            $direction = Orderings::SORT_ASC === $mainOrder[1] ? 'gte' : 'lte';

            $query->addFilter(new Query\Range($mainOrder[0], [$direction => $this->token->getTimestamp()]));
        }

        $search
            ->setQuery($query)
            ->setSort($sort)
            ->setLimit($limit)
        ;

        return $search->execute();
    }
}