<?php
$conn = new mysqli('localhost', 'root', '', 'hcinterior_git');
if ($conn->connect_error) die("Conn failed: " . $conn->connect_error);

foreach (['tbl_clock', 'tbl_clock_history', 'tbl_attendance', 'tbl_attendance_face_logs'] as $tbl) {
    echo "=== $tbl ===\n";
    $res = $conn->query("DESCRIBE $tbl");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            echo $r['Field'] . ' (' . $r['Type'] . ")\n";
        }
    } else {
        echo "Does not exist: " . $conn->error . "\n";
    }
}
