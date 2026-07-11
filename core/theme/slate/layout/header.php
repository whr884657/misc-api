<?php
if (!defined('VS_THEME_RENDER')) {
    exit;
}
?>
<div class="st-root">
<header class="st-topbar">
    <div class="st-shell st-topbar__row">
        <a href="<?php echo vs_e($vsBase); ?>/" class="st-brand">
            <?php vs_render_site_logo('st-brand__logo'); ?>
            <span class="st-brand__name"><?php echo vs_e($siteName); ?></span>
        </a>
        <nav class="st-tabs" aria-label="主导航">
            <?php foreach ($navItems as $item): ?>
                <a href="<?php echo vs_e($item['url']); ?>"
                   class="st-tabs__item<?php echo $activeNav === $item['id'] ? ' is-current' : ''; ?>">
                    <?php echo vs_e($item['label']); ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <a href="<?php echo vs_e($authUrl); ?>" class="st-login st-login--desk"><?php echo vs_e($authLabel); ?></a>
        <button type="button" class="st-panel-btn" id="stPanelBtn" aria-label="打开菜单" aria-expanded="false">☰</button>
    </div>
</header>

<div class="st-panel-mask" id="stPanelMask" hidden></div>
<aside class="st-panel" id="stPanel" aria-label="站点菜单" hidden>
    <div class="st-panel__top">
        <span>导航</span>
        <button type="button" id="stPanelClose" aria-label="关闭">&times;</button>
    </div>
    <nav class="st-panel__list">
        <?php foreach ($navItems as $item): ?>
            <a href="<?php echo vs_e($item['url']); ?>"
               class="st-panel__link<?php echo $activeNav === $item['id'] ? ' is-current' : ''; ?>">
                <?php echo vs_e($item['label']); ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <a href="<?php echo vs_e($authUrl); ?>" class="st-login st-login--panel"><?php echo vs_e($authLabel); ?></a>
</aside>

<nav class="st-dock" aria-label="手机快捷导航">
    <?php
    $dockItems = array_slice($navItems, 0, 5);
    foreach ($dockItems as $item):
    ?>
        <a href="<?php echo vs_e($item['url']); ?>"
           class="st-dock__item<?php echo $activeNav === $item['id'] ? ' is-current' : ''; ?>">
            <span class="st-dock__dot"></span>
            <span class="st-dock__label"><?php echo vs_e($item['label']); ?></span>
        </a>
    <?php endforeach; ?>
</nav>
