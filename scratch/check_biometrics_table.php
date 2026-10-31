<?php
$conn = new mysqli('localhost', 'root', '', 'hcinterior_git');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "=== tbl_attendance_face_logs schema ===\n";
$res = $conn->query("DESCRIBE tbl_attendance_face_logs");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        echo "{$row['Field']} ({$row['Type']})\n";
    }
} else {
    echo "Table does not exist\n";
}

echo "\n=== Last 5 logs in tbl_attendance_face_logs ===\n";
$rows = $conn->query("SELECT * FROM tbl_attendance_face_logs ORDER BY log_id DESC LIMIT 5");
if ($rows) {
    while ($r = $rows->fetch_assoc()) {
        echo "LogID: {$r['log_id']} | User: {$r['user_id']} | Punch: {$r['punch_type']} | Score: {$r['face_score']} | Time: {$r['punch_time']}\n";
    }
}
