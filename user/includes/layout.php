<?php
/**
 * 文件：user/includes/layout.php
 * 作用：用户中心简单布局
 */

/**
 * @param string $pageTitle
 * @return void
 */
function vs_user_layout_start($pageTitle)
{
    global $vsBase, $vsUser, $vsSiteName;

    $base = $vsBase;
    $siteName = $vsSiteName;
    $user = $vsUser;
    $favicon = SiteContext::siteFavicon();

    echo '<!DOCTYPE html>' . "\n";
    echo '<html lang="zh-CN">' . "\n";
    echo '<head>' . "\n";
    echo '<meta charset="UTF-8">' . "\n";
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n";
    echo '<title>' . vs_e(vs_page_title($pageTitle, $siteName)) . '</title>' . "\n";
    if ($favicon !== '') {
        echo '<link rel="icon" href="' . vs_e(vs_favicon_href($favicon)) . '">' . "\n";
    }
    vs_theme_bg_preload_script();
    echo '<link rel="stylesheet" href="' . vs_e($base) . '/assets/css/common.css?v=' . VS_VERSION . '">' . "\n";
    echo '<link rel="stylesheet" href="' . vs_e($base) . '/assets/css/toast.css?v=' . VS_VERSION . '">' . "\n";
    echo '<link rel="stylesheet" href="' . vs_e($base) . '/assets/css/icons.css?v=' . VS_VERSION . '">' . "\n";
    echo '<link rel="stylesheet" href="' . vs_e($base) . '/assets/css/theme-picker.css?v=' . VS_VERSION . '">' . "\n";
    echo '<link rel="stylesheet" href="' . vs_e($base) . '/assets/css/admin.css?v=' . VS_VERSION . '">' . "\n";
    echo '</head>' . "\n";
    echo '<body class="vs-body vs-admin-body">' . "\n";
    echo '<div class="vs-admin-shell vs-user-shell">' . "\n";
    echo '<header class="vs-topbar vs-user-topbar">' . "\n";
    echo '<div class="vs-topbar__left">' . "\n";
    vs_render_site_logo('vs-topbar__logo');
    echo '<span class="vs-topbar__title">' . vs_e($siteName) . ' · 用户中心</span>' . "\n";
    echo '</div>' . "\n";
    echo '<div class="vs-topbar__right">' . "\n";
    echo '<span class="vs-topbar__user">' . vs_e($user ? $user['username'] : '') . '</span>' . "\n";
    echo '<a class="vs-topbar__link" href="' . vs_e($base) . '/">首页</a>' . "\n";
    echo '<a class="vs-topbar__link" href="' . vs_e($base) . '/user/login.php?action=logout">退出</a>' . "\n";
    echo '</div></header>' . "\n";
    echo '<main class="vs-admin-main vs-user-main">' . "\n";
    echo '<div class="vs-admin-content">' . "\n";
}

/**
 * @param array $extraScripts
 * @return void
 */
function vs_user_layout_end(array $extraScripts = array())
{
    global $vsBase;

    echo '</div></main></div>' . "\n";
    echo '<script src="' . vs_e($vsBase) . '/assets/js/common.js?v=' . VS_VERSION . '"></script>' . "\n";
    echo '<script src="' . vs_e($vsBase) . '/assets/js/theme-picker.js?v=' . VS_VERSION . '"></script>' . "\n";
    foreach ($extraScripts as $js) {
        echo '<script src="' . vs_e($vsBase) . '/assets/js/' . vs_e($js) . '?v=' . VS_VERSION . '"></script>' . "\n";
    }
    echo '</body></html>';
}
