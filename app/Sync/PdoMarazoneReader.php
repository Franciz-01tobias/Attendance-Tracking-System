<?php

declare(strict_types=1);

namespace App\Sync;

use App\Core\Database;
use App\Core\ReadOnlyPdo;

final class PdoMarazoneReader implements MarazoneReaderInterface
{
    private ReadOnlyPdo $db;

    public function __construct()
    {
        $this->db = Database::marazoneReadOnly();
    }

    public function validateContract(): array
    {
        $required = config('marazone.required_tables', []);
        $missing = [];

        foreach ($required as $table) {
            $sql = 'SELECT 1 FROM information_schema.tables WHERE table_schema = :schema AND table_name = :table LIMIT 1';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'schema' => env_value('MARAZONE_DB_DATABASE', 'marazone_sms'),
                'table' => $table,
            ]);
            if (!$stmt->fetch()) {
                $missing[] = $table;
            }
        }

        return [
            'ok' => count($missing) === 0,
            'missing_tables' => $missing,
        ];
    }

    public function fetchStudents(): array
    {
        return $this->runConfiguredQuery('students');
    }

    public function fetchLecturers(): array
    {
        return $this->runConfiguredQuery('lecturers');
    }

    public function fetchCourses(): array
    {
        return $this->runConfiguredQuery('courses');
    }

    public function fetchSections(): array
    {
        return $this->runConfiguredQuery('sections');
    }

    public function fetchTimetableSlots(): array
    {
        return $this->runConfiguredQuery('timetable_slots');
    }

    private function runConfiguredQuery(string $key): array
    {
        $query = config('marazone.queries', [])[$key] ?? null;
        if (!$query) {
            return [];
        }

        try {
            $stmt = $this->db->query($query);
            return $stmt ? $stmt->fetchAll() : [];
        } catch (\Throwable) {
            return [];
        }
    }
}
