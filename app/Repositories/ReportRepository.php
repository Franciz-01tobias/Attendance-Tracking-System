<?php

declare(strict_types=1);

namespace App\Repositories;

final class ReportRepository extends BaseRepository
{
    public function attendanceSummary(): array
    {
        $sql = <<<'SQL'
SELECT
    s.session_date,
    SUM(CASE WHEN ai.status = 'present' THEN 1 ELSE 0 END) AS total_present,
    SUM(CASE WHEN ai.status = 'absent' THEN 1 ELSE 0 END) AS total_absent,
    SUM(CASE WHEN ai.status = 'late' THEN 1 ELSE 0 END) AS total_late
FROM ats_attendance_items ai
JOIN ats_attendance_submissions sub ON sub.id = ai.submission_id
JOIN ats_sessions s ON s.id = sub.session_id
GROUP BY s.session_date
ORDER BY s.session_date DESC
LIMIT 30
SQL;
        return $this->db()->query($sql)->fetchAll();
    }

    public function turnaroundSummary(): array
    {
        $sql = <<<'SQL'
SELECT
    DATE(sub.submitted_at) AS submitted_date,
    AVG(TIMESTAMPDIFF(MINUTE, sub.submitted_at, sub.lecturer_decision_at)) AS avg_minutes
FROM ats_attendance_submissions sub
WHERE sub.lecturer_decision_at IS NOT NULL
GROUP BY DATE(sub.submitted_at)
ORDER BY submitted_date DESC
LIMIT 30
SQL;
        return $this->db()->query($sql)->fetchAll();
    }

    public function escalationSummary(): array
    {
        $sql = <<<'SQL'
SELECT
    DATE(es.escalated_at) AS escalated_date,
    COUNT(*) AS escalated_count
FROM ats_escalations es
GROUP BY DATE(es.escalated_at)
ORDER BY escalated_date DESC
LIMIT 30
SQL;
        return $this->db()->query($sql)->fetchAll();
    }
}
