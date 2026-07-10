/**
 * 文件：assets/js/account.js
 * 作用：账号设置页头像预览 + AJAX 保存/解绑（避免 POST 重复提交）
 */
(function () {
    'use strict';

    var preview = document.getElementById('avatarPreview');
    var input = document.getElementById('avatarUrlInput');
    var fallback = preview ? (preview.getAttribute('data-fallback') || preview.src) : '';
    var timer = null;

    function updatePreview(url) {
        if (!preview) {
            return;
        }
        var value = (url != null ? url : (input ? input.value : '')).trim();
        preview.src = value === '' ? fallback : value;
    }

    if (preview && input) {
        input.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(function () {
                updatePreview();
            }, 200);
        });

        preview.addEventListener('error', function () {
            if (preview.src !== fallback) {
                preview.src = fallback;
            }
        });
    }

    function bindAjaxForm(form) {
        if (!form || form.getAttribute('data-ajax') !== '1') {
            return;
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var submitBtn = form.querySelector('[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
            }

            window.VS.postForm(form)
                .then(function (data) {
                    if (data.code !== 1) {
                        window.VS.showMessage(data.msg || '操作失败', 'error');
                        return;
                    }

                    window.VS.showMessage(data.msg || '操作成功', 'success');

                    if (form.id === 'accountForm') {
                        if (data.avatar_preview) {
                            updatePreview(data.avatar_preview);
                        }
                        if (input && data.avatar_url != null) {
                            input.value = data.avatar_url;
                        }
                        form.querySelectorAll('input[type="password"]').forEach(function (el) {
                            el.value = '';
                        });
                        return;
                    }

                    var item = form.closest('.vs-oauth-bind-item');
                    if (item && data.provider) {
                        var statusEl = item.querySelector('.vs-oauth-bind-item__status');
                        if (statusEl) {
                            statusEl.textContent = '未绑定';
                        }
                        var actionEl = item.querySelector('.vs-oauth-bind-item__action');
                        if (actionEl && window.VS_BASE_URL) {
                            var provider = data.provider;
                            var label = provider === 'qq' ? 'QQ' : 'Gitee';
                            actionEl.innerHTML = '<a href="' + window.VS_BASE_URL + '/user/oauth/start.php?provider='
                                + encodeURIComponent(provider) + '&amp;intent=bind" class="vs-btn vs-btn--default vs-btn--oauth-action">绑定</a>';
                            item.setAttribute('data-oauth-bound', '0');
                        }
                    }
                })
                .catch(function () {
                    window.VS.showMessage('网络异常，请稍后重试', 'error');
                })
                .finally(function () {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                    }
                });
        });
    }

    document.querySelectorAll('form[data-ajax="1"]').forEach(bindAjaxForm);
})();
