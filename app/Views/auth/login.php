<section class="panel auth-panel">
    <div>
        <h2>Sign In</h2>
        <p>Use your role account to continue.</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert error"><?= h((string) $error) ?></div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <form method="post" action="/auth/login" class="stack gap-16">
        <input type="hidden" name="_csrf" value="<?= h((string) $csrf) ?>">

        <label class="field">
            <span>Email</span>
            <input type="email" name="email" required placeholder="lecturer@demo.test">
        </label>

        <label class="field">
            <span>Password</span>
            <input type="password" name="password" required placeholder="Password123!">
        </label>

        <button class="btn primary" type="submit">Sign In</button>
    </form>

    <div class="demo-note">
        <strong>Demo:</strong> `admin@demo.test`, `lecturer@demo.test`, `cr@demo.test` with password `Password123!`.
    </div>
</section>
