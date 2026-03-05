<?php

declare(strict_types=1);

namespace App\Sync;

use App\Core\Database;

final class SessionProjectionService implements SessionProjectionServiceInterface
{
    public function projectForDate(string $date): array
    {
        $day = (int) (new \DateTimeImmutable($date))->format('N');

        $sql = <<<'SQL'
SELECT
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
  AND ts.status = 'ACTIVE'
  AND ts.version_status = 'PUBLISHED'
SQL;

        $read = Database::marazoneReadOnly();
        $stmt = $read->prepare($sql);
        $stmt->execute(['dow' => $day]);
        $rows = $stmt->fetchAll();

        $insert = Database::default()->prepare(
            'INSERT INTO ats_sessions (slot_id, course_id, stage_no, qualification_level_id, semester_id, lecturer_marazone_user_id, marazone_session_id, section_id, session_date, starts_at, ends_at, room, room_id, status, created_at, updated_at)
             VALUES (:slot_id, :course_id, :stage_no, :qualification_level_id, :semester_id, :lecturer_marazone_user_id, :msid, :section_id, :session_date, :starts_at, :ends_at, :room, :room_id, :status, :created_at, :updated_at)
             ON DUPLICATE KEY UPDATE course_id = VALUES(course_id), stage_no = VALUES(stage_no), qualification_level_id = VALUES(qualification_level_id), semester_id = VALUES(semester_id), lecturer_marazone_user_id = VALUES(lecturer_marazone_user_id), starts_at = VALUES(starts_at), ends_at = VALUES(ends_at), room = VALUES(room), room_id = VALUES(room_id), status = VALUES(status), updated_at = VALUES(updated_at)'
        );

        $count = 0;
        foreach ($rows as $row) {
            $msid = sprintf('%s-%s', $row['slot_id'], $date);
            $insert->execute([
                'slot_id' => (int) $row['slot_id'],
                'course_id' => (int) $row['course_id'],
                'stage_no' => $row['stage_no'] !== null ? (int) $row['stage_no'] : null,
                'qualification_level_id' => $row['qualification_level_id'] !== null ? (int) $row['qualification_level_id'] : null,
                'semester_id' => $row['semester_id'] !== null ? (int) $row['semester_id'] : null,
                'lecturer_marazone_user_id' => $row['lecturer_user_id'] !== null ? (int) $row['lecturer_user_id'] : null,
                'msid' => $msid,
                'section_id' => null,
                'session_date' => $date,
                'starts_at' => $row['start_time'],
                'ends_at' => $row['end_time'],
                'room' => $row['room_name'],
                'room_id' => $row['room_id'] !== null ? (int) $row['room_id'] : null,
                'status' => 'scheduled',
                'created_at' => now_utc(),
                'updated_at' => now_utc(),
            ]);
            $count++;
        }

        return ['projected' => $count, 'date' => $date];
    }
}
