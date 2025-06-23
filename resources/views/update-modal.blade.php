<style>
    /* Styling for the Update Modal Overlay - Covers the entire screen */
    .pro-updater-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 99999;
        font-family: 'Inter', sans-serif;
    }

    /* Styling for the Update Modal Content Box */
    .pro-updater-modal-content {
        background-color: #ffffff;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        max-width: 500px;
        width: 90%;
        text-align: center;
        position: relative;
        overflow: hidden;
        animation: fadeInScale 0.3s ease-out;
    }

    /* Keyframe animation for modal entry */
    @keyframes fadeInScale {
        from {
            opacity: 0;
            transform: scale(0.9);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    /* Styling for the Modal Header */
    .pro-updater-modal-header {
        margin-bottom: 20px;
        border-bottom: 1px solid #eeeeee;
        padding-bottom: 15px;
    }

    .pro-updater-modal-header h2 {
        margin: 0;
        color: #333333;
        font-size: 24px;
        font-weight: 700;
    }

    /* Styling for the Modal Body Text */
    .pro-updater-modal-body p {
        color: #555555;
        font-size: 16px;
        line-height: 1.6;
        margin-bottom: 15px;
    }

    /* Styling for the Modal Footer (buttons container) */
    .pro-updater-modal-footer {
        margin-top: 25px;
        display: flex;
        justify-content: center;
        gap: 15px;
    }

    /* Base Button Styling */
    .pro-updater-btn {
        padding: 12px 25px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 600;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    /* Primary Button Styling (Update Now) */
    .pro-updater-btn-primary {
        background-color: #4CAF50;
        color: white;
    }

    .pro-updater-btn-primary:hover {
        background-color: #45a049;
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }

    /* Secondary Button Styling (Later) */
    .pro-updater-btn-secondary {
        background-color: #f44336;
        color: white;
    }

    .pro-updater-btn-secondary:hover {
        background-color: #da190b;
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }

    /* Loading Indicator Styling */
    .pro-updater-loading-indicator {
        display: none;
        margin-top: 20px;
        font-size: 14px;
        color: #007bff;
    }
    .pro-updater-loading-indicator.active {
        display: block;
    }

    /* Success Message Styling */
    .pro-updater-success-message {
        color: #28a745;
        font-weight: 600;
        margin-top: 15px;
        display: none;
    }
    /* Error Message Styling */
    .pro-updater-error-message {
        color: #dc3545;
        font-weight: 600;
        margin-top: 15px;
        display: none;
    }
</style>

{{-- Conditional rendering of the update modal based on session flash data --}}
@if (Session::has('show_pro_updater_git_modal'))
<div class="pro-updater-modal-overlay" id="proUpdaterModal">
    <div class="pro-updater-modal-content">
        <div class="pro-updater-modal-header">
            <h2>🎉 New Update Available!</h2>
        </div>
        <div class="pro-updater-modal-body">
            <p>Your application is currently running version <strong>{{ Session::get('pro_updater_git_current_version', 'N/A') }}</strong>.</p>
            <p>A new version <strong>{{ Session::get('pro_updater_git_remote_version', 'N/A') }}</strong> is ready for installation.</p>
            <p>Click "Update Now" to get the latest features and bug fixes.</p>
        </div>
        <div class="pro-updater-modal-footer">
            <button id="proUpdaterInstallBtn" class="pro-updater-btn pro-updater-btn-primary">Update Now</button>
            <button id="proUpdaterCloseBtn" class="pro-updater-btn pro-updater-btn-secondary">Later</button>
        </div>
        <div id="proUpdaterLoading" class="pro-updater-loading-indicator">
            Updating... Please do not close this window. This may take a moment.
        </div>
        <div id="proUpdaterSuccess" class="pro-updater-success-message">
            Update successful! The page will now refresh.
        </div>
        <div id="proUpdaterError" class="pro-updater-error-message">
            Update failed. Please check logs for details or try again.
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('proUpdaterModal');
        const installBtn = document.getElementById('proUpdaterInstallBtn');
        const closeBtn = document.getElementById('proUpdaterCloseBtn');
        const loadingDiv = document.getElementById('proUpdaterLoading');
        const successDiv = document.getElementById('proUpdaterSuccess');
        const errorDiv = document.getElementById('proUpdaterError');

        if (modal) {
            installBtn.onclick = function() {
                loadingDiv.style.display = 'block';
                installBtn.disabled = true;
                closeBtn.disabled = true;
                successDiv.style.display = 'none';
                errorDiv.style.display = 'none';

                fetch('{{ route("pro-updater.update") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    loadingDiv.style.display = 'none';
                    if (data.status === 'success') {
                        successDiv.style.display = 'block';
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        errorDiv.innerHTML = 'Update failed: ' + data.message;
                        errorDiv.style.display = 'block';
                        installBtn.disabled = false;
                        closeBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    loadingDiv.style.display = 'none';
                    errorDiv.innerHTML = 'An unexpected error occurred during update.';
                    errorDiv.style.display = 'block';
                    installBtn.disabled = false;
                    closeBtn.disabled = false;
                });
            };

            closeBtn.onclick = function() {
                modal.style.display = 'none';
            };
        }
    });
</script>
@endif