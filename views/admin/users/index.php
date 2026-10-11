<?php

use App\Core\Csrf;

/** إدارة المستخدمين — للمسؤولة فقط. المتغير: $users */
$roleLabels = ['admin' => 'مسؤولة', 'seller' => 'بائعة', 'customer' => 'عميلة'];
?>
<section class="container section">
    <nav class="breadcrumb" aria-label="مسار التنقل">
        <a href="<?= e(url('/admin')) ?>"><?= e(__('admin.dashboard')) ?></a>
        <span aria-hidden="true">/</span>
        <span><?= e(__('admin.users')) ?></span>
    </nav>

    <div class="page-head">
        <h1><?= e(__('admin.users')) ?></h1>
        <p class="muted"><?= e(__('admin.users_count', ['count' => (string) count($users)])) ?></p>
    </div>

    <div class="admin-shell">
        <?= \App\Core\View::partial('admin/_nav', []) ?>

        <div class="admin-content">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th><?= e(__('admin.col_user')) ?></th>
                        <th><?= e(__('auth.phone')) ?></th>
                        <th><?= e(__('admin.col_joined')) ?></th>
                        <th><?= e(__('admin.col_role')) ?></th>
                        <th><?= e(__('admin.col_actions')) ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr class="<?= $user['is_active'] ? '' : 'row-disabled' ?>">
                            <td>
                                <strong><?= e($user['name']) ?></strong><br>
                                <span class="muted small" dir="ltr"><?= e($user['email']) ?></span>
                            </td>
                            <td dir="auto"><?= e($user['phone']) ?></td>
                            <td class="muted"><?= e($user['created_at']) ?></td>
                            <td colspan="2">
                                <form method="post" action="<?= e(url('admin/users/' . $user['id'] . '/role')) ?>"
                                      class="inline-form">
                                    <?= Csrf::field() ?>
                                    <select name="role">
                                        <?php foreach ($roleLabels as $key => $label): ?>
                                            <option value="<?= e($key) ?>" <?= $user['role'] === $key ? 'selected' : '' ?>>
                                                <?= e($label) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select name="is_active">
                                        <option value="1" <?= $user['is_active'] ? 'selected' : '' ?>><?= e(__('admin.active')) ?></option>
                                        <option value="0" <?= !$user['is_active'] ? 'selected' : '' ?>><?= e(__('admin.disabled')) ?></option>
                                    </select>
                                    <button type="submit" class="btn btn-ghost"><?= e(__('buttons.save')) ?></button>
                                    <?php if (\App\Core\Auth::id() === (int) $user['id']): ?>
                                        <span class="muted small"><?= e(__('admin.you')) ?></span>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>