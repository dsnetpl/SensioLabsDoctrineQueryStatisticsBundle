<?php

namespace SensioLabs\DoctrineQueryStatisticsBundle\Analyzer;

class Query
{
    private $queries = array();

    public function addQuery($sql, $parameters = array())
    {
        $this->queries[] = array(
            'sql' => $sql,
            'parameters' => $parameters
        );
    }

    public function getQueryCount()
    {
        return count($this->queries);
    }

    public function getIdenticalQueries()
    {
        $groupedQueries = array();
        foreach($this->queries as $query)
        {
            $queryKey = $this->generateQueryKeyWithParameters($query['sql'], $query['parameters']);
            $groupedQueries[$queryKey][] = $query;
        }

        return $this->filterIndistinctQueries($groupedQueries);
    }

    public function getSimilarQueries(array $excludedQueries)
    {
        $groupedQueries = array();
        foreach($this->queries as $query)
        {
            $queryKey = $this->generateQueryKeyWithoutParameters($query['sql']);
            $groupedQueries[$queryKey][] = $query;
        }

        return $this->filterIndistinctQueries($groupedQueries, $excludedQueries);
    }

    private function generateQueryKeyWithParameters($sql, $parameters)
    {
        $key = $this->generateQueryKeyWithoutParameters($sql);
        if (is_array($parameters)) {
            $key .= ':' . sha1(serialize($parameters));
        }

        return $key;
    }

    private function generateQueryKeyWithoutParameters($sql)
    {
        return sha1($sql);
    }

    private function filterIndistinctQueries(array $allQueries, array $excludedQueries = [])
    {
        $excludedQueriesSQLs = array_map(
            function ($query) {
                return $query['sql'];
            },
            $excludedQueries
        );
        $indistinctQueries = array();
        foreach($allQueries as $queryKey => $queries)
        {
            if (count($queries) > 1 && !in_array($queries[0]['sql'], $excludedQueriesSQLs))
            {
                $indistinctQueries[$queryKey] = array(
                    'sql' => $queries[0]['sql'],
                    'count' => count($queries),
                    'parameters' => $queries[0]['parameters']
                );
            }
        }
        return $indistinctQueries;
    }
}