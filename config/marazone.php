<?php

declare(strict_types=1);

return [
    // Read-only source queries mapped to the canonical mirror fields used by sync mappers.
    'queries' => [
        'students' => <<<'SQL'
SELECT
    s.student_id AS id,
    COALESCE(s.admission_no, s.form4_reg_no, CAST(s.student_id AS CHAR)) AS reg_no,
    COALESCE(NULLIF(CONCAT_WS(' ', u.first_name, NULLIF(u.middle_name, ''), u.last_name), ''), CONCAT('Student ', s.student_id)) AS full_name,
    u.email,
    CONCAT(s.course_id, '-', s.current_stage_no, '-', COALESCE(s.qualification_level_id, 0), '-0') AS section_id,
    CASE WHEN s.status = 'ACTIVE' AND u.status = 'ACTIVE' THEN 1 ELSE 0 END AS active
FROM students s
JOIN users u ON u.user_id = s.user_id
SQL,
        'lecturers' => <<<'SQL'
SELECT
    u.user_id AS id,
    COALESCE(NULLIF(CONCAT_WS(' ', u.first_name, NULLIF(u.middle_name, ''), u.last_name), ''), CONCAT('Lecturer ', u.user_id)) AS full_name,
    u.email,
    CASE WHEN u.status = 'ACTIVE' THEN 1 ELSE 0 END AS active
FROM users u
WHERE u.role = 'LECTURER'
SQL,
        'courses' => <<<'SQL'
SELECT
    c.course_id AS id,
    c.course_code AS code,
    c.course_name AS title,
    NULL AS lecturer_id,
    c.is_active AS active
FROM courses c
SQL,
        'sections' => <<<'SQL'
SELECT
    CONCAT(cqs.course_id, '-', cqs.stage_no, '-', cqs.qualification_level_id, '-0') AS id,
    cqs.course_id,
    CONCAT('Stage ', cqs.stage_no, ' / ', COALESCE(ql.level_code, CONCAT('QL ', cqs.qualification_level_id))) AS name,
    '0' AS semester,
    '' AS academic_year
FROM course_qualification_stages cqs
LEFT JOIN qualification_levels ql ON ql.qualification_level_id = cqs.qualification_level_id
WHERE cqs.is_active = 1
SQL,
        'timetable_slots' => <<<'SQL'
SELECT
    ts.slot_id AS id,
    CONCAT(ts.course_id, '-', COALESCE(ts.stage_no, 0), '-', COALESCE(ts.qualification_level_id, 0), '-', COALESCE(ts.semester_id, 0)) AS section_id,
    ts.day_of_week,
    ts.start_time,
    ts.end_time,
    COALESCE(ar.room_name, ar.room_code, CAST(ts.room_id AS CHAR)) AS room,
    CASE WHEN ts.status = 'ACTIVE' AND ts.version_status = 'PUBLISHED' THEN 1 ELSE 0 END AS active
FROM timetable_slots ts
LEFT JOIN academic_rooms ar ON ar.room_id = ts.room_id
SQL,
    ],
    'required_tables' => [
        'users',
        'students',
        'courses',
        'course_qualification_stages',
        'qualification_levels',
        'timetable_slots',
        'academic_rooms',
    ],
];
