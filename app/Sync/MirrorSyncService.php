<?php

declare(strict_types=1);

namespace App\Sync;

use App\Core\Database;

final class MirrorSyncService implements MirrorSyncServiceInterface
{
    public function __construct(private readonly MarazoneReaderInterface $reader = new PdoMarazoneReader())
    {
    }

    public function run(): array
    {
        $contract = $this->reader->validateContract();
        if (!$contract['ok']) {
            return [
                'ok' => false,
                'contract' => $contract,
                'message' => 'Marazone schema contract failed',
            ];
        }

        $db = Database::default();
        $db->beginTransaction();

        try {
            $summary = [
                'students' => $this->syncStudents($this->reader->fetchStudents()),
                'lecturers' => $this->syncLecturers($this->reader->fetchLecturers()),
                'courses' => $this->syncCourses($this->reader->fetchCourses()),
                'sections' => $this->syncSections($this->reader->fetchSections()),
                'timetable_slots' => $this->syncTimetableSlots($this->reader->fetchTimetableSlots()),
            ];

            $db->commit();
            return ['ok' => true, 'summary' => $summary, 'contract' => $contract];
        } catch (\Throwable $e) {
            $db->rollBack();
            return ['ok' => false, 'message' => $e->getMessage(), 'contract' => $contract];
        }
    }

    private function syncStudents(array $rows): int
    {
        $sql = <<<'SQL'
INSERT INTO ats_mz_students (source_id, reg_no, full_name, email, section_source_id, active, row_checksum, synced_at)
VALUES (:source_id, :reg_no, :full_name, :email, :section_source_id, :active, :row_checksum, :synced_at)
ON DUPLICATE KEY UPDATE
reg_no = VALUES(reg_no),
full_name = VALUES(full_name),
email = VALUES(email),
section_source_id = VALUES(section_source_id),
active = VALUES(active),
row_checksum = VALUES(row_checksum),
synced_at = VALUES(synced_at)
SQL;
        return $this->upsertGeneric($sql, $rows, static function (array $row): array {
            $payload = [
                'source_id' => (string) ($row['id'] ?? ''),
                'reg_no' => (string) ($row['reg_no'] ?? ''),
                'full_name' => (string) ($row['full_name'] ?? ''),
                'email' => $row['email'] ?? null,
                'section_source_id' => (string) ($row['section_id'] ?? ''),
                'active' => (int) ($row['active'] ?? 1),
            ];
            $payload['row_checksum'] = hash('sha256', (string) json_encode($payload));
            $payload['synced_at'] = now_utc();
            return $payload;
        });
    }

    private function syncLecturers(array $rows): int
    {
        $sql = <<<'SQL'
INSERT INTO ats_mz_lecturers (source_id, full_name, email, active, row_checksum, synced_at)
VALUES (:source_id, :full_name, :email, :active, :row_checksum, :synced_at)
ON DUPLICATE KEY UPDATE
full_name = VALUES(full_name),
email = VALUES(email),
active = VALUES(active),
row_checksum = VALUES(row_checksum),
synced_at = VALUES(synced_at)
SQL;
        return $this->upsertGeneric($sql, $rows, static function (array $row): array {
            $payload = [
                'source_id' => (string) ($row['id'] ?? ''),
                'full_name' => (string) ($row['full_name'] ?? ''),
                'email' => $row['email'] ?? null,
                'active' => (int) ($row['active'] ?? 1),
            ];
            $payload['row_checksum'] = hash('sha256', (string) json_encode($payload));
            $payload['synced_at'] = now_utc();
            return $payload;
        });
    }

    private function syncCourses(array $rows): int
    {
        $sql = <<<'SQL'
INSERT INTO ats_mz_courses (source_id, code, title, lecturer_source_id, active, row_checksum, synced_at)
VALUES (:source_id, :code, :title, :lecturer_source_id, :active, :row_checksum, :synced_at)
ON DUPLICATE KEY UPDATE
code = VALUES(code),
title = VALUES(title),
lecturer_source_id = VALUES(lecturer_source_id),
active = VALUES(active),
row_checksum = VALUES(row_checksum),
synced_at = VALUES(synced_at)
SQL;
        return $this->upsertGeneric($sql, $rows, static function (array $row): array {
            $payload = [
                'source_id' => (string) ($row['id'] ?? ''),
                'code' => (string) ($row['code'] ?? ''),
                'title' => (string) ($row['title'] ?? ''),
                'lecturer_source_id' => isset($row['lecturer_id']) ? (string) $row['lecturer_id'] : null,
                'active' => (int) ($row['active'] ?? 1),
            ];
            $payload['row_checksum'] = hash('sha256', (string) json_encode($payload));
            $payload['synced_at'] = now_utc();
            return $payload;
        });
    }

    private function syncSections(array $rows): int
    {
        $sql = <<<'SQL'
INSERT INTO ats_mz_sections (source_id, course_source_id, name, semester, academic_year, row_checksum, synced_at)
VALUES (:source_id, :course_source_id, :name, :semester, :academic_year, :row_checksum, :synced_at)
ON DUPLICATE KEY UPDATE
course_source_id = VALUES(course_source_id),
name = VALUES(name),
semester = VALUES(semester),
academic_year = VALUES(academic_year),
row_checksum = VALUES(row_checksum),
synced_at = VALUES(synced_at)
SQL;
        return $this->upsertGeneric($sql, $rows, static function (array $row): array {
            $payload = [
                'source_id' => (string) ($row['id'] ?? ''),
                'course_source_id' => (string) ($row['course_id'] ?? ''),
                'name' => (string) ($row['name'] ?? ''),
                'semester' => (string) ($row['semester'] ?? ''),
                'academic_year' => (string) ($row['academic_year'] ?? ''),
            ];
            $payload['row_checksum'] = hash('sha256', (string) json_encode($payload));
            $payload['synced_at'] = now_utc();
            return $payload;
        });
    }

    private function syncTimetableSlots(array $rows): int
    {
        $sql = <<<'SQL'
INSERT INTO ats_mz_timetable_slots (source_id, section_source_id, day_of_week, start_time, end_time, room, active, row_checksum, synced_at)
VALUES (:source_id, :section_source_id, :day_of_week, :start_time, :end_time, :room, :active, :row_checksum, :synced_at)
ON DUPLICATE KEY UPDATE
section_source_id = VALUES(section_source_id),
day_of_week = VALUES(day_of_week),
start_time = VALUES(start_time),
end_time = VALUES(end_time),
room = VALUES(room),
active = VALUES(active),
row_checksum = VALUES(row_checksum),
synced_at = VALUES(synced_at)
SQL;

        return $this->upsertGeneric($sql, $rows, static function (array $row): array {
            $payload = [
                'source_id' => (string) ($row['id'] ?? ''),
                'section_source_id' => (string) ($row['section_id'] ?? ''),
                'day_of_week' => (int) ($row['day_of_week'] ?? 1),
                'start_time' => (string) ($row['start_time'] ?? '08:00:00'),
                'end_time' => (string) ($row['end_time'] ?? '09:00:00'),
                'room' => (string) ($row['room'] ?? ''),
                'active' => (int) ($row['active'] ?? 1),
            ];
            $payload['row_checksum'] = hash('sha256', (string) json_encode($payload));
            $payload['synced_at'] = now_utc();
            return $payload;
        });
    }

    private function upsertGeneric(string $sql, array $rows, callable $mapper): int
    {
        $stmt = Database::default()->prepare($sql);
        $count = 0;
        foreach ($rows as $row) {
            $payload = $mapper($row);
            if (($payload['source_id'] ?? '') === '') {
                continue;
            }
            $stmt->execute($payload);
            $count++;
        }

        return $count;
    }
}
