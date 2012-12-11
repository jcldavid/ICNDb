# icndb/icndb

A PHP Wrapper for for the [Internet Chuck Norris Database (ICNDb)](http://www.icndb.com).

# Install

To ease the process, I recommend you use [composer](http://getcomposer.org/).

```
{
	"require": {
		"icndb/icndb": "dev-master"
	}
}
```

# Usage

```
$config = array(
	'firstName' => 'Cyrus',
	'lastName' => 'David'
);

// Pass a parameter to the constructor the change the firstName and lastName
// Default is _Chuck Norris_
$chuck = new ICNDb\Client($config);

// Get the total Chuck Norris jokes stored in ICNDb
$total = $chuck->count()->get();
echo 'Total jokes: '."$total \n";

// Get all categories
$categories = $chuck->categories()->get();
echo 'Chuck Norris categories: '.implode(', ', $categories)."\n";

// Get a specific joke by it's ID
$specific = $chuck->specific(18)->get();
echo 'Joke no. 18: '.html_entity_decode($specific->joke)."\n";

//Get a random joke
$random = $chuck->random()->get();
echo 'Random joke no. '.$random[0]->id.': '.$random[0]->joke."\n";

// Get multiple random jokes
$random2 = $chuck->random(3)->get();
foreach ($random2 as $r) {
	echo 'Random joke no. '.$r->id.': '.$r->joke."\n";
}

// use exclude() to get jokes not belong to that category
$exclude = $chuck->random()->exclude('nerdy')->get();
echo 'Random joke no. '.$exclude[0]->id.': '.$exclude[0]->joke."\n";

// you can also supply an array
$exclude2 = $chuck->random()->exclude(array('nerdy', 'explicit'))->get();

// or chain them
$exclude3 = $chuck->random(2)->exclude('explicit')->exclude('nerdy')->get();

// use limitTo() to get jokes only from that category
// you may supply an array or chain them like exclude()
$limit = $chuck->random()->limitTo('nerdy')->get();
```

# Exceptions

**APIUnavailableException** - API is either unreachable/unavailable
**ChainNotAllowedException** - When these methods are chained together `random()`, `specific($id)`, `categories()`, `count()`


# Tests

To run the unit test suite:

```
cd tests
curl -s https://getcomposer.org/installer | php
php composer.phar install
./vendor/bin/phpunit
```