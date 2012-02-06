<?php
/*
 * This script purges hosts from the puppet stored configs database
 *
 * Copyright (c) 2012 Jason Hancock <jsnbyh@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

require_once('CloudStack/CloudStackClient.php');

// Hard-coded configuration file location...you may need to adjust for your system
require_once('/etc/puppet/cleanup_hosts_config.php');

date_default_timezone_set($timezone);

logMsg('Starting up');

/*
 * Connect to the database, read all of the host names from the db, and throw
 * them into an associative array (for easy lookups) 
 */
$dbh = mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error($dbh));
mysql_select_db($db_name) or die(mysql_error($dbh));

logMsg("Connected to DB");

$q = 'SELECT DISTINCT(name) FROM hosts'; // this query pulls all hosts
$result = mysql_query($q, $dbh) or die(mysql_error($dbh));

$hosts = array();

while($row = mysql_fetch_row($result)) {
    $hosts[$row[0]] = true;
}

mysql_free_result($result);
mysql_close($dbh);

logMsg("Checking hosts");

/* Unset the array for each host that has a file. This will leave us
 * with a list of hosts that are either cloud instances or no longer
 * in existance
 */
if (is_dir($hosts_dir) && $handle = opendir($hosts_dir)) {
    while (false !== ($file = readdir($handle))) {
        if($file == '.' || $file == '..')
            continue;
        
        if(isset($hosts[$file]))
            unset($hosts[$file]);
    }

    closedir($handle);

} else {
    logMsg("ERROR: unable to open dir $hosts_dir");
}

logMsg('Calling cloudstack API');

$cloudstack = new CloudStackClient($cloud_api, $cloud_api_key, $cloud_api_secret);

/*
 * Grab the networks. This is so we can determine the FQDN of each cloud VM
 */
$ntwks = $cloudstack->listNetworks();
$networks = array(); // Array of id->networkdomain

foreach ($ntwks as $network)
    $networks[$network->id] = $network->networkdomain;

$vms = $cloudstack->listVirtualMachines();

// Hosts in this array will NOT be deleted
$cloud_vms = array();

foreach($vms as $vm) {
    if($vm->state == 'Destroyed')
        continue; // skip....if it's in the puppet DB, we'll destroy it

    // Cycle through each NIC on the vm, determine the FQDN, add it to
    // the assocaitive array
    for($i=0; $i<count($vm->nic); $i++) {
        if(!isset($networks[$vm->nic[$i]->networkid]))
            throw new Exception("Unknown network {$vm->nic[$i]->networkid}");

        $name = $vm->name . '.' . $networks[$vm->nic[$i]->networkid];
        $cloud_vms[$name] = true;
    }
}

if(count($vms) == 0)
    throw new Exception('Did not get any cloud vms....dying as a precaution');

$hosts_to_remove = array();

foreach($hosts as $name => $blah) {
    if(isset($cloud_vms[$name]))
        continue;
    
    logMsg("Marking $name to be expunged from puppet DB");
    $hosts_to_remove[] = $name;
}

if(count($hosts_to_remove) > 0) {
    exec($puppet_rm_script . ' ' . implode(' ', $hosts_to_remove), $output, $result);

    if($result != 0)
        logMSG("ERROR: unable $puppet_rm_script didn't exit cleanly");
}

logMsg('All done');

function logMsg($msg) {
    echo date('Y-m-d H:i:s') . " $msg\n";
}
