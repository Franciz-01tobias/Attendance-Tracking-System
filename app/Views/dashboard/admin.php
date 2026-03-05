<section class="grid cards-3">
    <article class="panel metric">
        <h3>Control Tower</h3>
        <p>Oversee escalations, overrides, and audit confidence.</p>
        <a class="btn subtle" href="/reports/attendance">Attendance API</a>
    </article>
    <article class="panel metric">
        <h3>Turnaround</h3>
        <p>Check lecturer approval speed trends.</p>
        <a class="btn subtle" href="/reports/approval-turnaround">Turnaround API</a>
    </article>
    <article class="panel metric">
        <h3>Escalations</h3>
        <p>Monitor pending submissions past SLA.</p>
        <a class="btn subtle" href="/reports/escalations">Escalations API</a>
    </article>
</section>

<section class="panel">
    <h3>Recent Sessions</h3>
    <table class="table">
        <thead>
        <tr>
            <th>Date</th><th>Course</th><th>Section</th><th>Status</th><th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($sessions as $session): ?>
            <tr>
                <td><?= h((string) $session['session_date']) ?></td>
                <td><?= h((string) $session['course_title']) ?></td>
                <td><?= h((string) $session['section_name']) ?></td>
                <td><span class="pill status-<?= h((string) $session['status']) ?>"><?= h((string) $session['status']) ?></span></td>
                <td><a class="btn subtle" href="/sessions/<?= h((string) $session['id']) ?>">Open</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
