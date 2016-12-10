<?php

namespace LeadsFactoryBundle\Entity;

/**
 *
 * LeadsFactoryBundle\Entity\SearchResult
 *
 */
class SearchResult
{

    protected $took = null;
    protected $total = null;
    protected $max_score = null;

    protected $results = array();

    /**
     * @return null
     */
    public function getTook()
    {
        return $this->took;
    }

    /**
     * @param null $took
     */
    public function setTook($took)
    {
        $this->took = $took;
    }

    /**
     * @return null
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param null $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @return null
     */
    public function getMaxScore()
    {
        return $this->max_score;
    }

    /**
     * @param null $max_score
     */
    public function setMaxScore($max_score)
    {
        $this->max_score = $max_score;
    }

    /**
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @param array $results
     */
    public function setResults($results)
    {
        $this->results = $results;
    }

    public function addResult($result)
    {
        $this->results[] = $result;
    }

    public function countResults()
    {
        return count($this->getResults());
    }


}
