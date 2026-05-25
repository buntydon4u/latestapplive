<?php
// Set the inactivity time of 5 minutes (900 seconds)
$inactivity_time = 5 * 60;

// Check if the last_timestamp is set
// and last_timestamp is greater then 5 minutes or 900 seconds
// then unset $_SESSION variable & destroy session data
if (isset($_SESSION['last_timestamp']) && (time() - $_SESSION['last_timestamp']) > $inactivity_time) {
    session_unset();
    session_destroy();

    //Redirect user to login page
    header("Location: login.php?status=inactive");
    exit();
  }else{
    // Regenerate new session id and delete old one to prevent session fixation attack
    session_regenerate_id(true);

    // Update the last timestamp
    $_SESSION['last_timestamp'] = time();
  }
?>