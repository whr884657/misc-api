/**
 * 文件：assets/js/users.js
 * 作用：用户管理页操作确认
 */
(function () {
    'use strict';

    document.querySelectorAll('form[data-confirm-delete]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var submitForm = form;

            function doSubmit() {
                submitForm.removeAttribute('data-confirm-delete');
                submitForm.submit();
            }

            if (window.VsModal && window.VsModal.confirm) {
                VsModal.confirm(
                    '删除后该用户的账号与绑定信息将永久移除，且不可恢复。确定删除吗？',
                    '确认删除用户',
                    { confirmText: '删除', danger: true }
                ).then(function (ok) {
                    if (ok) doSubmit();
                });
                return;
            }

            if (confirm('删除后该用户的账号与绑定信息将永久移除，且不可恢复。确定删除吗？')) {
                doSubmit();
            }
        });
    });
})();
