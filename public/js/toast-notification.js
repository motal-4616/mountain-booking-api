/**
 * Toast Notification & Popup Modal System - JavaScript
 * Author: MountainBook
 */

(function () {
    "use strict";

    // =========================================
    // TOAST NOTIFICATION SYSTEM
    // =========================================

    const ToastNotification = {
        container: null,
        defaultDuration: 5000,
        maxToasts: 3, // Tối đa 3 toast cùng lúc

        // Khởi tạo container
        init() {
            if (!this.container) {
                console.log("[Toast.init] Creating toast container");
                this.container = document.createElement("div");
                this.container.className = "toast-container";
                this.container.id = "toastContainer";

                // FORCE inline styles để đảm bảo hiển thị
                this.container.style.cssText =
                    "position: fixed !important; top: 80px !important; right: 20px !important; z-index: 99999 !important; display: flex !important; flex-direction: column; gap: 12px; max-width: 400px; pointer-events: none;";

                document.body.appendChild(this.container);
                console.log(
                    "[Toast.init] Container created and appended to body with inline styles",
                );
                console.log(
                    "[Toast.init] Container position:",
                    window.getComputedStyle(this.container).position,
                );
                console.log(
                    "[Toast.init] Container z-index:",
                    window.getComputedStyle(this.container).zIndex,
                );
            }
            return this;
        },

        // Hiển thị toast
        show(options) {
            this.init();

            const {
                type = "info",
                title = "",
                message = "",
                duration = this.defaultDuration,
                closable = true,
            } = options;

            // Xóa toast cũ nếu vượt quá maxToasts
            const existingToasts = this.container.querySelectorAll(
                ".toast:not(.toast-hiding)",
            );
            if (existingToasts.length >= this.maxToasts) {
                // Xóa toast cũ nhất
                const oldestToast = existingToasts[0];
                this.closeToast(oldestToast);
            }

            const icons = {
                success: "bi-check-circle-fill",
                error: "bi-x-circle-fill",
                warning: "bi-exclamation-triangle-fill",
                info: "bi-info-circle-fill",
            };

            const titles = {
                success: "Thành công",
                error: "Lỗi",
                warning: "Cảnh báo",
                info: "Thông báo",
            };

            // Tạo toast element
            const toast = document.createElement("div");
            toast.className = `toast toast-${type}`;

            // FORCE inline styles
            const baseStyle =
                "background: white !important; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.12); padding: 16px 20px; display: flex !important; align-items: flex-start; gap: 12px; pointer-events: auto; border-left: 4px solid; position: relative; overflow: hidden; min-width: 320px; margin-bottom: 12px;";
            const colorStyles = {
                success: "border-left-color: #10b981;",
                error: "border-left-color: #ef4444;",
                warning: "border-left-color: #f59e0b;",
                info: "border-left-color: #3b82f6;",
            };
            toast.style.cssText = baseStyle + colorStyles[type];

            toast.innerHTML = `
                <div class="toast-icon">
                    <i class="bi ${icons[type]}"></i>
                </div>
                <div class="toast-content">
                    <div class="toast-title">${title || titles[type]}</div>
                    ${message ? `<div class="toast-message">${message}</div>` : ""}
                </div>
                ${closable ? '<button class="toast-close"><i class="bi bi-x"></i></button>' : ""}
                ${duration > 0 ? `<div class="toast-progress" style="animation-duration: ${duration}ms"></div>` : ""}
            `;

            // Thêm vào container
            this.container.appendChild(toast);

            // Xử lý đóng toast
            const closeToastHandler = () => {
                this.closeToast(toast);
            };

            // Click nút close
            const closeBtn = toast.querySelector(".toast-close");
            if (closeBtn) {
                closeBtn.addEventListener("click", closeToastHandler);
            }

            // Auto close sau duration
            if (duration > 0) {
                setTimeout(closeToastHandler, duration);
            }

            return toast;
        },

        // Helper method để đóng toast
        closeToast(toast) {
            if (!toast) return;
            toast.classList.add("toast-hiding");
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        },

        // Xóa tất cả toast
        clearAll() {
            if (this.container) {
                const toasts = this.container.querySelectorAll(".toast");
                toasts.forEach((toast) => this.closeToast(toast));
            }
        },

        // Shorthand methods
        success(message, title = "", duration) {
            console.log(
                "[Toast.success] Called with:",
                message,
                title,
                duration,
            );
            return this.show({ type: "success", title, message, duration });
        },

        error(message, title = "", duration) {
            console.log("[Toast.error] Called with:", message, title, duration);
            return this.show({ type: "error", title, message, duration });
        },

        warning(message, title = "", duration) {
            console.log(
                "[Toast.warning] Called with:",
                message,
                title,
                duration,
            );
            return this.show({ type: "warning", title, message, duration });
        },

        info(message, title = "", duration) {
            console.log("[Toast.info] Called with:", message, title, duration);
            return this.show({ type: "info", title, message, duration });
        },

        // Xóa tất cả toasts
        clearAll() {
            if (this.container) {
                this.container.innerHTML = "";
            }
        },
    };

    // =========================================
    // POPUP MODAL SYSTEM
    // =========================================

    const PopupModal = {
        overlay: null,
        modal: null,

        // Khởi tạo popup container
        init() {
            if (!this.overlay) {
                this.overlay = document.createElement("div");
                this.overlay.className = "popup-overlay";
                this.overlay.id = "popupOverlay";
                this.overlay.innerHTML =
                    '<div class="popup-modal" id="popupModal"></div>';
                document.body.appendChild(this.overlay);
                this.modal = this.overlay.querySelector(".popup-modal");

                // Close on overlay click
                this.overlay.addEventListener("click", (e) => {
                    if (e.target === this.overlay) {
                        this.close();
                    }
                });

                // Close on ESC key
                document.addEventListener("keydown", (e) => {
                    if (
                        e.key === "Escape" &&
                        this.overlay.classList.contains("active")
                    ) {
                        this.close();
                    }
                });
            }
            return this;
        },

        // Hiển thị popup
        show(options) {
            this.init();

            const {
                type = "info",
                title = "",
                subtitle = "",
                message = "",
                icon = null,
                buttons = [{ text: "Đóng", type: "primary", action: "close" }],
                closable = true,
                onClose = null,
                onConfirm = null,
            } = options;

            const icons = {
                success: "bi-check-circle-fill",
                error: "bi-x-circle-fill",
                warning: "bi-exclamation-triangle-fill",
                info: "bi-info-circle-fill",
                confirm: "bi-question-circle-fill",
            };

            // Build buttons HTML
            const buttonsHtml = buttons
                .map((btn) => {
                    const btnClass =
                        btn.type === "danger"
                            ? "popup-btn-danger"
                            : btn.type === "secondary"
                              ? "popup-btn-secondary"
                              : "popup-btn-primary";
                    return `<button class="popup-btn ${btnClass}" data-action="${btn.action || "close"}">${btn.text}</button>`;
                })
                .join("");

            this.modal.className = `popup-modal popup-${type}`;
            this.modal.innerHTML = `
                <div class="popup-header">
                    <div class="popup-icon">
                        <i class="bi ${icon || icons[type]}"></i>
                    </div>
                    <h3 class="popup-title">${title}</h3>
                    ${subtitle ? `<p class="popup-subtitle">${subtitle}</p>` : ""}
                </div>
                <div class="popup-body">
                    <div class="popup-message">${message}</div>
                </div>
                <div class="popup-footer">
                    ${buttonsHtml}
                </div>
            `;

            // Button click handlers
            this.modal.querySelectorAll(".popup-btn").forEach((btn) => {
                btn.addEventListener("click", () => {
                    const action = btn.dataset.action;
                    if (action === "close") {
                        this.close();
                        if (onClose) onClose();
                    } else if (action === "confirm") {
                        this.close();
                        if (onConfirm) onConfirm();
                    } else if (action === "redirect" && btn.dataset.url) {
                        window.location.href = btn.dataset.url;
                    }
                });
            });

            // Show overlay
            document.body.style.overflow = "hidden";
            this.overlay.classList.add("active");

            return this;
        },

        // Đóng popup
        close() {
            if (this.overlay) {
                this.overlay.classList.remove("active");
                document.body.style.overflow = "";
            }
            return this;
        },

        // Shorthand methods
        success(title, message, options = {}) {
            return this.show({ type: "success", title, message, ...options });
        },

        error(title, message, options = {}) {
            return this.show({ type: "error", title, message, ...options });
        },

        warning(title, message, options = {}) {
            return this.show({ type: "warning", title, message, ...options });
        },

        info(title, message, options = {}) {
            return this.show({ type: "info", title, message, ...options });
        },

        // Confirm dialog
        confirm(title, message, onConfirm, onCancel = null) {
            return this.show({
                type: "confirm",
                title,
                message,
                buttons: [
                    { text: "Hủy", type: "secondary", action: "close" },
                    { text: "Xác nhận", type: "primary", action: "confirm" },
                ],
                onConfirm,
                onClose: onCancel,
            });
        },

        // Confirm delete
        confirmDelete(title, message, onConfirm, onCancel = null) {
            return this.show({
                type: "warning",
                title: title || "Xác nhận xóa",
                message:
                    message ||
                    "Bạn có chắc chắn muốn xóa? Hành động này không thể hoàn tác.",
                icon: "bi-trash3-fill",
                buttons: [
                    { text: "Hủy", type: "secondary", action: "close" },
                    { text: "Xóa", type: "danger", action: "confirm" },
                ],
                onConfirm,
                onClose: onCancel,
            });
        },
    };

    // =========================================
    // GLOBAL EXPORTS
    // =========================================

    console.log("[TOAST JS] Exporting to window...");
    window.Toast = ToastNotification;
    window.Popup = PopupModal;
    console.log(
        "[TOAST JS] Exported - Toast:",
        typeof window.Toast,
        "Popup:",
        typeof window.Popup,
    );

    // Log để debug
    console.log("[Toast System] Loaded successfully");

    // Shorthand functions
    window.showToast = function (type, message, title, duration) {
        return ToastNotification.show({ type, message, title, duration });
    };

    window.showPopup = function (options) {
        return PopupModal.show(options);
    };

    // Confirm function - thay thế confirm() mặc định
    window.confirmAction = function (message, callback, options = {}) {
        const {
            title = "Xác nhận",
            confirmText = "Xác nhận",
            cancelText = "Hủy",
            type = "confirm",
            isDanger = false,
        } = options;

        PopupModal.show({
            type: type,
            title: title,
            message: message,
            icon: isDanger
                ? "bi-exclamation-triangle-fill"
                : "bi-question-circle-fill",
            buttons: [
                { text: cancelText, type: "secondary", action: "close" },
                {
                    text: confirmText,
                    type: isDanger ? "danger" : "primary",
                    action: "confirm",
                },
            ],
            onConfirm: callback,
        });
    };

    // Confirm Delete - shortcut cho xóa
    window.confirmDelete = function (message, callback) {
        confirmAction(message, callback, {
            title: "Xác nhận xóa",
            confirmText: "Xóa",
            cancelText: "Hủy",
            type: "warning",
            isDanger: true,
        });
    };

    // Confirm Cancel Booking
    window.confirmCancel = function (message, callback) {
        confirmAction(message, callback, {
            title: "Xác nhận hủy",
            confirmText: "Hủy đơn",
            cancelText: "Quay lại",
            type: "warning",
            isDanger: true,
        });
    };

    // =========================================
    // AUTO-INIT FROM PHP SESSION
    // =========================================

    // =========================================
    // AUTO-INIT FROM PHP SESSION - REMOVED
    // Flash messages are now handled by inline script in blade component
    // to avoid duplication
    // =========================================
})();
