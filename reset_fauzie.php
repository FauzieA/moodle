<?php
require_once('config.php');
require_once($CFG->libdir.'/moodlelib.php');

global $DB;

$username = 'fauzie'; // change if needed
$newpassword = 'NewPassword123!';

$user = $DB->get_record('user', ['username' => $username]);

if (!$user) {
    echo "User not found.";
    exit;
}

echo "Resetting password for user: {$user->username} (ID: {$user->id})<br>";

$hashed = hash_internal_user_password($newpassword);
$DB->set_field('user', 'password', $hashed, ['id' => $user->id]);

echo "Password has been reset to: $newpassword";
