<?php
/**
 * 文件：admin/users.php
 * 作用：用户管理（列表、OAuth 绑定、封禁/删除）
 */

require_once __DIR__ . '/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    vs_require_secure_post();

    $action = isset($_POST['action']) ? (string) $_POST['action'] : '';
    $userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;

    if ($userId <= 0) {
        AjaxResponse::error('无效用户');
    }

    if ($action === 'ban') {
        $result = UserManager::setStatus($userId, 0);
        if ($result !== true) {
            AjaxResponse::error($result);
        }
        AjaxResponse::success('用户已封禁', array('action' => 'ban', 'user_id' => $userId, 'status' => 0));
    }

    if ($action === 'unban') {
        $result = UserManager::setStatus($userId, 1);
        if ($result !== true) {
            AjaxResponse::error($result);
        }
        AjaxResponse::success('用户已解封', array('action' => 'unban', 'user_id' => $userId, 'status' => 1));
    }

    if ($action === 'delete') {
        $result = UserManager::delete($userId);
        if ($result !== true) {
            AjaxResponse::error($result);
        }
        AjaxResponse::success('用户已删除', array('action' => 'delete', 'user_id' => $userId));
    }

    AjaxResponse::error('无效操作', 400);
}

$users = UserManager::all();
$userCount = count($users);

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
 * @param bool   $confirmDelete
 * @return string
 */
function vs_users_action_button($userId, $action, $label, $class, $confirmDelete = false)
{
    $confirmAttr = $confirmDelete ? ' data-confirm-delete="1"' : '';
    return '<button type="button" class="vs-btn vs-btn--pill ' . vs_e($class) . ' vs-user-action-btn"'
        . ' data-user-action="' . vs_e($action) . '" data-user-id="' . (int) $userId . '"' . $confirmAttr . '>'
        . vs_e($label) . '</button>';
}

/**
 * @param int  $userId
 * @param bool $active
 * @return string
 */
function vs_users_action_group($userId, $active)
{
    $html = '<div class="vs-users-actions">';
    if ($active) {
        $html .= vs_users_action_button($userId, 'ban', '封禁', 'vs-btn--pill-danger');
    } else {
        $html .= vs_users_action_button($userId, 'unban', '解封', 'vs-btn--pill-primary');
    }
    $html .= vs_users_action_button($userId, 'delete', '删除', 'vs-btn--pill-danger', true);
    $html .= '</div>';
    return $html;
}

/**
 * @param array $row
 * @return string
 */
function vs_users_search_blob(array $row)
{
    $parts = array(
        (string) (int) $row['id'],
        (string) $row['username'],
        (string) $row['email'],
    );
    return strtolower(implode(' ', $parts));
}

vs_admin_layout_start('用户管理', 'users');
?>

<div class="vs-panel">
    <div class="vs-panel__header vs-users-list-head">
        <div class="vs-users-list-head__text">
            <h2 class="vs-panel__title">用户列表</h2>
            <p class="vs-panel__desc" id="usersCountDesc" data-total="<?php echo (int) $userCount; ?>">共 <?php echo (int) $userCount; ?> 位用户</p>
        </div>
        <div class="vs-users-search" id="usersSearch">
            <button type="button" class="vs-users-search__toggle" id="usersSearchToggle" aria-label="展开搜索" aria-expanded="false">
                <i class="vs-icon vs-icon--search" aria-hidden="true"></i>
            </button>
            <input type="search" class="vs-users-search__input" id="usersSearchInput"
                   placeholder="搜索用户名、邮箱或 ID" autocomplete="off" enterkeyhint="search">
        </div>
    </div>

    <div id="usersSearchEmpty" class="vs-users-search-empty" hidden>
        <?php vs_render_notice('info', '', '未找到匹配的用户', array('compact' => true)); ?>
    </div>

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
                        <tr class="<?php echo $active ? '' : 'vs-users-row--banned'; ?>" data-user-row="<?php echo $uid; ?>"
                            data-search="<?php echo vs_e(vs_users_search_blob($row)); ?>">
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
                                <?php echo vs_users_action_group($uid, $active); ?>
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
                <article class="vs-user-card<?php echo $active ? '' : ' vs-user-card--banned'; ?>" data-user-row="<?php echo $uid; ?>"
                         data-search="<?php echo vs_e(vs_users_search_blob($row)); ?>">
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
                            <?php echo vs_users_action_button($uid, 'ban', '封禁', 'vs-btn--pill-danger'); ?>
                        <?php else: ?>
                            <?php echo vs_users_action_button($uid, 'unban', '解封', 'vs-btn--pill-primary'); ?>
                        <?php endif; ?>
                        <?php echo vs_users_action_button($uid, 'delete', '删除', 'vs-btn--pill-danger', true); ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php vs_admin_layout_end(array('users.js')); ?>
