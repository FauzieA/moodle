<?php
require_once('config.php');
require_once($CFG->libdir.'/moodlelib.php');

$user = $DB->get_record('user', ['username' => 'fauzie']);

if (!$user) {
    die('User not found.');
}

$newpassword = 'NewPassword123!'; // Set your new password here

if (update_internal_user_password($user, $newpassword)) {
    echo "Password successfully reset to: $newpassword";
} else {
    echo "Failed to reset password.";
}
