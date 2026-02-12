/**
 * Form Submit Handler - Ngăn chặn double submit
 * Tự động disable button và hiển thị loading khi submit form
 */

document.addEventListener("DOMContentLoaded", function () {
    // Xử lý tất cả các form có class 'prevent-double-submit'
    const forms = document.querySelectorAll(
        'form.prevent-double-submit, form[data-prevent-double-submit="true"]',
    );

    forms.forEach(function (form) {
        form.addEventListener("submit", function (e) {
            const submitBtn = form.querySelector('button[type="submit"]');

            if (!submitBtn) return;

            // Nếu button đã disabled thì ngăn submit
            if (submitBtn.disabled) {
                e.preventDefault();
                return false;
            }

            // Disable button
            submitBtn.disabled = true;

            // Lưu nội dung gốc
            const originalHTML = submitBtn.innerHTML;
            submitBtn.setAttribute("data-original-html", originalHTML);

            // Thay đổi nội dung button - chỉ hiển thị spinner
            submitBtn.innerHTML = `
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            `;

            // Thêm class loading
            submitBtn.classList.add("btn-loading");

            // Timeout fallback - Re-enable sau 30s nếu không có response
            setTimeout(function () {
                if (submitBtn.disabled) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHTML;
                    submitBtn.classList.remove("btn-loading");
                }
            }, 30000);
        });

        // Reset nếu có lỗi validation và quay lại trang
        window.addEventListener("pageshow", function (event) {
            if (event.persisted) {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn && submitBtn.disabled) {
                    submitBtn.disabled = false;
                    const originalHTML =
                        submitBtn.getAttribute("data-original-html");
                    if (originalHTML) {
                        submitBtn.innerHTML = originalHTML;
                    }
                    submitBtn.classList.remove("btn-loading");
                }
            }
        });
    });

    // Auto-apply cho các form thêm/sửa/xóa nếu không có class
    const autoApplyForms = document.querySelectorAll(
        'form[action*="store"], form[action*="update"], form[action*="destroy"], ' +
            'form[action*="/admin/"], form[action*="/bookings/"]',
    );

    autoApplyForms.forEach(function (form) {
        if (
            !form.classList.contains("prevent-double-submit") &&
            !form.hasAttribute("data-prevent-double-submit")
        ) {
            form.setAttribute("data-prevent-double-submit", "true");

            form.addEventListener("submit", function (e) {
                const submitBtn = form.querySelector('button[type="submit"]');

                if (!submitBtn) return;

                if (submitBtn.disabled) {
                    e.preventDefault();
                    return false;
                }

                submitBtn.disabled = true;
                const originalHTML = submitBtn.innerHTML;
                submitBtn.setAttribute("data-original-html", originalHTML);

                submitBtn.innerHTML = `
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                `;

                submitBtn.classList.add("btn-loading");
            });
        }
    });
});

/**
 * Helper function: Manually trigger loading state
 * Usage: FormSubmitHandler.startLoading(buttonElement, 'Đang lưu...');
 */
window.FormSubmitHandler = {
    startLoading: function (button) {
        if (!button) return;

        button.disabled = true;
        const originalHTML = button.innerHTML;
        button.setAttribute("data-original-html", originalHTML);

        button.innerHTML = `
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        `;
        button.classList.add("btn-loading");
    },
    stopLoading: function (button) {
        if (!button) return;
        button.disabled = false;
        const originalHTML = button.getAttribute("data-original-html");
        if (originalHTML) {
            button.innerHTML = originalHTML;
        }
        button.classList.remove("btn-loading");
    },
};
