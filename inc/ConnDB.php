<?php
/**
 * ConnDB.php - Backward-compatible database connector wrapper
 */
require_once __DIR__ . '/Database.php';

$db = new Database();
$conn = $db->getConnection();
$connectedConfig = $db->getConnectionInfo();
$lastError = $db->getLastError();

