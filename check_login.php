<?php

session_start();

// Check if the user session is set
if (isset($_SESSION['user_id']) || isset($_SESSION['user_name'])) 
{
    echo "loggedin";
} 
else 
{
    echo "notloggedin";
}
?>

