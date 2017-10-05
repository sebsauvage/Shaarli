## Basics
Install [Docker](https://www.docker.com/), by following the instructions relevant
to your OS / distribution, and start the service.

### Search an image on [DockerHub](https://hub.docker.com/)

```bash
$ docker search debian

NAME            DESCRIPTION                                     STARS   OFFICIAL   AUTOMATED
ubuntu          Ubuntu is a Debian-based Linux operating s...   2065    [OK]
debian          Debian is a Linux distribution that's comp...   603     [OK]
google/debian                                                   47                 [OK]
```

### Show available tags for a repository
```bash
$ curl https://index.docker.io/v1/repositories/debian/tags | python -m json.tool

% Total    % Received % Xferd  Average Speed   Time    Time     Time  Current
Dload  Upload   Total   Spent    Left  Speed
100  1283    0  1283    0     0    433      0 --:--:--  0:00:02 --:--:--   433
```

Sample output:
```json
[
    {
        "layer": "85a02782",
        "name": "stretch"
    },
    {
        "layer": "59abecbc",
        "name": "testing"
    },
    {
        "layer": "bf0fd686",
        "name": "unstable"
    },
    {
        "layer": "60c52dbe",
        "name": "wheezy"
    },
    {
        "layer": "c5b806fe",
        "name": "wheezy-backports"
    }
]

```

### Pull an image from DockerHub
```bash
$ docker pull repository[:tag]

$ docker pull debian:wheezy
wheezy: Pulling from debian
4c8cbfd2973e: Pull complete
60c52dbe9d91: Pull complete
Digest: sha256:c584131da2ac1948aa3e66468a4424b6aea2f33acba7cec0b631bdb56254c4fe
Status: Downloaded newer image for debian:wheezy
```

Docker re-uses layers already downloaded. In other words if you have images based on Alpine or some Ubuntu version for example, those can share disk space.

### Start a container
A container is an instance created from an image, that can be run and that keeps running until its main process exits. Or until the user stops the container. 

The simplest way to start a container from image is ``docker run``. It also pulls the image for you if it is not locally available. For more advanced use, refer to ``docker create``.

Stopped containers are not destroyed, unless you specify ``--rm``. To view all created, running and stopped containers, enter:
```bash
$ docker ps -a
```

Some containers may be designed or configured to be restarted, others are not. Also remember both network ports and volumes of a container are created on start, and not editable later.

### Access a running container
A running container is accessible using ``docker exec``, or ``docker copy``. You can use ``exec`` to start a root shell in the Shaarli container:
```bash
$ docker exec -ti <container-name-or-id> bash
```
Note the names and ID's of containers are listed in ``docker ps``. You can even type only one or two letters of the ID, given they are unique.

Access can also be through one or more network ports, or disk volumes. Both are specified on and fixed on ``docker create`` or ``run``.

You can view the console output of the main container process too:
```bash
$ docker logs -f <container-name-or-id>
```

### Docker disk use
Trying out different images can fill some gigabytes of disk quickly. Besides images, the docker volumes usually take up most disk space.

If you care only about trying out docker and not about what is running or saved, the following commands should help you out quickly if you run low on disk space:

```bash
$ docker rmi -f $(docker images -aq) # remove or mark all images for disposal
$ docker volume rm $(docker volume ls -q) # remove all volumes
```

### Systemd config
Systemd is the process manager of choice on Debian-based distributions. Once you have a ``docker`` service installed, you can use the following steps to set up Shaarli to run on system start.

```bash
systemctl enable /etc/systemd/system/docker.shaarli.service
systemctl start docker.shaarli
systemctl status docker.*
journalctl -f # inspect system log if needed
```

You will need sudo or a root terminal to perform some or all of the steps above. Here are the contents for the service file:
```
[Unit]
Description=Shaarli Bookmark Manager Container
After=docker.service
Requires=docker.service


[Service]
Restart=always

# Put any environment you want in an included file, like $host- or $domainname in this example
EnvironmentFile=/etc/sysconfig/box-environment

# It's just an example..
ExecStart=/usr/bin/docker run \
  -p 28010:80 \
  --name ${hostname}-shaarli \
  --hostname shaarli.${domainname} \
  -v /srv/docker-volumes-local/shaarli-data:/var/www/shaarli/data:rw \
  -v /etc/localtime:/etc/localtime:ro \
  shaarli/shaarli:latest

ExecStop=/usr/bin/docker rm -f ${hostname}-shaarli


[Install]
WantedBy=multi-user.target
```
