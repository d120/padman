# padman - etherpad-lite management front-end

To install, git clone this repository into your www root. Rename config.inc.php.template 
to config.inc.php and change the configuration appropriately.

## Server configuration

The web server must redirect all requests to index.php. For example, with nginx:

```
        location / {
                try_files $uri $uri/ /index.php?$args;
        }
```

Or with Apache:

```
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]
```

## Links

* [etherpad-lite](https://github.com/ether/etherpad-lite/)
* [Etherpad HTTP API](http://etherpad.org/doc/v1.4.1/#index_http_api)

## Screenshot

![Screenshot](http://u.dropme.de/5165/61a34f/Bildschirmfoto-2014-12-04-um-15.26.02.png)


## License

Copyright (c) 2014-2015 Max Weller, Johannes Lauinger, Jannik Vieten

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


