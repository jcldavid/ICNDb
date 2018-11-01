<?php

namespace Swapnilsarwe;

class Client
{
    private static $baseURL = 'https://api.icndb.com/';

    /**
     * @var array
     */
    private $config = [];

    /**
     * @var string
     */
    private $uri = '';

    /**
     * @var array
     */
    private $response = [];

    /**
     * Either specific, random, categories, count
     * There can only be one
     * @var array
     */
    private $method = [];


    /**
     * list of excluded categories
     * @var array
     */
    private $exclude = [];


    /**
     * list of categories jokes are limited to
     * @var array
     */
    private $limitTo = [];


    /**
     * @param array $config Set character's name
     */
    public function __construct($config = [])
    {
        if (empty($config)) {
            $this->config = [
                'firstName' => '', // Defaults to Chuck if provided empty
                'lastName' => '', // Defaults to Norris if provided empty
            ];
        } else {
            $this->config = $config;
        }
    }

    /**
     * sets the method to get a single/multiple random ICNDb quote(s)
     * @param  int $count number of random quotes to fetch
     * @return object Client
     */
    public function random($count = 1)
    {
        $this->uri = "jokes/random/$count";
        $this->method[] = 'random';

        return $this;
    }

    /**
     * sets the method to get a specific ICNDb quote
     * @param  int $id id of the quote
     * @return object Client
     */
    public function specific($id)
    {
        $this->uri = "jokes/$id";
        $this->method[] = 'specific';

        return $this;
    }

    /**
     * sets the method to get the categories
     * @return object Client
     */
    public function categories()
    {
        $this->uri = 'categories';
        $this->method[] = 'categories';

        return $this;
    }
    /**
     * Exclude a category/categories
     * @param  mixed $category category/categories to be excldued
     * @return object Client
     */
    public function exclude($category)
    {
        if (is_array($category)) {
            $this->exclude = $category;
        } else {
            $this->exclude[] = $category;
        }

        return $this;
    }

    /**
     * limit jokes to a category/categories
     * @param  mixed $category category/categories to be limitedTo
     * @return object Client
     */
    public function limitTo($category)
    {
        if (is_array($category)) {
            $this->limitTo = $category;
        } else {
            $this->limitTo[] = $category;
        }

        return $this;
    }

    /**
     * sets the method to get the total no. of jokes
     * @return object Client
     */
    public function count()
    {
        $this->uri = 'jokes/count';
        $this->method[] = 'count';

        return $this;
    }

    /**
     * Does the execution of request
     * @return json the response value
     */
    public function get()
    {
        if (count($this->method) > 1) {
            throw new \LogicException('Cannot use [' . implode(', ', $this->method) . '] at the same time.');
        }
        $ch = curl_init($this->getURL());

        curl_setopt_array($ch, [
            // CURLOPT_CONNECTTIMEOUT => 1,
            // CURLOPT_TIMEOUT => 5,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
        ]);

        $response = curl_exec($ch);

        $this->response['status'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($this->response['status'] != 200 or $response == '') {
            throw new APIUnavailableException('API Unreachable');
        }

        // escaped characters
        $response = str_replace('\\', '', $response);

        $this->response['body'] = json_decode($response);

        if ($this->response['body']->type != 'success') {
            throw new APIUnavailableException('API Failed');
        }

        $this->cleanUp();

        return $this->response['body']->value;
    }

    public function first()
    {
        $results = $this->get();

        return reset($results);
    }

    /**
     * get the URL and check for includes/limitTos
     * @return String URL
     */
    private function getURL()
    {
        $queryParams = [];
        $strQueryParams = '';
        $url = static::$baseURL.$this->uri;

        if (! empty($this->exclude)) {
            $queryParams[] = 'exclude=['.implode(',', $this->exclude).']';
        }
        if (! empty($this->limitTo)) {
            $queryParams[] = 'limitTo=['.implode(',', $this->limitTo).']';
        }

        if (isset($this->config['firstName']) && ! empty($this->config['firstName'])) {
            $queryParams[] = 'firstName='.$this->config['firstName'];
        }

        if (isset($this->config['lastName']) && ! empty($this->config['lastName'])) {
            $queryParams[] = 'lastName='.$this->config['lastName'];
        }

        if (count($queryParams) > 0) {
            $strQueryParams = '?'.implode('&', $queryParams);
        }

        return $url . $strQueryParams;
    }

    /**
     * Empty some attributes for the next request
     * @return void
     */
    private function cleanUp()
    {
        $this->method = [];
        $this->exclude = [];
        $this->limitTo = [];
    }
}

class APIUnavailableException extends \Exception
{
}
