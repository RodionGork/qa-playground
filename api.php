<?php

define('TOKEN_SALT', 'S0m3S@lt');

$db = null;

$body = file_get_contents('php://input');

$json = json_decode($body);
if ($json === null) {
    http_response_code(400);
    echo 'Failed to parse body';
    return;
}

switch ($_SERVER['PATH_INFO']) {
    case '/get-token':
        getToken($json);
        break;
    case '/register':
        register($json);
        break;
    case '/get-members':
        getMembers();
        break;
    default:
        http_response_code(404);
        echo 'No such endpoint';
        return;
}

function getToken($json) {
    $pwd = hashPwd($json->pwd);
    $found = db()->querySingle("select * from users where name='{$json->user}' and pwd='$pwd'", true);
    if (!$found) {
        echo json_encode(['error' => 'authentication failed']);
        return;
    }
    $tkn = $found['id'] . '-' . time();
    $tkn .= '.' . md5(TOKEN_SALT . $tkn);
    echo json_encode(['token' => $tkn]);
}

function checkToken() {
    $h = getallheaders();
    $tkn = $h['X-Auth-Token'];
    if (!$tkn) {
        return false;
    }
    $parts = explode('.', $tkn);
    if (count($parts) < 2) {
        return false;
    }
    return md5(TOKEN_SALT . $parts[0]) == $parts[1];
}

function register($json) {
    if (strlen($json->user) < 4) {
        echo json_encode(['error' => 'Username too short']);
        return;
    }
    if (strlen($json->pwd) < 8) {
        echo json_encode(['error' => 'Password too short']);
        return;
    }
    if (strtolower($json->pwd) != strtolower($json->pwd2)) {
        echo json_encode(['error' => 'Two passwords differ']);
        return;
    }
    if (strpos($json->email, '@') === false) {
        echo json_encode(['error' => 'Email in wrong format']);
        return;
    }
    $pwd = hashPwd($json->pwd);
    db()->exec("insert into users (name, pwd, email) values ('{$json->user}', '$pwd', '{$json->email}')");
    echo json_encode(['success' => true]);
}

function hashPwd($pwd) {
    return md5('pwd-s@lt.' . $pwd);
}

function getMembers() {
    if (!checkToken()) {
        http_response_code(403);
        echo '[]';
        return;
    }
    $records = db()->query('select * from users limit 97');
    $res = [];
    while ($r = $records->fetchArray()) {
        $res[] = [$r['id'], $r['name'], $r['email']];
    }
    echo json_encode($res);
}

function db() {
    global $db;
    if ($db === null) {
        $db = new SQlite3('/tmp/sad-alcoholics.db');
        $exists = $db->querySingle("select count(*) from sqlite_master where type='table' AND name='users'");
        if (!$exists) {
            $db->exec('create table users (id integer primary key, name text, pwd text, email text)');
        }
    }
    return $db;
}
