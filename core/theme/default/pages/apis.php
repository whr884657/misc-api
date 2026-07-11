<?php if (!defined('VS_THEME_RENDER')) { exit; } ?>
<main class="dt-main"><div class="dt-container">
<section class="dt-page-head"><h1 class="dt-page-head__title">全部接口</h1><p class="dt-page-head__desc">浏览平台提供的 API / TAPI 接口列表</p></section>
<section class="dt-section"><?php vs_render_notice('info', '', '接口列表模块正在建设中。用户可注册后自主发布接口。', array('compact' => true)); ?>
<div class="dt-grid-2">
<div class="dt-card"><h3>公开接口</h3><p>即将展示所有可公开调用的 API 接口</p></div>
<div class="dt-card"><h3>我的接口</h3><p>登录后可在用户中心管理已发布的接口</p></div>
</div></section></div></main>
