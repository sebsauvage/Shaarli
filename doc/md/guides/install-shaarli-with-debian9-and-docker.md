_Last updated on 2018-07-01._

## Goals
- Getting a Virtual Private Server (VPS)
- Running Shaarli:
    - as a Docker container,
    - using the Træfik reverse proxy,
    - securized with TLS certificates from Let's Encrypt.


The following components and tools will be used:

- [Debian](https://www.debian.org/), a GNU/Linux distribution widely used in
  server environments;
- [Docker](https://docs.docker.com/engine/docker-overview/), an open platform
  for developing, shipping, and running applications;
- [Docker Compose](https://docs.docker.com/compose/), a tool for defining and
  running multi-container Docker applications.


More information can be found in the [Resources](#resources) section at the
bottom of the guide.

## Getting a Virtual Private Server
For this guide, I went for the smallest VPS available from DigitalOcean,
a Droplet with 1 CPU, 1 GiB RAM and 25 GiB SSD storage, which costs
$5/month ($0.007/hour):

- [Droplets Overview](https://www.digitalocean.com/docs/droplets/overview/)
- [Pricing](https://www.digitalocean.com/pricing/)
- [How to Create a Droplet from the DigitalOcean Control Panel](https://www.digitalocean.com/docs/droplets/how-to/create/)
- [How to Add SSH Keys to Droplets](https://www.digitalocean.com/docs/droplets/how-to/add-ssh-keys/)
- [Initial Server Setup with Debian 8](https://www.digitalocean.com/community/tutorials/initial-server-setup-with-debian-8) (also applies to Debian 9)
- [An Introduction to Securing your Linux VPS](https://www.digitalocean.com/community/tutorials/an-introduction-to-securing-your-linux-vps)

### Creating a Droplet
Select `Debian 9` as the Droplet distribution:

<img src="../images/01-create-droplet-distro.jpg"
     width="500px"
     alt="Droplet distribution" />

Choose a region that is geographically close to you:

<img src="../images/02-create-droplet-region.jpg"
     width="500px"
     alt="Droplet region" />

Choose a Droplet size that corresponds to your usage and budget:

<img src="../images/03-create-droplet-size.jpg"
     width="500px"
     alt="Droplet size" />

Finalize the Droplet creation:

<img src="../images/04-finalize.jpg"
     width="500px"
     alt="Droplet finalization" />

Droplet information is displayed on the Control Panel:

<img src="../images/05-droplet.jpg"
     width="500px"
     alt="Droplet summary" />

Once your VPS has been created, you will receive an e-mail with connection
instructions.

## Obtaining a domain name
After creating your VPS, it will be reachable using its IP address; some hosting
providers also create a DNS record, e.g. `ns4853142.ip-01-47-127.eu`.

A domain name (DNS record) is required to obtain a certificate and setup HTTPS
(HTTP with TLS encryption).

Domain names can be obtained from registrars through hosting providers such as
[Gandi](https://www.gandi.net/en/domain).

Once you have your own domain, you need to create a new DNS record that points
to your VPS' IP address:

<img src="../images/06-domain.jpg"
     width="650px"
     alt="Domain configuration" />

## Host setup
Now's the time to connect to your freshly created VPS!

```shell
$ ssh root@188.166.85.8

Linux stretch-shaarli-02 4.9.0-6-amd64 #1 SMP Debian 4.9.88-1+deb9u1 (2018-05-07) x86_64

The programs included with the Debian GNU/Linux system are free software;
the exact distribution terms for each program are described in the
individual files in /usr/share/doc/*/copyright.

Debian GNU/Linux comes with ABSOLUTELY NO WARRANTY, to the extent
permitted by applicable law.
Last login: Sun Jul  1 11:20:18 2018 from <REDACTED>

root@stretch-shaarli-02:~$
```

### Updating the system
```shell
root@stretch-shaarli-02:~$ apt update && apt upgrade -y
```

### Setting up Docker
_The following instructions are from the
[Get Docker CE for Debian](https://docs.docker.com/install/linux/docker-ce/debian/)
guide._

Install package dependencies:

```shell
root@stretch-shaarli-02:~$ apt install -y apt-transport-https ca-certificates curl gnupg2 software-properties-common
```

Add Docker's package repository GPG key:

```shell
root@stretch-shaarli-02:~$ curl -fsSL https://download.docker.com/linux/debian/gpg | sudo apt-key add -
```

Add Docker's package repository:

```shell
root@stretch-shaarli-02:~$ add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/debian stretch stable"
```

Update package lists and install Docker:

```shell
root@stretch-shaarli-02:~$ apt update && apt install -y docker-ce
```

Verify Docker is properly configured by running the `hello-world` image:

```shell
root@stretch-shaarli-02:~$ docker run hello-world
```

### Setting up Docker Compose
_The following instructions are from the
[Install Docker Compose](https://docs.docker.com/compose/install/)
guide._

Download the current version from the release page:

```shell
root@stretch-shaarli-02:~$ curl -L https://github.com/docker/compose/releases/download/1.21.2/docker-compose-$(uname -s)-$(uname -m) -o /usr/local/bin/docker-compose
root@stretch-shaarli-02:~$ chmod +x /usr/local/bin/docker-compose
```

## Running Shaarli
Shaarli comes with a configuration file for Docker Compose, that will setup:

- a local Docker network
- a Docker [volume](https://docs.docker.com/storage/volumes/) to store Shaarli data
- a Docker [volume](https://docs.docker.com/storage/volumes/) to store Træfik TLS configuration and certificates
- a [Shaarli](https://hub.docker.com/r/shaarli/shaarli/) instance
- a [Træfik](https://hub.docker.com/_/traefik/) instance

[Træfik](https://docs.traefik.io/) is a modern HTTP reverse proxy, with native
support for Docker and [Let's Encrypt](https://letsencrypt.org/).

### Compose configuration
Create a new directory to store the configuration:

```shell
root@stretch-shaarli-02:~$ mkdir shaarli && cd shaarli
root@stretch-shaarli-02:~/shaarli$
```

Download the current version of Shaarli's `docker-compose.yml`:

```shell
root@stretch-shaarli-02:~/shaarli$ curl -L https://raw.githubusercontent.com/shaarli/Shaarli/master/docker-compose.yml -o docker-compose.yml
```

Create the `.env` file and fill in your VPS and domain information (replace
`<MY_SHAARLI_DOMAIN>` and `<MY_CONTACT_EMAIL>` with your actual information):

```shell
root@stretch-shaarli-02:~/shaarli$ vim .env
```

```shell
SHAARLI_VIRTUAL_HOST=<MY_SHAARLI_DOMAIN>
SHAARLI_LETSENCRYPT_EMAIL=<MY_CONTACT_EMAIL>
```

### Pull the Docker images
```shell
root@stretch-shaarli-02:~/shaarli$ docker-compose pull
Pulling shaarli ... done
Pulling traefik ... done
```

### Run!
```shell
root@stretch-shaarli-02:~/shaarli$ docker-compose up -d
Creating network "shaarli_http-proxy" with the default driver
Creating volume "shaarli_traefik-acme" with default driver
Creating volume "shaarli_shaarli-data" with default driver
Creating shaarli_shaarli_1 ... done
Creating shaarli_traefik_1 ... done
```

## Conclusion
Congratulations! Your Shaarli instance should be up and running, and available
at `https://<MY_SHAARLI_DOMAIN>`.

<img src="../images/07-installation.jpg"
     width="500px"
     alt="Shaarli installation page" />

## Resources
### Related Shaarli documentation
- [Docker 101](../docker/docker-101.md)
- [Shaarli images](../docker/shaarli-images.md)

### Hosting providers
- [DigitalOcean](https://www.digitalocean.com/)
- [Gandi](https://www.gandi.net/en)
- [OVH](https://www.ovh.co.uk/)
- [RackSpace](https://www.rackspace.com/)
- etc.

### Domain Names and Registrars
- [Introduction to the Domain Name System (DNS)](https://opensource.com/article/17/4/introduction-domain-name-system-dns)
- [ICANN](https://www.icann.org/)
- [Domain name registrar](https://en.wikipedia.org/wiki/Domain_name_registrar)
- [OVH Domain Registration](https://www.ovh.co.uk/domains/)
- [Gandi Domain Registration](https://www.gandi.net/en/domain)

### HTTPS and Security
- [Transport Layer Security](https://en.wikipedia.org/wiki/Transport_Layer_Security)
- [Let's Encrypt](https://letsencrypt.org/)

### Docker
- [Docker Overview](https://docs.docker.com/engine/docker-overview/)
- [Docker Documentation](https://docs.docker.com/)
- [Get Docker CE for Debian](https://docs.docker.com/install/linux/docker-ce/debian/)
- [docker logs](https://docs.docker.com/engine/reference/commandline/logs/)
- [Volumes](https://docs.docker.com/storage/volumes/)
- [Install Docker Compose](https://docs.docker.com/compose/install/)
- [docker-compose logs](https://docs.docker.com/compose/reference/logs/)

### Træfik
- [Getting Started](https://docs.traefik.io/)
- [Docker backend](https://docs.traefik.io/configuration/backends/docker/)
- [Let's Encrypt and Docker](https://docs.traefik.io/user-guide/docker-and-lets-encrypt/)
- [traefik](https://hub.docker.com/_/traefik/) Docker image
