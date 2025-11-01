<?php
session_start();
require_once 'db_connect.php';

// Redirect to dynamic homepage
header('Location: home.php');
exit();
?>