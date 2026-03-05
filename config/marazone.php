<?php

declare(strict_types=1);

return [
    // Adjust these queries to match your exact Marazone schema.
    'queries' => [
        'students' => 'SELECT id, reg_no, full_name, email, section_id, active FROM students',
        'lecturers' => 'SELECT id, full_name, email, active FROM lecturers',
        'courses' => 'SELECT id, code, title, lecturer_id, active FROM courses',
        'sections' => 'SELECT id, course_id, name, semester, academic_year FROM sections',
        'timetable_slots' => 'SELECT id, section_id, day_of_week, start_time, end_time, room, active FROM timetable_slots',
    ],
    'required_tables' => [
        'students',
        'lecturers',
        'courses',
        'sections',
        'timetable_slots',
    ],
];
