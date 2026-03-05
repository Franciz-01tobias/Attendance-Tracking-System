<section class="panel">
    <h2>Lecturer Taskboard</h2>
    <p>Review CR submissions, upload signed sheets, and approve quickly.</p>
</section>

<section class="panel">
    <h3>Assigned Sessions</h3>
    <table class="table">
        <thead>
        <tr>
            <th>Date</th><th>Course</th><th>Section</th><th>Time</th><th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($sessions as $session): ?>
            <tr>
                <td><?= h((string) $session['session_date']) ?></td>
                <td><?= h((string) $session['course_title']) ?></td>
                <td><?= h((string) $session['section_name']) ?></td>
                <td><?= h((string) $session['starts_at']) ?> - <?= h((string) $session['ends_at']) ?></td>
                <td><a class="btn primary" href="/sessions/<?= h((string) $session['id']) ?>">Review</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
