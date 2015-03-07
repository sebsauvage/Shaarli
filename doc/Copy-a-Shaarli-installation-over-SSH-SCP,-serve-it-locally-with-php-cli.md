Example bash script:

```
#!/bin/bash
#Description: Copy a Shaarli installation over SSH/SCP, serve it locally with php-cli
#Will create a local-shaarli/ directory when you run it, backup your Shaarli there, and serve it locally.
#Will NOT download linked pages. It's just a directly usable backup/copy/mirror of your Shaarli
#Requires: ssh, scp and a working SSH access to the server where your Shaarli is installed
#Usage: ./local-shaarli.sh
#Author: nodiscc (nodiscc@gmail.com)
#License: MIT (http://opensource.org/licenses/MIT)
set -o errexit
set -o nounset

##### CONFIG #################
#The port used by php's local server
php_local_port=7431

#Name of the SSH server and path where Shaarli is installed
#TODO: pass these as command-line arguments
remotehost="my.ssh.server"
remote_shaarli_dir="/var/www/shaarli"


###### FUNCTIONS #############
_main() {
	_CBSyncShaarli
	_CBServeShaarli
}

_CBSyncShaarli() {
	remote_temp_dir=$(ssh $remotehost mktemp -d)
	remote_ssh_user=$(ssh $remotehost whoami)
	ssh -t "$remotehost" sudo cp -r "$remote_shaarli_dir" "$remote_temp_dir"
	ssh -t "$remotehost" sudo chown -R "$remote_ssh_user":"$remote_ssh_user" "$remote_temp_dir"
	scp -rq "$remotehost":"$remote_temp_dir" local-shaarli
	ssh "$remotehost" rm -r "$remote_temp_dir"
}

_CBServeShaarli() {
	#TODO: allow serving a previously downloaded Shaarli
	#TODO: ask before overwriting local copy, if it exists
	cd local-shaarli/
	php -S localhost:${php_local_port}
	echo "Please go to http://localhost:${php_local_port}"
}


##### MAIN #################

_main
```

This outputs:

```
$ ./local-shaarli.sh
PHP 5.6.0RC4 Development Server started at Mon Sep  1 21:56:19 2014
Listening on http://localhost:7431
Document root is /home/user/local-shaarli/shaarli
Press Ctrl-C to quit.

[Mon Sep  1 21:56:27 2014] ::1:57868 [200]: /
[Mon Sep  1 21:56:27 2014] ::1:57869 [200]: /index.html
[Mon Sep  1 21:56:37 2014] ::1:57881 [200]: /...
```