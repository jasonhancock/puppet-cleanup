<?php

// MySQL database connection parameters
$db_host          = 'localhost';
$db_user          = 'username';
$db_pass          = 'password';
$db_name          = 'puppet';

// This is the directory where non cloud hosts are stored (assumed each file in
// the directory is the FQDN of a host. If not using this feature, set to a non
// existant directory like /foo/bar
$hosts_dir        = '/foo/bar';

// The path to the puppetstoredconfigclean.rb script on your system
$puppet_rm_script = '/usr/share/puppet/ext/puppetstoredconfigclean.rb';

// CloudStack API connection parameters
$cloud_api        = 'http://example.com:8080/client/api';
$cloud_api_key    = 'API KEY';
$cloud_api_secret = 'API SECRET';

// What timezone to log timestamps in
$timezone         = 'America/Los_Angeles';
