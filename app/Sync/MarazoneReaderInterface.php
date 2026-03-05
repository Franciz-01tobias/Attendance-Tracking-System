<?php

declare(strict_types=1);

namespace App\Sync;

interface MarazoneReaderInterface
{
    public function validateContract(): array;

    public function fetchStudents(): array;

    public function fetchLecturers(): array;

    public function fetchCourses(): array;

    public function fetchSections(): array;

    public function fetchTimetableSlots(): array;
}
