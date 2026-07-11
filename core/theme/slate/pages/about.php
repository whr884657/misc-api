<?php if (!defined('VS_THEME_RENDER')) { exit; } ?>
<main class="st-body"><div class="st-shell">
<div class="st-block"><h1 class="st-block__head">关于</h1><p class="st-block__sub"><?php echo vs_e($siteDesc !== '' ? $siteDesc : 'misc-api 轻量级 Web 管理系统'); ?></p></div>
<div class="st-pill-grid">
<div class="st-pill"><h4>系统版本</h4><p>v<?php echo vs_e(VS_VERSION); ?></p></div>
<div class="st-pill"><h4>当前主题</h4><p>深岩（slate）</p></div>
</div></div></main>
