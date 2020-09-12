## Backup and restore

All data and [configuration](Shaarli-configuration.md) is kept in the `data` directory. Backup this directory: 

```bash
rsync -avzP my.server.com:/var/www/shaarli.mydomain.org/data ~/backups/shaarli-data-$(date +%Y-%m-%d_%H%M)
```

It is strongly recommended to do periodic, automatic backups to a seperate machine. You can automate the command above using a cron job or full-featured backup solutions such as [rsnapshot](https://rsnapshot.org/)

To restore a backup, simply put back the `data/` directory in place, owerwriting any existing files.