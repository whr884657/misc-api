<?php
if (!defined('VS_THEME_RENDER')) {
    exit;
}
$heroDesc = isset($heroDesc) ? $heroDesc : ($siteDesc !== '' ? $siteDesc : '深色极简前台，杂志式内容布局。');
?>
<main class="st-body">
<div class="st-shell">
<section class="st-banner">
    <h1 class="st-banner__title"><?php echo vs_e($siteName); ?></h1>
    <p class="st-banner__lead"><?php echo vs_e($heroDesc); ?></p>
    <a href="<?php echo vs_e($authUrl); ?>" class="st-login"><?php echo vs_e($authLabel); ?></a>
</section>
<section class="st-block">
    <h2 class="st-block__head">探索站点</h2>
    <p class="st-block__sub">点击下方条目快速跳转，或使用底部导航栏切换页面。</p>
    <div class="st-stream" style="margin-top:16px;">
        <?php foreach ($navItems as $item): ?>
            <?php if ($item['id'] === 'home') { continue; } ?>
            <a href="<?php echo vs_e($item['url']); ?>" class="st-row">
                <div>
                    <div class="st-row__title"><?php echo vs_e($item['label']); ?></div>
                    <div class="st-row__meta">进入 <?php echo vs_e($item['label']); ?> 页面</div>
                </div>
                <span class="st-row__arrow">›</span>
            </a>
        <?php endforeach; ?>
    </div>
</section>
</div>
</main>
