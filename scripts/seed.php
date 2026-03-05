<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap/autoload.php';
require_once __DIR__ . '/../bootstrap/helpers.php';

use App\Core\Database;

$pdo = Database::default();
$pdo->beginTransaction();

try {
    $users = [
        ['name' => 'System Admin', 'email' => 'admin@demo.test', 'password' => 'Password123!', 'role' => 'admin'],
        ['name' => 'Lecturer One', 'email' => 'lecturer@demo.test', 'password' => 'Password123!', 'role' => 'lecturer'],
        ['name' => 'Class Representative', 'email' => 'cr@demo.test', 'password' => 'Password123!', 'role' => 'cr'],
    ];

    $userStmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role, active, created_at, updated_at) VALUES (:name, :email, :password_hash, :role, 1, :created_at, :updated_at) ON DUPLICATE KEY UPDATE name = VALUES(name), password_hash = VALUES(password_hash), role = VALUES(role), updated_at = VALUES(updated_at)');
    foreach ($users as $u) {
        $userStmt->execute([
            'name' => $u['name'],
            'email' => $u['email'],
            'password_hash' => password_hash($u['password'], PASSWORD_DEFAULT),
            'role' => $u['role'],
            'created_at' => now_utc(),
            'updated_at' => now_utc(),
        ]);
    }

    $ids = [];
    $stmt = $pdo->query("SELECT id, role FROM users WHERE email IN ('admin@demo.test','lecturer@demo.test','cr@demo.test')");
    foreach ($stmt->fetchAll() as $row) {
        $ids[$row['role']] = (int) $row['id'];
    }

    $courseStmt = $pdo->prepare('INSERT INTO courses (marazone_course_id, code, title, lecturer_user_id, active, created_at, updated_at) VALUES (:mz, :code, :title, :lecturer, 1, :created_at, :updated_at) ON DUPLICATE KEY UPDATE title = VALUES(title), lecturer_user_id = VALUES(lecturer_user_id), updated_at = VALUES(updated_at)');
    $courseStmt->execute([
        'mz' => 'MZ-CSE-001',
        'code' => 'CSE101',
        'title' => 'Software Engineering',
        'lecturer' => $ids['lecturer'],
        'created_at' => now_utc(),
        'updated_at' => now_utc(),
    ]);

    $courseId = (int) $pdo->query("SELECT id FROM courses WHERE code = 'CSE101' LIMIT 1")->fetch()['id'];

    $sectionStmt = $pdo->prepare('INSERT INTO sections (marazone_section_id, course_id, name, semester, academic_year, created_at, updated_at) VALUES (:mz, :course_id, :name, :semester, :academic_year, :created_at, :updated_at) ON DUPLICATE KEY UPDATE course_id = VALUES(course_id), updated_at = VALUES(updated_at)');
    $sectionStmt->execute([
        'mz' => 'MZ-SEC-001',
        'course_id' => $courseId,
        'name' => 'SE-1A',
        'semester' => 'Semester 1',
        'academic_year' => '2026/2027',
        'created_at' => now_utc(),
        'updated_at' => now_utc(),
    ]);

    $sectionId = (int) $pdo->query("SELECT id FROM sections WHERE marazone_section_id = 'MZ-SEC-001' LIMIT 1")->fetch()['id'];

    $assignStmt = $pdo->prepare('INSERT INTO cr_assignments (section_id, cr_user_id, starts_on, ends_on, assigned_by, active, created_at, updated_at) VALUES (:section_id, :cr_user_id, :starts_on, :ends_on, :assigned_by, 1, :created_at, :updated_at)');
    $existsAssign = $pdo->prepare('SELECT id FROM cr_assignments WHERE section_id = :sid AND cr_user_id = :uid LIMIT 1');
    $existsAssign->execute(['sid' => $sectionId, 'uid' => $ids['cr']]);
    if (!$existsAssign->fetch()) {
        $assignStmt->execute([
            'section_id' => $sectionId,
            'cr_user_id' => $ids['cr'],
            'starts_on' => (new DateTimeImmutable('-30 days'))->format('Y-m-d'),
            'ends_on' => (new DateTimeImmutable('+365 days'))->format('Y-m-d'),
            'assigned_by' => $ids['admin'],
            'created_at' => now_utc(),
            'updated_at' => now_utc(),
        ]);
    }

    $students = [
        ['reg_no' => 'SE1A-001', 'full_name' => 'Amina Hassan', 'email' => 'amina@student.test'],
        ['reg_no' => 'SE1A-002', 'full_name' => 'Baraka Juma', 'email' => 'baraka@student.test'],
        ['reg_no' => 'SE1A-003', 'full_name' => 'Catherine Paul', 'email' => 'catherine@student.test'],
        ['reg_no' => 'SE1A-004', 'full_name' => 'Daniel Mushi', 'email' => 'daniel@student.test'],
    ];

    $studentStmt = $pdo->prepare('INSERT INTO students (marazone_student_id, reg_no, full_name, email, section_id, active, created_at, updated_at) VALUES (:mz, :reg_no, :full_name, :email, :section_id, 1, :created_at, :updated_at) ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), email = VALUES(email), section_id = VALUES(section_id), updated_at = VALUES(updated_at)');
    $i = 1;
    foreach ($students as $s) {
        $studentStmt->execute([
            'mz' => 'MZ-STU-00' . $i,
            'reg_no' => $s['reg_no'],
            'full_name' => $s['full_name'],
            'email' => $s['email'],
            'section_id' => $sectionId,
            'created_at' => now_utc(),
            'updated_at' => now_utc(),
        ]);
        $i++;
    }

    $sessionDate = (new DateTimeImmutable('today'))->format('Y-m-d');
    $sessionStmt = $pdo->prepare('INSERT INTO sessions (marazone_session_id, section_id, session_date, starts_at, ends_at, room, status, created_at, updated_at) VALUES (:msid, :section_id, :session_date, :starts_at, :ends_at, :room, :status, :created_at, :updated_at) ON DUPLICATE KEY UPDATE status = VALUES(status), updated_at = VALUES(updated_at)');
    $sessionStmt->execute([
        'msid' => 'MZ-SLOT-001-' . $sessionDate,
        'section_id' => $sectionId,
        'session_date' => $sessionDate,
        'starts_at' => '08:00:00',
        'ends_at' => '10:00:00',
        'room' => 'LT-01',
        'status' => 'scheduled',
        'created_at' => now_utc(),
        'updated_at' => now_utc(),
    ]);

    $pdo->commit();
    echo "Seed complete.\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
}
