RedisStorage for Nette Framework [![Build Status](https://secure.travis-ci.org/OndrejSlamecka/RedisStorage.png?branch=master)](http://travis-ci.org/OndrejSlamecka/RedisStorage)
===========================

Built on [phpredis](https://github.com/nicolasff/phpredis) extension and [Nette Framework](https://github.com/nette/nette).

Setup
-----

In your config.neon:

	services:
		redis:
			class: Redis
			setup:
				- connect('localhost', 6379)
		cacheStorage: Slamecka\RedisStorage