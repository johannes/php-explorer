PHP_PATH=/opt/php/5.3/bin

all: build-explorer.phar
	
build-explorer.phar:
	(cd src; find * -type f | xargs ${PHP_PATH}/php -d phar.readonly=0 ${PHP_PATH}/phar pack -f ../explorer.phar -s explorer.php )
	chmod a+x explorer.phar

explorer.phar: build-explorer.phar

run: build-explorer.phar
	${PHP_PATH}/php ./explorer.phar
	rm explorer.phar
