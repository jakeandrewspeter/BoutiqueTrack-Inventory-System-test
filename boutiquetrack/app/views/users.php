<div class="page-heading">
    <div><div class="eyebrow">ACCESS & RESPONSIBILITY</div><h1>Team access</h1><p>Give staff an account that matches their role in the shop.</p></div>
</div>
<div class="users-layout">
    <section class="panel">
        <div class="panel-heading"><div><h2>Team members</h2><p><?= count($users) ?> account<?= count($users) === 1 ? '' : 's' ?></p></div></div>
        <div class="team-list"><?php foreach ($users as $member): ?><div class="team-row">
            <span class="user-avatar"><?= h(strtoupper(substr($member['full_name'], 0, 1))) ?></span>
            <span class="compact-copy"><strong><?= h($member['full_name']) ?></strong><small>@<?= h($member['username']) ?> · <?= h(auth_label($member['role'])) ?></small></span>
            <span class="status-pill <?= $member['is_active'] ? 'status-active' : 'status-inactive' ?>"><?= $member['is_active'] ? 'Active' : 'Disabled' ?></span>
            <?php if ((int)$member['id'] !== (int)$user['id']): ?><form method="post" action="<?= h(app_path('users')) ?>" data-confirm="<?= $member['is_active'] ? 'Disable this account? They will be signed out on their next request.' : 'Re-enable this account?' ?>">
                <?= csrf_field() ?><input type="hidden" name="action" value="toggle-user"><input type="hidden" name="user_id" value="<?= (int)$member['id'] ?>"><button class="text-button" type="submit"><?= $member['is_active'] ? 'Disable' : 'Enable' ?></button>
            </form><?php else: ?><span class="muted small-text">You</span><?php endif; ?>
        </div><?php endforeach; ?></div>
    </section>
    <section class="panel add-user-panel">
        <div class="panel-heading"><div><h2>Add a team member</h2><p>They will sign in with their own account.</p></div></div>
        <form method="post" action="<?= h(app_path('users')) ?>" class="form-stack">
            <?= csrf_field() ?><input type="hidden" name="action" value="create-user">
            <label>Full name<input name="full_name" required maxlength="120" placeholder="Staff member name"></label>
            <label>Username<input name="username" required minlength="3" maxlength="60" pattern="[A-Za-z0-9._-]+" placeholder="e.g. cashier1"></label>
            <label>Password<input name="password" type="password" required minlength="10" autocomplete="new-password" placeholder="At least 10 characters"></label>
            <label>Role<select name="role"><option value="cashier">Cashier</option><option value="admin">Administrator</option></select></label>
            <div class="role-note"><strong>Cashiers</strong><span>Can make sales, see their receipts, and view the dashboard.</span></div>
            <button class="button button-primary button-wide" type="submit">Add team member <span>→</span></button>
        </form>
    </section>
</div>