all: explorer.phar
	
explorer.phar: explorer.glade *.php
	/opt/php/5.3/bin/php -d phar.readonly=0 /opt/php/5.3/bin/phar pack -f explorer.phar -s explorer.php explorer.glade *.php

