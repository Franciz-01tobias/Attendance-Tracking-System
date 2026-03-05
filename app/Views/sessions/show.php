<?php
use App\Enums\AttendanceItemStatus;

$submissionId = $submission['id'] ?? null;
?>
<section class="panel">
    <h2><?= h((string) $session['course_title']) ?> - <?= h((string) $session['section_name']) ?></h2>
    <p><?= h((string) $session['session_date']) ?> | <?= h((string) $session['starts_at']) ?> - <?= h((string) $session['ends_at']) ?> | Room <?= h((string) ($session['room_name'] ?? $session['room'] ?? 'TBA')) ?></p>
    <?php if ($submission): ?>
        <span class="pill status-<?= h((string) $submission['status']) ?>">Submission: <?= h((string) $submission['status']) ?></span>
        <span class="pill">Signed sheet: <?= h((string) $submission['signed_sheet_status']) ?></span>
    <?php else: ?>
        <span class="pill status-scheduled">No submission yet</span>
    <?php endif; ?>
</section>

<?php if ($user['role'] === 'cr' && !$submission): ?>
<section class="panel">
    <h3>Submit Attendance</h3>
    <form id="cr-submit-form" action="/sessions/<?= h((string) $session['id']) ?>/submissions" method="post" class="stack gap-16">
        <input type="hidden" name="_csrf" value="<?= h((string) $csrf) ?>">
        <input type="hidden" name="items_json" id="items_json">

        <label class="field">
            <span>Teaching Declaration</span>
            <textarea name="declaration_text" required>Lecturer taught this session.</textarea>
        </label>

        <table class="table" id="cr-attendance-table">
            <thead><tr><th>Student</th><th>Reg No</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td><?= h((string) $student['full_name']) ?></td>
                    <td><?= h((string) $student['reg_no']) ?></td>
                    <td>
                        <select data-student-id="<?= h((string) $student['id']) ?>" class="status-select">
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="late">Late</option>
                        </select>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <button class="btn primary" type="submit">Submit Attendance</button>
    </form>
</section>
<?php endif; ?>

<?php if ($submission): ?>
<section class="panel">
    <h3>Attendance Items</h3>
    <table class="table">
        <thead><tr><th>Student</th><th>Reg No</th><th>Status</th><th>Note</th><?php if ($user['role'] === 'lecturer' && $submission['status'] === 'pending'): ?><th>Action</th><?php endif; ?></tr></thead>
        <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?= h((string) $item['full_name']) ?></td>
                <td><?= h((string) $item['reg_no']) ?></td>
                <td>
                    <?php if ($user['role'] === 'lecturer' && $submission['status'] === 'pending'): ?>
                        <select id="item-status-<?= h((string) $item['id']) ?>">
                            <?php foreach (AttendanceItemStatus::all() as $status): ?>
                                <option value="<?= h($status) ?>" <?= $item['status'] === $status ? 'selected' : '' ?>><?= h(ucfirst($status)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <?= h((string) $item['status']) ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($user['role'] === 'lecturer' && $submission['status'] === 'pending'): ?>
                        <input id="item-note-<?= h((string) $item['id']) ?>" value="<?= h((string) $item['note']) ?>">
                    <?php else: ?>
                        <?= h((string) $item['note']) ?>
                    <?php endif; ?>
                </td>
                <?php if ($user['role'] === 'lecturer' && $submission['status'] === 'pending'): ?>
                    <td>
                        <button class="btn subtle" onclick="updateAttendanceItem(<?= (int) $submissionId ?>, <?= (int) $item['id'] ?>, '<?= h((string) $csrf) ?>')">Save</button>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php if ($user['role'] === 'lecturer' && $submission['status'] === 'pending'): ?>
<section class="grid cards-2">
    <article class="panel">
        <h3>Signed Sheet Upload</h3>
        <?php if ($signedSheet): ?>
            <p>Current: <?= h((string) $signedSheet['original_name']) ?> (v<?= h((string) $signedSheet['version_no']) ?>)</p>
            <a class="btn subtle" href="/submissions/<?= h((string) $submissionId) ?>/signed-sheet">Download Active Sheet</a>
        <?php else: ?>
            <p>No signed sheet uploaded yet.</p>
        <?php endif; ?>

        <form action="/submissions/<?= h((string) $submissionId) ?>/signed-sheet" method="post" enctype="multipart/form-data" class="stack gap-12">
            <input type="hidden" name="_csrf" value="<?= h((string) $csrf) ?>">
            <input type="file" name="signed_sheet" accept=".pdf,.jpg,.jpeg,.png" required>
            <button type="submit" class="btn primary">Upload / Replace</button>
        </form>
    </article>

    <article class="panel">
        <h3>Decision</h3>
        <form method="post" action="/submissions/<?= h((string) $submissionId) ?>/approve" class="stack gap-12">
            <input type="hidden" name="_csrf" value="<?= h((string) $csrf) ?>">
            <button class="btn success" type="submit">Approve</button>
        </form>

        <form method="post" action="/submissions/<?= h((string) $submissionId) ?>/reject" class="stack gap-12">
            <input type="hidden" name="_csrf" value="<?= h((string) $csrf) ?>">
            <label class="field">
                <span>Rejection Reason</span>
                <textarea name="lecturer_comment" required></textarea>
            </label>
            <button class="btn danger" type="submit">Reject</button>
        </form>
    </article>
</section>
<?php endif; ?>

<?php if ($user['role'] === 'admin'): ?>
<section class="panel">
    <h3>Admin Override</h3>
    <form method="post" action="/submissions/<?= h((string) $submissionId) ?>/override" class="grid cards-3 align-end">
        <input type="hidden" name="_csrf" value="<?= h((string) $csrf) ?>">
        <label class="field">
            <span>Action</span>
            <select name="action" required>
                <option value="approve">Approve</option>
                <option value="reject">Reject</option>
            </select>
        </label>
        <label class="field">
            <span>Reason</span>
            <input type="text" name="reason" required>
        </label>
        <button class="btn warning" type="submit">Apply Override</button>
    </form>
</section>
<?php endif; ?>
<?php endif; ?>
