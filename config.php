<?php  // Moodle configuration file

// TEMPORARY: Allow cross-origin API access (only for testing!)
header("Access-Control-Allow-Origin: *");

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'pgsql';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'dpg-d0go2pbuibrs73fq44o0-a';
$CFG->dbname    = 'moodle_7g1n';
$CFG->dbuser    = 'moodle_7g1n_user';
$CFG->dbpass    = 'u4u7dVeBYujzLAy9hmtTO0Yiq8Y7jFfP';
$CFG->sslproxy  = true;
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist'   => 0,
  'dbport'      => 5432,
  'dbsocket'    => '',
  'dbcollation' => 'utf8mb4_general_ci',
);

$CFG->wwwroot   = 'https://moodle-site.onrender.com';  // Make sure this matches exactly
$CFG->dataroot  = '/var/www/moodledata';
$CFG->dirroot   = '/opt/render/project/src';
$CFG->admin     = 'admin';

$CFG->directorypermissions = 0777;

// DEBUGGING (for seeing errors on screen while testing)
@error_reporting(E_ALL | E_STRICT);
@ini_set('display_errors', '1');
$CFG->debug = (E_ALL | E_STRICT);
$CFG->debugdisplay = 1;

require_once(__DIR__ . '/lib/setup.php');
$CFG->auth = 'manual,email, userkey';

