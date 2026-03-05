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

    $userStmt = $pdo->prepare(
        'INSERT INTO ats_users (name, email, password_hash, role, marazone_user_id, active, created_at, updated_at)
         VALUES (:name, :email, :password_hash, :role, :marazone_user_id, 1, :created_at, :updated_at)
         ON DUPLICATE KEY UPDATE name = VALUES(name), password_hash = VALUES(password_hash), role = VALUES(role), updated_at = VALUES(updated_at)'
    );

    foreach ($users as $u) {
        $userStmt->execute([
            'name' => $u['name'],
            'email' => $u['email'],
            'password_hash' => password_hash($u['password'], PASSWORD_DEFAULT),
            'role' => $u['role'],
            'marazone_user_id' => null,
            'created_at' => now_utc(),
            'updated_at' => now_utc(),
        ]);
    }

    $ids = [];
    $stmt = $pdo->query("SELECT id, role FROM ats_users WHERE email IN ('admin@demo.test','lecturer@demo.test','cr@demo.test')");
    foreach ($stmt->fetchAll() as $row) {
        $ids[$row['role']] = (int) $row['id'];
    }

    $dayOfWeek = (int) (new DateTimeImmutable('today', app_timezone()))->format('N');
    $slotStmt = $pdo->prepare(
        'SELECT
            ts.slot_id,
            ts.course_id,
            ts.stage_no,
            ts.qualification_level_id,
            ts.semester_id,
            ts.lecturer_user_id,
            ts.room_id,
            ts.start_time,
            ts.end_time,
            COALESCE(ar.room_name, ar.room_code, CAST(ts.room_id AS CHAR)) AS room_name
         FROM timetable_slots ts
         LEFT JOIN academic_rooms ar ON ar.room_id = ts.room_id
         WHERE ts.day_of_week = :dow
           AND ts.status = "ACTIVE"
           AND ts.version_status = "PUBLISHED"
         ORDER BY ts.slot_id ASC
         LIMIT 1'
    );
    $slotStmt->execute(['dow' => $dayOfWeek]);
    $slot = $slotStmt->fetch();

    if (!$slot) {
        throw new RuntimeException('No published ACTIVE timetable slot found for today.');
    }

    $updateLecturer = $pdo->prepare('UPDATE ats_users SET marazone_user_id = :mz, updated_at = :updated_at WHERE id = :id');
    $updateLecturer->execute([
        'mz' => (int) $slot['lecturer_user_id'],
        'updated_at' => now_utc(),
        'id' => $ids['lecturer'],
    ]);

    $existsAssign = $pdo->prepare(
        'SELECT id FROM ats_cr_assignments
         WHERE cr_user_id = :cr_user_id
           AND active = 1
           AND (slot_id = :slot_id OR (slot_id IS NULL AND course_id = :course_id AND stage_no <=> :stage_no AND qualification_level_id <=> :qualification_level_id AND semester_id <=> :semester_id))
         LIMIT 1'
    );
    $existsAssign->execute([
        'cr_user_id' => $ids['cr'],
        'slot_id' => (int) $slot['slot_id'],
        'course_id' => (int) $slot['course_id'],
        'stage_no' => $slot['stage_no'] !== null ? (int) $slot['stage_no'] : null,
        'qualification_level_id' => $slot['qualification_level_id'] !== null ? (int) $slot['qualification_level_id'] : null,
        'semester_id' => $slot['semester_id'] !== null ? (int) $slot['semester_id'] : null,
    ]);

    if (!$existsAssign->fetch()) {
        $assignStmt = $pdo->prepare(
            'INSERT INTO ats_cr_assignments
             (section_id, slot_id, course_id, stage_no, qualification_level_id, semester_id, cr_user_id, starts_on, ends_on, assigned_by, active, created_at, updated_at)
             VALUES
             (:section_id, :slot_id, :course_id, :stage_no, :qualification_level_id, :semester_id, :cr_user_id, :starts_on, :ends_on, :assigned_by, 1, :created_at, :updated_at)'
        );

        $assignStmt->execute([
            'section_id' => null,
            'slot_id' => (int) $slot['slot_id'],
            'course_id' => (int) $slot['course_id'],
            'stage_no' => $slot['stage_no'] !== null ? (int) $slot['stage_no'] : null,
            'qualification_level_id' => $slot['qualification_level_id'] !== null ? (int) $slot['qualification_level_id'] : null,
            'semester_id' => $slot['semester_id'] !== null ? (int) $slot['semester_id'] : null,
            'cr_user_id' => $ids['cr'],
            'starts_on' => (new DateTimeImmutable('-30 days'))->format('Y-m-d'),
            'ends_on' => (new DateTimeImmutable('+365 days'))->format('Y-m-d'),
            'assigned_by' => $ids['admin'],
            'created_at' => now_utc(),
            'updated_at' => now_utc(),
        ]);
    }

    $sessionDate = (new DateTimeImmutable('today', app_timezone()))->format('Y-m-d');
    $sessionStmt = $pdo->prepare(
        'INSERT INTO ats_sessions
         (slot_id, course_id, stage_no, qualification_level_id, semester_id, lecturer_marazone_user_id, marazone_session_id, section_id, session_date, starts_at, ends_at, room, room_id, status, created_at, updated_at)
         VALUES
         (:slot_id, :course_id, :stage_no, :qualification_level_id, :semester_id, :lecturer_marazone_user_id, :msid, :section_id, :session_date, :starts_at, :ends_at, :room, :room_id, :status, :created_at, :updated_at)
         ON DUPLICATE KEY UPDATE
           course_id = VALUES(course_id),
           stage_no = VALUES(stage_no),
           qualification_level_id = VALUES(qualification_level_id),
           semester_id = VALUES(semester_id),
           lecturer_marazone_user_id = VALUES(lecturer_marazone_user_id),
           starts_at = VALUES(starts_at),
           ends_at = VALUES(ends_at),
           room = VALUES(room),
           room_id = VALUES(room_id),
           status = VALUES(status),
           updated_at = VALUES(updated_at)'
    );

    $sessionStmt->execute([
        'slot_id' => (int) $slot['slot_id'],
        'course_id' => (int) $slot['course_id'],
        'stage_no' => $slot['stage_no'] !== null ? (int) $slot['stage_no'] : null,
        'qualification_level_id' => $slot['qualification_level_id'] !== null ? (int) $slot['qualification_level_id'] : null,
        'semester_id' => $slot['semester_id'] !== null ? (int) $slot['semester_id'] : null,
        'lecturer_marazone_user_id' => $slot['lecturer_user_id'] !== null ? (int) $slot['lecturer_user_id'] : null,
        'msid' => (string) $slot['slot_id'] . '-' . $sessionDate,
        'section_id' => null,
        'session_date' => $sessionDate,
        'starts_at' => $slot['start_time'],
        'ends_at' => $slot['end_time'],
        'room' => $slot['room_name'] ?? null,
        'room_id' => $slot['room_id'] !== null ? (int) $slot['room_id'] : null,
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
