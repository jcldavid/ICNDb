<?php

namespace ICNDb;


class ICNDbTest extends \PHPUnit_Framework_TestCase {

	public function setUp()
	{
		$this->wrapper = new Client();
	}

	public function testCanSetCustomConfig()
	{
		$config = array(
			'firstName' => 'John',
			'lastName' => 'Doe'
		);

		$wrapper = new Client($config);

		$this->assertAttributeEquals($config, 'config', $wrapper);
	}

	public function testSetsCorrectUriForCategories()
	{
		$this->wrapper->categories();
		$this->assertAttributeEquals('categories', 'uri', $this->wrapper);
	}

	public function testSetsCorrectUriForRandomJoke()
	{
		$this->wrapper->random();
		$this->assertAttributeEquals('jokes/random/1', 'uri', $this->wrapper);

		$this->wrapper->random(2);
		$this->assertAttributeEquals('jokes/random/2', 'uri', $this->wrapper);
	}

	public function testSetsCorrectUriForSpecificJoke()
	{
		$this->wrapper->specific(10);
		$this->assertAttributeEquals('jokes/10', 'uri', $this->wrapper);
	}

	/**
	 * @expectedException ICNDb\ChainNotAllowedException
	 */
	public function testShouldNotChainSpecificAndRandom()
	{
		/**
		 * You can't get a random, and a specific joke all at the same time
		 */
		$this->wrapper->random()->specific(1)->get();
	}

	/**
	 * @expectedException ICNDb\ChainNotAllowedException
	 */
	public function testShouldNotChainJokeAndCategories()
	{
		/**
		 * You can't get a random, and a specific joke all at the same time
		 */
		$this->wrapper->specific(1)->categories()->get();
	}
}