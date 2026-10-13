<?php
/**
 * Custom Logic for HTMLy - SSRN Strategic Intelligence Stack
 * Isolates proprietary routes and logic from the core kernel.
 */

// Voting system - Handles Like/Dislike tallies
post('/vote/:slug/:type', function ($slug, $type) {
    if (isset($_COOKIE['voted_' . $slug])) {
        json(array('status' => 'error', 'message' => 'Already voted'), 403);
    }
    $votesFile = 'content/data/votes.json';
    $votes = array();
    if (file_exists($votesFile)) {
        $votes = json_decode(file_get_contents($votesFile), true);
    }
    if (!isset($votes[$slug])) {
        $votes[$slug] = array('likes' => 0, 'dislikes' => 0);
    }
    if ($type === 'like') {
        $votes[$slug]['likes']++;
    } elseif ($type === 'dislike') {
        $votes[$slug]['dislikes']++;
    }
    file_put_contents($votesFile, json_encode($votes), LOCK_EX);
    setcookie('voted_' . $slug, '1', time() + 86400 * 365, '/');
    json(array('status' => 'success'));
});

// Author Engagement - Anonymous Topic Suggestion & Direct Inquiry
post('/author-inquiry', function () {
    $input = $_POST;
    if (empty($input)) {
        $raw = file_get_contents('php://input');
        if (!empty($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $input = $decoded;
            }
        }
    }

    // 1. Anti-spam: honeypot verification
    if (!empty($input['website'])) {
        json(array('status' => 'success'), 200);
    }

    // 2. Anti-spam: cooldown check (6 hours)
    if (isset($_COOKIE['inquiry_cooldown'])) {
        json(array('status' => 'error', 'message' => 'Please wait before submitting another suggestion.'), 429);
    }

    // 3. Input validation & sanitization
    $topic = isset($input['topic']) ? trim($input['topic']) : '';
    if (empty($topic)) {
        json(array('status' => 'error', 'message' => 'Topic suggestion cannot be empty.'), 400);
    }

    if (mb_strlen($topic) > 500) {
        $topic = mb_substr($topic, 0, 500);
    }
    $topic = htmlspecialchars($topic, ENT_QUOTES, 'UTF-8');

    // 4. Persist to content/data/reader_inquiries.json
    $dataFile = 'content/data/reader_inquiries.json';
    if (!is_dir('content/data')) {
        @mkdir('content/data', 0775, true);
    }
    $inquiries = array();
    if (file_exists($dataFile)) {
        $rawJson = file_get_contents($dataFile);
        $decodedJson = json_decode($rawJson, true);
        if (is_array($decodedJson)) {
            $inquiries = $decodedJson;
        }
    }

    $inquiries[] = array(
        'timestamp' => time(),
        'date' => date('Y-m-d H:i:s'),
        'topic' => $topic
    );

    file_put_contents($dataFile, json_encode($inquiries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
    setcookie('inquiry_cooldown', '1', time() + 21600, '/');
    json(array('status' => 'success', 'message' => 'Submitted to Chas. Thank you.'));
});

// Cache Management - Author Profile Cache Invalidation
post('/clear-author-cache', function () {
    $dir = '/home/user0/www/bikepaths/html/blog/cache/page';
    $count = 0;
    foreach (glob($dir . '/*author*chas*.cache') as $f) {
        if (@unlink($f)) {
            $count++;
        }
    }
    header('Content-Type: application/json');
    echo json_encode(array('status' => 'success', 'cleared' => $count));
    die;
});

// Site Traffic Dashboard - GET (loads snapshot JSON)
get('/admin/traffic', function () {
    if (!login() || user('role', $_SESSION[site_url()]['user']) !== 'admin') {
        header("location: " . site_url() . "login");
        die;
    }

    $snapshotFile = '/home/user0/www/bikepaths/html/blog/content/data/traffic_snapshot.json';
    $snapshot = array('generated_at' => null, 'summary' => array(), 'security_offenders' => array(),
        'top_articles' => array(), 'inquiries_today' => array(), 'external_sources' => array(), 'geolocation' => array());

    if (file_exists($snapshotFile)) {
        $raw = file_get_contents($snapshotFile);
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $snapshot = $decoded;
        }
    }

    config('views.root', 'system/admin/views');
    render('traffic', array(
        'title'       => 'Site Traffic Dashboard',
        'description' => 'Admin traffic dashboard for bikepaths.org',
        'canonical'   => site_url() . 'admin/traffic',
        'metatags'    => '',
        'breadcrumb'  => '<a href="' . site_url() . '">' . config('breadcrumb.home') . '</a> &#187; <a href="' . site_url() . 'admin">Admin</a> &#187; Traffic',
        'type'        => 'is_admin',
        'is_admin'    => true,
        'snapshot'    => $snapshot,
        'bodyclass'   => 'hold-transition skin-blue sidebar-mini',
    ));
    die;
});

// Site Traffic Dashboard - POST (triggers live scan and refreshes snapshot)
post('/admin/traffic/refresh', function () {
    if (!login() || user('role', $_SESSION[site_url()]['user']) !== 'admin') {
        header("location: " . site_url() . "login");
        die;
    }

    shell_exec('python3 /home/user0/.cron_scripts/bot-guard.py -r --no-color --snapshot > /tmp/live_scan.log 2>&1 &');
    header("location: " . site_url() . "admin/traffic");
    die;
});

// Remote Sync Route - Triggers Atomic GitHub Synchronization


get('/admin/sync', function () {
    if (login() && user('role', $_SESSION[site_url()]['user']) === 'admin') {
        $output = shell_exec('/bin/bash /home/user0/bin/bikepaths-sync 2>&1');
        config('views.root', 'system/admin/views');
        render('main', array(
            'title' => 'GitHub Synchronization',
            'content' => '<h3>Sync Results</h3><pre>' . htmlspecialchars($output) . '</pre><a class="btn btn-primary" href="' . site_url() . 'admin">Back to Dashboard</a>',
            'type' => 'is_admin',
            'is_admin' => true
        ));
    } else {
        header("location: " . site_url() . "login");
    }
    die;
});
