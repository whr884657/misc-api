<?php if (!defined('VS_THEME_RENDER')) { exit; } ?>
<main class="dt-main"><div class="dt-container">
<section class="dt-page-head"><h1 class="dt-page-head__title">关于</h1><p class="dt-page-head__desc">了解 <?php echo vs_e($siteName); ?></p></section>
<section class="dt-section"><div class="dt-card">
<h2><?php echo vs_e($siteName); ?></h2>
<p><?php echo vs_e($siteDesc !== '' ? $siteDesc : 'misc-api 是基于 PHP + MySQL 的轻量级 Web 管理系统。'); ?></p>
<ul style="margin:16px 0 0;padding-left:18px;color:var(--vs-text-secondary);font-size:14px;line-height:1.8;">
<li>系统版本：v<?php echo vs_e(VS_VERSION); ?></li>
<li>当前主题：<?php echo vs_e($themeId); ?></li>
</ul></div></section></div></main>
