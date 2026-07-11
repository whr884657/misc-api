/**
 * 深岩主题 · 独立脚本（右侧抽屉 + 底部 Dock）
 */
(function () {
    'use strict';

    var panelBtn = document.getElementById('stPanelBtn');
    var panel = document.getElementById('stPanel');
    var mask = document.getElementById('stPanelMask');
    var closeBtn = document.getElementById('stPanelClose');

    if (!panelBtn || !panel || !mask) {
        return;
    }

    function openPanel() {
        panel.hidden = false;
        mask.hidden = false;
        panel.classList.add('is-open');
        panelBtn.setAttribute('aria-expanded', 'true');
        document.body.classList.add('st-panel-open');
    }

    function closePanel() {
        panel.classList.remove('is-open');
        panelBtn.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('st-panel-open');
        window.setTimeout(function () {
            if (!panel.classList.contains('is-open')) {
                panel.hidden = true;
                mask.hidden = true;
            }
        }, 250);
    }

    panelBtn.addEventListener('click', function () {
        if (panel.classList.contains('is-open')) {
            closePanel();
        } else {
            openPanel();
        }
    });

    mask.addEventListener('click', closePanel);
    if (closeBtn) {
        closeBtn.addEventListener('click', closePanel);
    }

    panel.querySelectorAll('.st-panel__link').forEach(function (link) {
        link.addEventListener('click', closePanel);
    });
})();
