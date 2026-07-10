<?php
/**
 * 文件：admin/users.php
 * 作用：用户管理（列表、OAuth 绑定、封禁/删除）
 */

require_once __DIR__ . '/init.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!AuthSecurity::validateCsrf($token)) {
        $error = '请求无效，请刷新页面后重试';
    } else {
        $action = isset($_POST['action']) ? (string) $_POST['action'] : '';
        $userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;

        if ($userId <= 0) {
            $error = '无效用户';
        } elseif ($action === 'ban') {
            $result = UserManager::setStatus($userId, 0);
            $success = $result === true ? '用户已封禁' : $result;
            if ($result !== true) {
                $error = $result;
                $success = '';
            }
        } elseif ($action === 'unban') {
            $result = UserManager::setStatus($userId, 1);
            $success = $result === true ? '用户已解封' : $result;
            if ($result !== true) {
                $error = $result;
                $success = '';
            }
        } elseif ($action === 'delete') {
            $result = UserManager::delete($userId);
            $success = $result === true ? '用户已删除' : $result;
            if ($result !== true) {
                $error = $result;
                $success = '';
            }
        } else {
            $error = '无效操作';
        }
    }
}

$users = UserManager::all();
$userCount = count($users);
$csrfToken = AuthSecurity::csrfToken();

/**
 * @param array $row
 * @return string
 */
function vs_users_oauth_badges(array $row)
{
    $badges = array();
    if (trim((string) $row['oauth_qq_openid']) !== '') {
        $badges[] = '<span class="vs-oauth-badge vs-oauth-badge--qq">QQ</span>';
    }
    if (trim((string) $row['oauth_gitee_id']) !== '') {
        $badges[] = '<span class="vs-oauth-badge vs-oauth-badge--gitee">Gitee</span>';
    }
    if (empty($badges)) {
        return '<span class="vs-oauth-badge vs-oauth-badge--none">未绑定</span>';
    }
    return implode(' ', $badges);
}

/**
 * @param array  $row
 * @param string $base
 * @return string
 */
function vs_users_oauth_icons(array $row, $base)
{
    $icons = array();
    if (trim((string) $row['oauth_qq_openid']) !== '') {
        $icons[] = '<img src="' . vs_e($base) . '/assets/img/QQ.svg" alt="QQ" class="vs-user-oauth-icon" width="18" height="18">';
    }
    if (trim((string) $row['oauth_gitee_id']) !== '') {
        $icons[] = '<img src="' . vs_e($base) . '/assets/img/gitee.svg" alt="Gitee" class="vs-user-oauth-icon" width="18" height="18">';
    }
    if (empty($icons)) {
        return '<span class="vs-user-oauth-none">未绑定</span>';
    }
    return implode('', $icons);
}

/**
 * @param string|null $datetime
 * @return string
 */
function vs_users_format_time($datetime)
{
    if ($datetime === null || trim((string) $datetime) === '') {
        return '从未登录';
    }
    return (string) $datetime;
}

/**
 * @param int    $userId
 * @param string $action ban|unban|delete
 * @param string $label
 * @param string $class
 * @param string $csrfToken
 * @param bool   $confirmDelete
 * @return string
 */
function vs_users_action_button($userId, $action, $label, $class, $csrfToken, $confirmDelete = false)
{
    $confirmAttr = $confirmDelete ? ' data-confirm-delete="1"' : '';
    return '<form method="post" action="" class="vs-user-action-form"' . $confirmAttr . '>'
        . '<input type="hidden" name="csrf_token" value="' . vs_e($csrfToken) . '">'
        . '<input type="hidden" name="user_id" value="' . (int) $userId . '">'
        . '<input type="hidden" name="action" value="' . vs_e($action) . '">'
        . '<button type="submit" class="vs-btn vs-btn--pill ' . vs_e($class) . '">' . vs_e($label) . '</button>'
        . '</form>';
}

vs_admin_layout_start('用户管理', 'users');
?>

<div class="vs-panel">
    <div class="vs-panel__header">
        <h2 class="vs-panel__title">用户列表</h2>
        <p class="vs-panel__desc">共 <?php echo (int) $userCount; ?> 位用户</p>
    </div>

    <?php if ($error): ?>
        <div class="vs-alert vs-alert--error"><?php echo vs_e($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="vs-alert vs-alert--success"><?php echo vs_e($success); ?></div>
    <?php endif; ?>

    <?php if ($userCount === 0): ?>
        <?php vs_render_notice('info', '', '暂无注册用户', array('compact' => true)); ?>
    <?php else: ?>
        <div class="vs-users-desktop vs-table-wrap">
            <table class="vs-table vs-users-table">
                <thead>
                    <tr>
                        <th>用户</th>
                        <th>邮箱</th>
                        <th>第三方绑定</th>
                        <th>注册时间</th>
                        <th>最后登录</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $row): ?>
                        <?php
                        $avatar = UserAvatar::resolve($row);
                        $active = (int) $row['status'] === 1;
                        $uid = (int) $row['id'];
                        ?>
                        <tr class="<?php echo $active ? '' : 'vs-users-row--banned'; ?>">
                            <td>
                                <div class="vs-users-cell-user">
                                    <img src="<?php echo vs_e($avatar); ?>" alt="" class="vs-users-avatar">
                                    <div>
                                        <div class="vs-users-name">
                                            <span class="vs-users-id">ID <?php echo $uid; ?></span>
                                            <?php echo vs_e($row['username']); ?>
                                            <?php if (!$active): ?>
                                                <span class="vs-users-banned-tag">已封禁</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo vs_e($row['email']); ?></td>
                            <td><?php echo vs_users_oauth_badges($row); ?></td>
                            <td><?php echo vs_e($row['created_at']); ?></td>
                            <td><?php echo vs_e(vs_users_format_time(isset($row['last_login_at']) ? $row['last_login_at'] : null)); ?></td>
                            <td>
                                <div class="vs-users-actions">
                                    <?php if ($active): ?>
                                        <?php echo vs_users_action_button($uid, 'ban', '封禁', 'vs-btn--pill-danger', $csrfToken); ?>
                                    <?php else: ?>
                                        <?php echo vs_users_action_button($uid, 'unban', '解封', 'vs-btn--pill-primary', $csrfToken); ?>
                                    <?php endif; ?>
                                    <?php echo vs_users_action_button($uid, 'delete', '删除', 'vs-btn--pill-danger', $csrfToken, true); ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="vs-users-mobile">
            <?php foreach ($users as $row): ?>
                <?php
                $avatar = UserAvatar::resolve($row);
                $active = (int) $row['status'] === 1;
                $uid = (int) $row['id'];
                ?>
                <article class="vs-user-card<?php echo $active ? '' : ' vs-user-card--banned'; ?>">
                    <div class="vs-user-card__head">
                        <img src="<?php echo vs_e($avatar); ?>" alt="" class="vs-users-avatar">
                        <div class="vs-user-card__main">
                            <div class="vs-user-card__top">
                                <div class="vs-users-name">
                                    <span class="vs-users-id">ID <?php echo $uid; ?></span>
                                    <?php echo vs_e($row['username']); ?>
                                    <?php if (!$active): ?>
                                        <span class="vs-users-banned-tag">已封禁</span>
                                    <?php endif; ?>
                                </div>
                                <div class="vs-user-card__oauth">
                                    <?php echo vs_users_oauth_icons($row, $vsBase); ?>
                                </div>
                            </div>
                            <div class="vs-users-meta vs-user-card__email"><?php echo vs_e($row['email']); ?></div>
                        </div>
                    </div>
                    <div class="vs-user-card__actions">
                        <?php if ($active): ?>
                            <?php echo vs_users_action_button($uid, 'ban', '封禁', 'vs-btn--pill-danger', $csrfToken); ?>
                        <?php else: ?>
                            <?php echo vs_users_action_button($uid, 'unban', '解封', 'vs-btn--pill-primary', $csrfToken); ?>
                        <?php endif; ?>
                        <?php echo vs_users_action_button($uid, 'delete', '删除', 'vs-btn--pill-danger', $csrfToken, true); ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php vs_admin_layout_end(array('users.js')); ?>
