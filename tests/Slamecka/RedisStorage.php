<?php
/**
 * RedisStorage for Nette Framework cache.
 *
 * @author Ondrej Slamecka, www.slamecka.cz
 * @copyright 2012 Ondrej Slamecka
 * @license BSD-3
 */

use Slamecka\RedisStorage,
	Nette\Caching\Cache;

require __DIR__ . '/../../vendor/nette/nette/Nette/loader.php';
require __DIR__ . '/../../libs/Slamecka/RedisStorage.php';

/* TESTING FUNCTIONS */
function fail($func)
{
	$trace = debug_backtrace();
	$trace = end($trace);
	echo "Failed $func on line " . $trace['line'] . "\n";
	exit(254);
}

function assertTrue($val)
{
	if ($val !== TRUE) {
		fail(__FUNCTION__);
	}
}

function assertFalse($val)
{
	if ($val !== FALSE) {
		fail(__FUNCTION__);
	}
}

/* INIT */

if (!RedisStorage::isAvailable()) {
	exit("Missing extension Redis\n");
}

$value = 'rulez';
$cache = new Cache(new RedisStorage());


/* --- CALLBACKS --- */

$key = 'callbacks-key';

function dependency($val)
{
	return $val;
}

// Writing cache...
$cache->save($key, $value, array(
	Cache::CALLBACKS => array(array('dependency', 1)),
));

assertTrue(isset($cache[$key]));

// Writing cache...
$cache->save($key, $value, array(
	Cache::CALLBACKS => array(array('dependency', 0)),
));

assertFalse(isset($cache[$key]));

/* --- EXPIRATION --- */

$key = 'expiration-key';

// Writing cache...
$cache->save($key, $value, array(
	Cache::EXPIRATION => time() + 3,
));


// Sleeping 1 second
sleep(1);
assertTrue(isset($cache[$key]));


// Sleeping 3 seconds
sleep(3);
assertFalse(isset($cache[$key]));

/* --- EXPIRATION --- */

$key = 'items-key';

// Writing cache...
$cache->save($key, $value, array(
	Cache::ITEMS => array('dependent'),
));

assertTrue(isset($cache[$key]));


// Modifing dependent cached item
$cache['dependent'] = 'hello world';

assertFalse(isset($cache[$key]));


// Writing cache...
$cache->save($key, $value, array(
	Cache::ITEMS => 'dependent',
));

assertTrue(isset($cache[$key]));


// Modifing dependent cached item
sleep(2);
$cache['dependent'] = 'hello europe';

assertFalse(isset($cache[$key]));


// Writing cache...
$cache->save($key, $value, array(
	Cache::ITEMS => 'dependent',
));

assertTrue(isset($cache[$key]), 'Is cached?');


// Deleting dependent cached item
$cache['dependent'] = NULL;

assertFalse(isset($cache[$key]));


/* --- PRIORITY --- */

$key = 'priority-key';

// Writing cache...
$cache->save('nette-priority-key1', 'value1', array(
	Cache::PRIORITY => 100,
));

$cache->save('nette-priority-key2', 'value2', array(
	Cache::PRIORITY => 200,
));

$cache->save('nette-priority-key3', 'value3', array(
	Cache::PRIORITY => 300,
));

$cache['nette-priority-key4'] = 'value4';


// Cleaning by priority...
$cache->clean(array(
	Cache::PRIORITY => '200',
));

assertFalse(isset($cache['nette-priority-key1']));
assertFalse(isset($cache['nette-priority-key2']));
assertTrue(isset($cache['nette-priority-key3']));
assertTrue(isset($cache['nette-priority-key4']));


/* --- SLIDING --- */

$key = 'sliding-key';

// Writing cache...
$cache->save($key, $value, array(
	Cache::EXPIRATION => time() + 2,
	Cache::SLIDING => TRUE,
));


for($i = 0; $i < 3; $i++) {
	// Sleeping 1 second
	sleep(1);
	assertTrue(isset($cache[$key]));
}

// Sleeping few seconds...
sleep(3);

assertFalse(isset($cache[$key]));

/* --- TAGS --- */

$key = 'tags-key';

// Writing cache...
$cache->save('nette-tags-key1', 'value1', array(
	Cache::TAGS => array('one', 'two'),
));

$cache->save('nette-tags-key2', 'value2', array(
	Cache::TAGS => array('one', 'three'),
));

$cache->save('nette-tags-key3', 'value3', array(
	Cache::TAGS => array('two', 'three'),
));

$cache['nette-tags-key4'] = 'value4';


// Cleaning by tags...
$cache->clean(array(
	Cache::TAGS => 'one',
));

assertFalse(isset($cache['nette-tags-key1']));
assertFalse(isset($cache['nette-tags-key2']));
assertTrue(isset($cache['nette-tags-key3']));
assertTrue(isset($cache['nette-tags-key4']));
