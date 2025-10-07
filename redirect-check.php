<?php
session_start();
if (!isset($_SESSION["username"]) && !isset($_COOKIE["username"])) {
    header("Location: login.php"); exit;
}
if (!isset($_SESSION["username"]) && isset($_COOKIE["username"])) {
    $_SESSION["username"] = $_COOKIE["username"];
}
?>