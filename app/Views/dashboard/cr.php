<section class="panel">
    <h2>CR Workflow</h2>
    <p>Submit attendance once per session with complete roster coverage.</p>
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
                <td><a class="btn primary" href="/sessions/<?= h((string) $session['id']) ?>">Capture</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
