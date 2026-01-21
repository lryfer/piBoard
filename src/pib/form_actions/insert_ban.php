<?php
require_once "../ErrorHandler.php";
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    die("Error, method should be POST");
}

require_once "../../connection.php";
require_once "../Ban.php";
require_once "../CSRF.php";
require_once "utilities.php";

if (!\pib\CSRF::validateRequest()) {
    die("Error: Invalid CSRF token");
}

if (!isset($_POST['endDate'])) {
    die("Error, required data not provided");
}

global $conn;

$current_role = get_role($conn);
if (!$current_role || !in_array($current_role, ["Founder", "Admin", "Mod"])) {
    die("Error: Unauthorized action - Only staff members can create bans");
}

if (empty($_POST['startDate']) || empty($_POST['endDate'])) {
    die("Error: Start date and end date are required");
}

try {
    $startDate = new \DateTime($_POST['startDate']);
    $endDate = new \DateTime($_POST['endDate']);
    $startYear = (int)$startDate->format('Y');
    $endYear = (int)$endDate->format('Y');
    if ($startYear < 1900 || $startYear > 2100 || $endYear < 1900 || $endYear > 2100) {
        die("Error: Invalid date range. Year must be between 1900 and 2100");
    }

    if ($endDate <= $startDate) {
        die("Error: End date must be after start date");
    }

    $startDate->setTimezone(new DateTimeZone('UTC'));
    $endDate->setTimezone(new DateTimeZone('UTC'));
} catch (\Exception $e) {
    die("Error: Invalid date format - " . $e->getMessage());
}

$boards = new \pib\Ban($conn);
$account_id = $_POST['account'] ?? null;
if(strlen($account_id) == 0) $account_id = null;
$ip_address = (!$account_id)? $_POST['ip_address'] : null;
$boards->addBan($account_id, $ip_address, $startDate, $endDate);

header('Location: ../../admin.php');
