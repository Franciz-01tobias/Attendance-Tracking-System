<?php

declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap/autoload.php';
require_once __DIR__ . '/../../bootstrap/helpers.php';

use App\Policies\SubmissionPolicy;

$policy = new SubmissionPolicy();

$admin = ['id' => 1, 'role' => 'admin'];
$lecturer = ['id' => 2, 'role' => 'lecturer'];
$cr = ['id' => 3, 'role' => 'cr'];

$submission = ['lecturer_user_id' => 2, 'cr_user_id' => 3, 'status' => 'pending'];

assert($policy->canView($admin, $submission) === true);
assert($policy->canView($lecturer, $submission) === true);
assert($policy->canView($cr, $submission) === true);
assert($policy->canEditItems($lecturer, $submission) === true);
assert($policy->canUploadSheet($lecturer, $submission) === true);
assert($policy->canOverride($admin) === true);

echo "submission_policy_test passed\n";
