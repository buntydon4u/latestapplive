<?php
session_start();
header('Content-Type: application/json');
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
if ($origin) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
} else {
    header('Access-Control-Allow-Origin: *');
}
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Enable error reporting for debugging in development, return as JSON if error occurs
ini_set('display_errors', 0);
error_reporting(E_ALL);
if (function_exists('mysqli_report')) {
    mysqli_report(MYSQLI_REPORT_OFF);
}

function json_error_handler($errno, $errstr, $errfile, $errline) {
    if (error_reporting() === 0) {
        return false;
    }
    echo json_encode([
        'success' => false,
        'error' => "PHP Error: [$errno] $errstr in $errfile on line $errline"
    ]);
    exit(0);
}
set_error_handler("json_error_handler");

// Parse JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (is_array($input)) {
    $_POST = array_merge($_POST, $input);
}

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

// DB details
// $servername = "localhost";
// $username = "555prouser";
// $password = "e2OFVjrRK77ljyfs4z@R";
// $dbname = "555prodb";
// $dbport = 3306;

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "test55";
$dbport = 3306;

if (file_exists(__DIR__ . '/api-config.local.php')) {
    require __DIR__ . '/api-config.local.php';
}

$servername = getenv('DB_HOST') ?: $servername;
$username = getenv('DB_USER') ?: $username;
$password = getenv('DB_PASS') ?: $password;
$dbname = getenv('DB_NAME') ?: $dbname;
$dbport = (int)(getenv('DB_PORT') ?: $dbport);

function get_db_connection() {
    global $servername, $username, $password, $dbname, $dbport;

    if (!class_exists('mysqli')) {
        echo json_encode(['success' => false, 'error' => 'Database connection failed: mysqli extension is not enabled']);
        exit();
    }

    $conn = mysqli_init();
    if (!$conn) {
        echo json_encode(['success' => false, 'error' => 'Database connection failed: unable to initialize mysqli']);
        exit();
    }

    $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
    set_error_handler(function () {
        return true;
    });
    $connected = @$conn->real_connect($servername, $username, $password, $dbname, $dbport);
    restore_error_handler();
    if (!$connected) {
        $message = mysqli_connect_error();
        echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . ($message ?: $conn->connect_error)]);
        exit();
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

function resolve_shift_id($conn, $submitted_shift, $updated_by) {
    $submitted_shift = (int)$submitted_shift;
    if (!$submitted_shift || !$updated_by) {
        return $submitted_shift;
    }

    date_default_timezone_set('Asia/Kolkata');
    $fromdate = date('Y-m-d');
    $todate = date('Y-m-d', time() + (12 * 60 * 60));
    $stmt = $conn->prepare("
        SELECT id, shift_id, app_time, open_date
        FROM user_shift_timings
        WHERE updated_by = ?
          AND open_date >= ?
          AND open_date <= ?
          AND (id = ? OR shift_id = ?)
        ORDER BY open_date ASC, master ASC
    ");
    $stmt->bind_param("sssii", $updated_by, $fromdate, $todate, $submitted_shift, $submitted_shift);
    $stmt->execute();
    $res = $stmt->get_result();

    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
    }

    $now = time();
    foreach ($rows as $row) {
        $limit = strtotime(date('d-m-Y', strtotime($row['open_date'])) . ' ' . date('H:i', strtotime($row['app_time'])));
        if ($now < $limit && (int)$row['id'] === $submitted_shift) {
            return (int)$row['id'];
        }
    }

    foreach ($rows as $row) {
        $limit = strtotime(date('d-m-Y', strtotime($row['open_date'])) . ' ' . date('H:i', strtotime($row['app_time'])));
        if ($now < $limit && (int)$row['shift_id'] === $submitted_shift) {
            return (int)$row['id'];
        }
    }

    return $submitted_shift;
}

switch ($action) {
    case 'login':
        $user_name = isset($_POST['username']) ? trim($_POST['username']) : '';
        $pass_word = isset($_POST['password']) ? trim($_POST['password']) : '';

        if (empty($user_name) || empty($pass_word)) {
            echo json_encode(['success' => false, 'error' => 'Username and password are required']);
            exit();
        }

        $conn = get_db_connection();
        $stmt = $conn->prepare("SELECT id, ledger_name, updated_by FROM tbl_ledger WHERE username = ? AND password = ? AND is_master = '0' AND status = '1'");
        $stmt->bind_param("ss", $user_name, $pass_word);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($row = $res->fetch_assoc()) {
            // Check if user has child logins
            $stmt2 = $conn->prepare("SELECT id FROM tbl_ledger WHERE parent_id = ?");
            $stmt2->bind_param("i", $row['id']);
            $stmt2->execute();
            $res2 = $stmt2->get_result();
            $has_children = ($res2->num_rows > 0);

            if ($has_children) {
                $_SESSION['parent_id'] = $row['id'];
                $_SESSION['parent_name'] = $row['ledger_name'];
                $_SESSION['parent_updated_by'] = $row['updated_by'];
                echo json_encode([
                    'success' => true,
                    'parent_selection_required' => true,
                    'parent_id' => $row['id'],
                    'name' => $row['ledger_name']
                ]);
            } else {
                $_SESSION['login'] = $row['id'];
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['updated_by'] = $row['updated_by'];
                $_SESSION['user_type'] = 'ledger';
                echo json_encode([
                    'success' => true,
                    'parent_selection_required' => false,
                    'user' => [
                        'id' => $row['id'],
                        'name' => $row['ledger_name'],
                        'user_type' => 'ledger',
                        'updated_by' => $row['updated_by']
                    ]
                ]);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid Username or Password']);
        }
        $conn->close();
        break;

    case 'select_child':
        if (!isset($_SESSION['parent_id'])) {
            echo json_encode(['success' => false, 'error' => 'No active parent login found']);
            exit();
        }
        $child_id = isset($_POST['child_id']) ? (int)$_POST['child_id'] : 0;
        
        $conn = get_db_connection();
        // Verify relationship
        $stmt = $conn->prepare("SELECT id, ledger_name, updated_by FROM tbl_ledger WHERE id = ? AND (parent_id = ? OR id = ?)");
        $parent_id = $_SESSION['parent_id'];
        $stmt->bind_param("iii", $child_id, $parent_id, $parent_id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($row = $res->fetch_assoc()) {
            $_SESSION['login'] = $row['id'];
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['updated_by'] = $row['updated_by'];
            $_SESSION['user_type'] = 'ledger';
            echo json_encode([
                'success' => true,
                'user' => [
                    'id' => $row['id'],
                    'name' => $row['ledger_name'],
                    'user_type' => 'ledger',
                    'updated_by' => $row['updated_by']
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid selection']);
        }
        $conn->close();
        break;

    case 'get_session':
        if (isset($_SESSION['login'])) {
            $conn = get_db_connection();
            $stmt = $conn->prepare("SELECT id, ledger_name, updated_by FROM tbl_ledger WHERE id = ?");
            $stmt->bind_param("i", $_SESSION['login']);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                echo json_encode([
                    'logged_in' => true,
                    'user' => [
                        'id' => $row['id'],
                        'name' => $row['ledger_name'],
                        'user_type' => 'ledger',
                        'updated_by' => $row['updated_by']
                    ]
                ]);
            } else {
                echo json_encode(['logged_in' => false]);
            }
            $conn->close();
        } else if (isset($_SESSION['parent_id'])) {
            echo json_encode([
                'logged_in' => false,
                'parent_selection_required' => true,
                'parent_id' => $_SESSION['parent_id'],
                'name' => $_SESSION['parent_name']
            ]);
        } else {
            echo json_encode(['logged_in' => false]);
        }
        break;

    case 'logout':
        session_destroy();
        echo json_encode(['success' => true]);
        break;

    case 'get_children':
        $parent_id = isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : (isset($_SESSION['parent_id']) ? (int)$_SESSION['parent_id'] : 0);
        if (!$parent_id) {
            echo json_encode(['success' => false, 'error' => 'Parent ID is required']);
            exit();
        }
        $conn = get_db_connection();
        $stmt = $conn->prepare("SELECT id, ledger_name FROM tbl_ledger WHERE parent_id = ?");
        $stmt->bind_param("i", $parent_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $children = [];
        while ($row = $res->fetch_assoc()) {
            $children[] = $row;
        }
        echo json_encode(['success' => true, 'children' => $children]);
        $conn->close();
        break;

    case 'get_balance':
        if (!isset($_SESSION['login'])) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit();
        }
        $ledger_id = (int)$_SESSION['login'];
        $conn = get_db_connection();

        $start_datetime = '2025-08-01';
        $end_datetime = date('Y-m-d 06:00:00', strtotime('+1 day'));

        // Query 1: coin transactions
        $stmt = $conn->prepare("
            SELECT amount, sender_id, receiver_id, status, type 
            FROM coin_transactions 
            WHERE (receiver_id = ? OR (sender_id = ? AND type = 'spend'))
              AND created_at >= ? AND created_at < ?
        ");
        $stmt->bind_param("iiss", $ledger_id, $ledger_id, $start_datetime, $end_datetime);
        $stmt->execute();
        $res = $stmt->get_result();
        $balance = 0;
        while ($tx = $res->fetch_assoc()) {
            if ($tx['receiver_id'] == $ledger_id) {
                $balance += (float)$tx['amount'];
            } elseif ($tx['sender_id'] == $ledger_id && $tx['status'] == 1) {
                $balance -= (float)$tx['amount'];
            }
        }

        // Query 2: final hisab
        $stmt2 = $conn->prepare("
            SELECT today_hisab 
            FROM tbl_final_hisab 
            WHERE ledger_id = ?
              AND STR_TO_DATE(date, '%d-%m-%Y') >= ?
              AND STR_TO_DATE(date, '%d-%m-%Y') < ?
        ");
        $start_date_only = date('Y-m-d', strtotime($start_datetime));
        $end_date_only = date('Y-m-d', strtotime($end_datetime));
        $stmt2->bind_param("iss", $ledger_id, $start_date_only, $end_date_only);
        $stmt2->execute();
        $res2 = $stmt2->get_result();
        while ($row = $res2->fetch_assoc()) {
            $pl = (float)$row['today_hisab'];
            if ($pl < 0) {
                $balance += abs($pl);
            } else {
                $balance -= $pl;
            }
        }

        // Query 3: deduct amount
        $stmt3 = $conn->prepare("
            SELECT SUM(amount) AS deduct_amount 
            FROM coin_transactions 
            WHERE shift_id IS NOT NULL 
              AND deposite_byto_master = 0 
              AND type = 'allocation' 
              AND status = 1 
              AND sender_id = ?
              AND created_at >= ? AND created_at < ?
        ");
        $stmt3->bind_param("iss", $ledger_id, $start_datetime, $end_datetime);
        $stmt3->execute();
        $res3 = $stmt3->get_result();
        if ($row3 = $res3->fetch_assoc()) {
            $balance -= (float)($row3['deduct_amount'] ?? 0);
        }

        echo json_encode(['success' => true, 'balance' => $balance]);
        $conn->close();
        break;

    case 'get_parties':
        if (!isset($_SESSION['login'])) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit();
        }
        $conn = get_db_connection();
        $result = $conn->query("SELECT id, ledger_name FROM tbl_ledger WHERE Status = 1 ORDER BY ledger_name ASC");
        $parties = [];
        while ($row = $result->fetch_assoc()) {
            $parties[] = [
                'id' => $row['id'],
                'name' => $row['ledger_name']
            ];
        }
        echo json_encode(['success' => true, 'parties' => $parties]);
        $conn->close();
        break;

    case 'get_shifts':
        if (!isset($_SESSION['login'])) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit();
        }
        date_default_timezone_set('Asia/Kolkata');
        $updated_by = $_SESSION['updated_by'];
        $newTimestamp = time() + (12 * 60 * 60);
        $todate = date('Y-m-d', $newTimestamp);
        $fromdate = date('Y-m-d');

        $conn = get_db_connection();
        $stmt = $conn->prepare("
            SELECT user_shift_timings.id AS id, tbl_shift.id AS tbl_shift_id, tbl_shift.shift_name, user_shift_timings.app_time, user_shift_timings.open_date, tbl_shift.super_admin 
            FROM user_shift_timings 
            LEFT JOIN tbl_shift ON user_shift_timings.shift_id = tbl_shift.id 
            WHERE user_shift_timings.updated_by = ? 
              AND user_shift_timings.open_date >= ? 
              AND user_shift_timings.open_date <= ? 
            ORDER BY user_shift_timings.open_date ASC, user_shift_timings.master ASC
        ");
        $stmt->bind_param("sss", $updated_by, $fromdate, $todate);
        $stmt->execute();
        $res = $stmt->get_result();

        $shifts = [];
        $ttime = time();
        while ($row = $res->fetch_assoc()) {
            $time = strtotime(date('d-m-Y', strtotime($row['open_date'])) . ' ' . date("H:i", strtotime($row['app_time'])));
            $expired = ($ttime >= $time);
            $shifts[] = [
                'id' => $row['id'],
                'tbl_shift_id' => $row['tbl_shift_id'],
                'name' => $row['shift_name'],
                'app_time' => $row['app_time'],
                'open_date' => $row['open_date'],
                'time_limit_timestamp' => $time,
                'expired' => $expired
            ];
        }
        echo json_encode(['success' => true, 'shifts' => $shifts]);
        $conn->close();
        break;

    case 'get_transactions':
        if (!isset($_SESSION['login'])) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit();
        }
        date_default_timezone_set('Asia/Kolkata');
        $userid = (int)$_SESSION['login'];
        $user_type = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'ledger';
        $conn = get_db_connection();

        $stmt = $conn->prepare("
            SELECT tbl_master_transaction.id,
                   tbl_agent.agent_name,
                   tbl_trans_numbers.created_date AS createddate,
                   tbl_trans_numbers.modified_date AS modifieddate,
                   tbl_master_transaction.t_date,
                   tbl_master_transaction.total_number_amount,
                   tbl_master_transaction.created_date,
                   tbl_master_transaction.party_id,
                   tbl_shift.id AS shiftid,
                   user_shift_timings.app_time,
                   tbl_shift.shift_name AS shift_name,
                   user_shift_timings.open_date,
                   tbl_shift.super_admin,
                   tbl_shift.data_entry_operator,
                   tbl_shift.id AS shift_id,
                   tbl_ledger.ledger_name,
                   tbl_trans_numbers.number AS trnno,
                   tbl_trans_numbers.amount AS trn_amt
            FROM tbl_master_transaction 
            JOIN user_shift_timings ON user_shift_timings.id = tbl_master_transaction.shift_id 
            JOIN tbl_shift ON tbl_shift.id = user_shift_timings.shift_id 
            JOIN tbl_trans_numbers ON tbl_trans_numbers.master_id = tbl_master_transaction.id 
            JOIN tbl_ledger ON tbl_ledger.id = tbl_master_transaction.party_id
            LEFT JOIN tbl_agent ON tbl_ledger.agent_id = tbl_agent.id
            WHERE tbl_master_transaction.t_date >= NOW() - INTERVAL 30 DAY 
              AND tbl_master_transaction.party_id = ? 
            ORDER BY tbl_master_transaction.id DESC
        ");
        $stmt->bind_param("i", $userid);
        $stmt->execute();
        $res = $stmt->get_result();

        $transactions = [];
        $ttime = time();
        while ($row = $res->fetch_assoc()) {
            $t_date = date('Y-m-d', strtotime($row['t_date']));

            if ($user_type === 'admin') {
                $time = strtotime(date('d-m-Y', strtotime($row['open_date'])) . ' ' . date("H:i", strtotime($row['super_admin'])));
            } else {
                if ((int)$row['shiftid'] === 11) {
                    $time = strtotime(date('d-m-Y', strtotime($row['open_date'])) . ' ' . date("H:i", strtotime($row['app_time'])));
                    $t_date = $row['t_date'];
                } else {
                    $time = strtotime($row['app_time']);
                }
            }

            $startDate = strtotime("$t_date 14:00:00");
            $endDate = strtotime("+1 day 04:15:00", strtotime($t_date));
            if ((int)$row['shiftid'] === 11) {
                $can_delete = ($ttime >= $startDate && $ttime <= $endDate);
            } else {
                $can_delete = ($ttime < $time && (date('Y-m-d', $ttime) == $t_date));
            }

            $amounts = explode(',', $row['trn_amt']);
            $total_amount = array_sum(array_map('floatval', $amounts));

            $transactions[] = [
                'id' => (int)$row['id'],
                't_date' => $row['t_date'],
                'display_date' => date('d M, Y', strtotime($row['t_date'])),
                'shift_name' => $row['shift_name'],
                'total_amount' => $total_amount,
                'can_delete' => $can_delete,
                'shiftid' => (int)$row['shiftid']
            ];
        }

        echo json_encode(['success' => true, 'transactions' => $transactions]);
        $conn->close();
        break;

    case 'get_transaction_details':
        if (!isset($_SESSION['login'])) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit();
        }
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'Transaction ID is required']);
            exit();
        }

        $conn = get_db_connection();
        $stmt = $conn->prepare("
            SELECT tbl_master_transaction.t_date, tbl_shift.shift_name, tbl_trans_numbers.number AS trnno, tbl_trans_numbers.amount AS trn_amt 
            FROM tbl_master_transaction 
            JOIN user_shift_timings ON user_shift_timings.id = tbl_master_transaction.shift_id 
            JOIN tbl_shift ON tbl_shift.id = user_shift_timings.shift_id 
            JOIN tbl_trans_numbers ON tbl_trans_numbers.master_id = tbl_master_transaction.id 
            WHERE tbl_master_transaction.id = ? AND tbl_master_transaction.party_id = ?
        ");
        $userid = $_SESSION['login'];
        $stmt->bind_param("ii", $id, $userid);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($row = $res->fetch_assoc()) {
            $numbers = explode(',', $row['trnno']);
            $amounts = explode(',', $row['trn_amt']);
            $items = [];
            foreach ($numbers as $idx => $num) {
                if (trim($num) !== '') {
                    $items[] = [
                        'number' => $num,
                        'amount' => isset($amounts[$idx]) ? (float)$amounts[$idx] : 0
                    ];
                }
            }
            echo json_encode([
                'success' => true,
                'shift_name' => $row['shift_name'],
                't_date' => $row['t_date'],
                'items' => $items
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Transaction not found']);
        }
        $conn->close();
        break;

    case 'get_hisabs':
        if (!isset($_SESSION['login'])) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit();
        }
        $ledger_id = $_SESSION['login'];
        $conn = get_db_connection();
        $stmt = $conn->prepare("SELECT date FROM tbl_final_hisab WHERE ledger_id = ? ORDER BY STR_TO_DATE(date, '%d-%m-%Y') DESC");
        $stmt->bind_param("i", $ledger_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $hisabs = [];
        while ($row = $res->fetch_assoc()) {
            $hisabs[] = [
                'date' => $row['date'],
                'updated_by' => $_SESSION['updated_by']
            ];
        }
        echo json_encode(['success' => true, 'hisabs' => $hisabs]);
        $conn->close();
        break;

    case 'submit_transaction':
        if (!isset($_SESSION['login'])) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit();
        }

        // We will proxy the POST request to the live backend using cURL
        $url = "https://new.555xch.pro/tbl_transactions/add_transaction_final_app_api";
        $conn = get_db_connection();
        $shift_id = resolve_shift_id($conn, $_POST['shift'], $_SESSION['updated_by']);
        $conn->close();
        
        // Prepare POST fields
        $fields = [
            'party' => $_POST['party'],
            'dateoftrnforapponly' => $_POST['dateoftrnforapponly'],
            'dateoftrn' => $_POST['dateoftrn'],
            'userid' => $_SESSION['login'],
            'entryval' => 'Entry-page.php?login=' . $_SESSION['login'] . '&user_type=ledger',
            'updated_by' => $_SESSION['updated_by'],
            'shift' => $shift_id,
            'submitpost' => 'submit'
        ];

        if (isset($_POST['trn_number']) && is_array($_POST['trn_number'])) {
            $fields['trn_number'] = $_POST['trn_number'];
        }
        if (isset($_POST['trn_amount']) && is_array($_POST['trn_amount'])) {
            $fields['trn_amount'] = $_POST['trn_amount'];
        }

        // Build HTTP Query to handle nested arrays
        $postData = http_build_query($fields);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Don't redirect automatically, we want to capture redirect status
        curl_setopt($ch, CURLOPT_HEADER, true);

        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        // Check if redirect contains status=1
        $redirectUrl = isset($info['redirect_url']) ? $info['redirect_url'] : '';
        if (empty($redirectUrl) && preg_match('/Location:\s*([^\r\n]+)/i', $response, $matches)) {
            $redirectUrl = trim($matches[1]);
        }

        if (strpos($redirectUrl, 'status=1') !== false) {
            echo json_encode(['success' => true, 'status' => 1]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Submission failed. Please check shift times and parameters.', 'redirect' => $redirectUrl]);
        }
        break;

    case 'submit_jantri':
        if (!isset($_SESSION['login'])) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit();
        }

        $url = "https://new.555xch.pro/tbl_jantri/add_jantri_form_app";
        $conn = get_db_connection();
        $shift_id = resolve_shift_id($conn, $_POST['shift'], $_SESSION['updated_by']);
        $conn->close();

        $fields = [
            'party' => $_POST['party'],
            'dateoftrnforapponly' => $_POST['dateoftrnforapponly'],
            'dateoftrn' => $_POST['dateoftrn'],
            'userid' => $_SESSION['login'],
            'entryval' => 'Entry-page.php?login=' . $_SESSION['login'] . '&user_type=ledger',
            'updated_by' => $_SESSION['updated_by'],
            'shift' => $shift_id,
            'ttamntt' => '0',
            'gtotal' => $_POST['gtotal'],
            'submitpost' => 'submit'
        ];

        if (isset($_POST['trn_amount']) && is_array($_POST['trn_amount'])) {
            $fields['trn_amount'] = $_POST['trn_amount'];
        }
        if (isset($_POST['b']) && is_array($_POST['b'])) {
            $fields['b'] = $_POST['b'];
        }
        if (isset($_POST['a']) && is_array($_POST['a'])) {
            $fields['a'] = $_POST['a'];
        }

        $postData = http_build_query($fields);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        $redirectUrl = isset($info['redirect_url']) ? $info['redirect_url'] : '';
        if (empty($redirectUrl) && preg_match('/Location:\s*([^\r\n]+)/i', $response, $matches)) {
            $redirectUrl = trim($matches[1]);
        }

        if (strpos($redirectUrl, 'status=1') !== false) {
            echo json_encode(['success' => true, 'status' => 1]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Jantri submission failed.', 'redirect' => $redirectUrl]);
        }
        break;

    case 'delete_transaction':
        if (!isset($_SESSION['login'])) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit();
        }
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'Transaction ID is required']);
            exit();
        }

        $userid = $_SESSION['login'];
        $url = "https://new.555xch.pro/tbl_transactions/remove_app/$id/$userid";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}
?>
