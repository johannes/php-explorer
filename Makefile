PHP_PATH=/opt/php/5.3/bin
all: explorer.phar
	
explorer.phar: explorer.glade *.php
	${PHP_PATH}/php -d phar.readonly=0 ${PHP_PATH}/phar pack -f explorer.phar -s explorer.php explorer.glade *.php index.html

