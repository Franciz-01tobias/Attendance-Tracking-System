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
SELECT mts.*, sec.id AS local_section_id
FROM mz_timetable_slots mts
JOIN sections sec ON sec.marazone_section_id = mts.section_source_id
WHERE mts.day_of_week = :dow
  AND mts.active = 1
SQL;

        $stmt = Database::default()->prepare($sql);
        $stmt->execute(['dow' => $day]);
        $rows = $stmt->fetchAll();

        $insert = Database::default()->prepare(
            'INSERT INTO sessions (marazone_session_id, section_id, session_date, starts_at, ends_at, room, status, created_at, updated_at)
             VALUES (:msid, :section_id, :session_date, :starts_at, :ends_at, :room, :status, :created_at, :updated_at)
             ON DUPLICATE KEY UPDATE starts_at = VALUES(starts_at), ends_at = VALUES(ends_at), room = VALUES(room), status = VALUES(status), updated_at = VALUES(updated_at)'
        );

        $count = 0;
        foreach ($rows as $row) {
            $msid = sprintf('%s-%s', $row['source_id'], $date);
            $insert->execute([
                'msid' => $msid,
                'section_id' => (int) $row['local_section_id'],
                'session_date' => $date,
                'starts_at' => $row['start_time'],
                'ends_at' => $row['end_time'],
                'room' => $row['room'],
                'status' => 'scheduled',
                'created_at' => now_utc(),
                'updated_at' => now_utc(),
            ]);
            $count++;
        }

        return ['projected' => $count, 'date' => $date];
    }
}
