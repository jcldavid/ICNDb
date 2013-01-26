<?php

namespace ICNDb;

class Client {

	private static $baseURL = 'http://api.icndb.com/';

	/**
	 * @var array
	 */
	private $config = array();

	/**
	 * @var string
	 */
	private $uri = '';

	/**
	 * @var array
	 */
	private $response = array();

	/**
	 * Either specific, random, categories, count
	 * There can only be one
	 * @var array
	 */
	private $method = array();


	/**
	 * list of excluded categories
	 * @var array
	 */
	private $exclude = array();


	/**
	 * list of categories jokes are limited to
	 * @var array
	 */
	private $limitTo = array();


	/**
	 * @param array $config Set character's name
	 */
	public function __construct($config = array())
	{
		if (empty($config)) {
			$this->config = array(
				'firstName' => '', // Defaults to Chuck if provided empty
				'lastName' => '', // Defaults to Norris if provided empty
			);
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

		curl_setopt_array($ch, array(
			// CURLOPT_CONNECTTIMEOUT => 1,
			// CURLOPT_TIMEOUT => 5,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true
		));

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
		$url = static::$baseURL.$this->uri;

		if ( ! empty($this->exclude)) {
			$url .= '?exclude=['.implode(',', $this->exclude).']';
		}elseif ( ! empty($this->limitTo)) {
			$url .= '?limitTo=['.implode(',', $this->limitTo).']';
		}

		return $url;
	}

	/**
	 * Empty some attributes for the next request
	 * @return void
	 */
	private function cleanUp()
	{
		$this->method = array();
		$this->exclude = array();
		$this->limitTo = array();
	}

}

class APIUnavailableException extends \Exception {}
