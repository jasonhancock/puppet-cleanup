Description
-----------
This script will remove any hosts from Puppet's stored configurations DB if a file
with the host's FQDN doesn't exist in a specified directory OR the node has been
destroyed in CloudStack. I run this scipt from cron every few minutes.

Dependencies
------------
Requires the [cloudstack-php-client](https://github.com/jasonhancock/cloudstack-php-client)


Configuration
-------------
See cleanup_hosts_config.php. The script looks for this file in the /etc/puppet
directory, but you can simply update the 'require' statement to relocate it
anywhere you wish.

This script needs permission to do a SELECT on the "hosts" table in Puppet's
stored configurations database.


License
-------
Copyright (c) 2012 Jason Hancock <jsnbyh@gmail.com>

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is furnished
to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
