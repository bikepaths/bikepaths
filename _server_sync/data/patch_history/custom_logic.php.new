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
