<?php

declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap/autoload.php';
require_once __DIR__ . '/../../bootstrap/helpers.php';

use App\Core\Request;
use App\Repositories\SessionRepository;
use App\Repositories\StudentRepository;
use App\Repositories\SubmissionRepository;
use App\Repositories\UserRepository;
use App\Services\AttendanceSubmissionService;
use App\Services\DecisionService;
use App\Services\SignedSheetService;

if (!in_array('mysql', PDO::getAvailableDrivers(), true)) {
    echo "workflow_smoke skipped (pdo_mysql not installed)\n";
    exit(0);
}

function fakeRequest(): Request {
    return new Request('POST', '/smoke', [], [], [], ['REMOTE_ADDR' => '127.0.0.1', 'HTTP_USER_AGENT' => 'smoke'], ['accept' => 'application/json']);
}

$users = new UserRepository();
$cr = $users->findByEmail('cr@demo.test');
$lecturer = $users->findByEmail('lecturer@demo.test');

if (!$cr || !$lecturer) {
    throw new RuntimeException('Run seed first.');
}

$sessionRepo = new SessionRepository();
$sessions = $sessionRepo->listForRole($cr);
if (!$sessions) {
    throw new RuntimeException('No sessions found for CR.');
}
$session = $sessions[0];

$subRepo = new SubmissionRepository();
$existing = $subRepo->findBySessionId((int) $session['id']);
if ($existing) {
    echo "Smoke skipped: session already has submission.\n";
    exit(0);
}

$detail = $sessionRepo->findById((int) $session['id']);
$students = (new StudentRepository())->listBySection((int) $detail['section_id']);
$items = array_map(static fn(array $s): array => [
    'student_id' => (int) $s['id'],
    'status' => 'present',
    'note' => null,
], $students);

$submissionId = (new AttendanceSubmissionService())->submit((int) $session['id'], $cr, 'Lecturer taught this session.', $items, fakeRequest());

$tmp = tempnam(sys_get_temp_dir(), 'sheet_');
file_put_contents($tmp, "%PDF-1.4\n% test signed sheet\n");

(new SignedSheetService())->upload($submissionId, $lecturer, [
    'name' => 'signed-sheet.pdf',
    'tmp_name' => $tmp,
    'size' => filesize($tmp),
    'error' => UPLOAD_ERR_OK,
], fakeRequest());

(new DecisionService())->approve($submissionId, $lecturer, fakeRequest());

echo "workflow_smoke passed\n";
