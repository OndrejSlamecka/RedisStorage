#!/bin/bash

wget https://github.com/nicolasff/phpredis/tarball/2.2.1
tar -xzf 2.2.1
sh -c "cd nicolasff-phpredis-250e81b && phpize && ./configure && sudo make install"
echo "extension=redis.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`
exit 0