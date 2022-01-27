<?php

namespace dokuwiki\plugin\xref;

use dokuwiki\HTTP\DokuHTTPClient;

class Grok
{

    protected $baseUrl;
    protected $def;
    protected $path;

    public function __construct($reference, $baseUrl = 'https://codesearch.dokuwiki.org')
    {
        $heuristic = new Heuristics($reference);
        $this->def = $heuristic->getDef();
        $this->path = $heuristic->getPath();
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * Return the URL that leads to the search interface
     *
     * @return string
     */
    public function getSearchUrl()
    {
        if($this->def === '' && $this->path === '') return $this->baseUrl;

        $url = $this->baseUrl . '/search?';
        $param = [
            'project' => 'dokuwiki',
            'defs' => $this->def,
            'path' => $this->path,
        ];
        $url .= buildURLparams($param, '&');
        return $url;
    }

    /**
     * Return the URL that allows to query the API
     *
     * @return string
     */
    public function getAPIUrl()
    {
        $url = $this->baseUrl . '/api/v1/search?';
        $param = [
            'projects' => 'dokuwiki',
            'def' => $this->def,
            'path' => $this->path,
        ];
        $url .= buildURLparams($param, '&');
        return $url;
    }

    /**
     * Return the number of results to expect
     *
     * @return false|int false on errors
     */
    public function getResultCount()
    {
        if($this->def === '' && $this->path === '') return 0;

        $http = new DokuHTTPClient();
        $http->timeout = 5;
        $json = $http->get($this->getAPIUrl());
        if (!$json) return false;
        $data = json_decode($json, true);
        if (!$data) return false;
        if (!isset($data['resultCount'])) return false;
        return $data['resultCount'];
    }
}
