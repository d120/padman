# padman - etherpad-lite management front-end

To install:
* git clone this repository into an appropriate folder (e.g. /var/www/padman).
* Configure your web server according to the section "Server configuration" below.
* Initialize the database according to the "Database" section below.
* Rename config.inc.php.template to config.inc.php and change the configuration appropriately.
* Set up a cron job to run `bash -c 'cd /var/www/padman && php indexer.php'` regularly (at least every hour).
  
  ```
  echo '#!/bin/bash' > /etc/cron.hourly/padman-indexer
  echo 'sudo -u www-data bash -c \'cd /var/www/padman && php indexer.php\'' >> /etc/cron.hourly/padman-indexer
  ```

## Server configuration

The web server must redirect all requests to index.php. Additionally, a htaccess
authentication is required if only registered users should be able to create new
pads. (This is the default configuration, see "ALLOW_ANON_PAD_CREATE" in config.php.)

A configuration with authentication for nginx could look like:

```
location / {
  auth_basic "Padman";
  auth_basic_user_file /path/to/the/pad/.htpasswd;

  try_files $uri $uri/ /index.php?$args;
}
```

And for Apache:

```
Alias /padman /var/www/padman/private
Alias /pp /var/www/padman/public

<Directory /var/www/padman/private>
AuthType Basic
AuthName "Padman"
AuthUserFile /var/www/padman/.htpasswd
require valid-user

RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ /padman/index.php?group=$1 [L]

</Directory>
<Directory /var/www/padman/public>
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ /pp/redirect.php?lnk=$1 [L]

</Directory>
```


## Database

The most recent version of Padman doesn't rely on the group and pad lists provided
by the etherpad-lite api as they are quite slow. Therefore, a few database tables are
required to store groups, pads and per-user configuration.

Create a new mysql database on your server or use an existing one (all tables are 
prefixed `padman_`). Import the file `install.sql` into the database:

    mysql MY_DATABASE < install.sql

To create new groups, use the padmanctl.php command line tool:

    php padmanctl.php -N -a <group_alias> -t <menu_title> -m <group_mapper> -p <position>

You need to fill in the parameters as described below:

| Column      | Description                                           |
|-------------|-------------------------------------------------------|
| group_alias | A short name for the group. Visible in the URL. Must only consist of letters, numbers, underscores. Difficult to change.  |
| menu_title  | The menu path for the group. Create sub-menus by using slashes (e.g. menu_title = "Main group/Sub group/Subsubgroup"). Can be changed any time. |
| group_mapper | Internal group name for etherpad-lite. Not visible to the user. Many groups can share the same group_mapper. See the section "Group Sessions" below. |
| position    | The menu will be sorted by this column. |

If you modify the groups manually in the MySQL database, go to the padman folder and run the below command to rehash the group database:

    php padmanctl.php -R

More commands of the padmanctl.php tool can be found by calling `php padmanctl.php -h`.

If this does not work, you probably did not correctly fill in the API_KEY and API_URL fields in  config.inc.php,
or your etherpad-lite is not running.


## Group Sessions

When a user opens padman for the first time, or after his session has expired, padman needs to
ask etherpad-lite to generate session ids. For every group_mapper, a different session id is generated.
The generation of one session id takes about a third of a second. So if you have many different 
group_mappers, you/your users will have to wait quite a long time before they can use padman.

Usually, you can just use one group_mapper for all groups (e.g. just put "padman" in the 
column "group_mapper" for all groups).

But if you want to have group-wise access control, you need to set different group_mappers for
the groups different users should be allowed to access.

Example:

User A may see group 1,2,3,4. User B may see group 1,2. 

Then you could set up these group_mappers:

| group_alias | group_mapper |
|-------------|--------------|
| group1      | somegroups    |
| group2      | somegroups    |
| group3      | secretgroups  |
| group4      | secretgroups  |


## Search function


* `&` search for groups, e.g. `&mygroup`
* `#` search for tags, e.g. `#mytag`
* `!` negation, e.g. `!#notthistag`

## Links

* [etherpad-lite](https://github.com/ether/etherpad-lite/)
* [Etherpad HTTP API](http://etherpad.org/doc/v1.4.1/#index_http_api)

## Screenshot

![Screenshot](http://u.dropme.de/5165/61a34f/Bildschirmfoto-2014-12-04-um-15.26.02.png)


## License

Copyright (c) 2014-2016 Max Weller, Johannes Lauinger, Jannik Vieten

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.


