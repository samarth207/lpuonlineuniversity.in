<?php
session_start();

require_once __DIR__ . '/../db_config.php';

// Site URLs
define('SITE_URL', 'https://lpuonlineuniversity.in');
define('BLOG_URL', SITE_URL . '/blog');
define('CMS_URL', SITE_URL . '/blog-cms');
define('UPLOAD_DIR', __DIR__ . '/uploads');
define('UPLOAD_URL', CMS_URL . '/uploads');

// CSRF Token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Auth helpers
function isLoggedIn() {
    return !empty($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// Slug generation
function generateSlug($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

// Output escaping
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Generate TOC from HTML content
function generateTOC($html) {
    $toc = [];
    if (preg_match_all('/<(h[2-4])[^>]*id=["\']([^"\']*)["\'][^>]*>(.*?)<\/\1>/si', $html, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $m) {
            $toc[] = ['tag' => $m[1], 'id' => $m[2], 'text' => strip_tags($m[3])];
        }
    }
    return $toc;
}

// Add IDs to headings in content for TOC linking
function addHeadingIDs($html) {
    $counter = [];
    return preg_replace_callback('/<(h[2-4])([^>]*)>(.*?)<\/\1>/si', function($m) use (&$counter) {
        $tag = $m[1];
        $attrs = $m[2];
        $text = strip_tags($m[3]);
        $slug = generateSlug($text);
        if (empty($slug)) $slug = 'section';
        if (isset($counter[$slug])) {
            $counter[$slug]++;
            $slug .= '-' . $counter[$slug];
        } else {
            $counter[$slug] = 1;
        }
        // Preserve existing attributes but add/replace id
        if (preg_match('/id=["\']/', $attrs)) {
            return $m[0]; // Already has ID, keep it
        }
        return "<{$tag}{$attrs} id=\"{$slug}\">{$m[3]}</{$tag}>";
    }, $html);
}
