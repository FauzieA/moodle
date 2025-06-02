<?php

require_once(__DIR__ . '/config.php');
require_once($CFG->libdir . '/moodlelib.php');

// DO NOT leave this code in production!
// Run this file once via the browser, then delete it immediately.

$username = 'newadmin';
$password = 'TempAdmin123!';
$email    = 'admin@example.com';

// Check if user already exists
if ($DB->record_exists('user', ['username' => $username])) {
    echo "User already exists. Delete first or choose a different username.";
    die();
}

$user = new stdClass();
$user->auth = 'manual';
$user->confirmed = 1;
$user->mnethostid = $CFG->mnet_localhost_id;
$user->username = $username;
$user->password = hash_internal_user_password($password);
$user->firstname = 'Temp';
$user->lastname = 'Admin';
$user->email = $email;
$user->timecreated = time();
$user->timemodified = time();
$user->maildisplay = 1;

$new_user_id = $DB->insert_record('user', $user);

// Assign system admin role (roleid 1 = admin)
$context = context_system::instance();
role_assign(1, $new_user_id, $context->id);

echo "✅ Created admin user: <strong>$username</strong> with password <strong>$password</strong>. Please delete this file now.";
