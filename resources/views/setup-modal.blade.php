<style>
    /* Styling for the Setup Modal Overlay */
    .pro-updater-setup-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 99998;
        font-family: 'Inter', sans-serif;
    }

    /* Styling for the Setup Modal Content Box */
    .pro-updater-setup-modal-content {
        background-color: #ffffff;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        max-width: 600px;
        width: 90%;
        text-align: center;
        position: relative;
        animation: fadeInScale 0.3s ease-out;
    }

    /* Styling for the Modal Header */
    .pro-updater-setup-modal-header {
        margin-bottom: 20px;
        border-bottom: 1px solid #eeeeee;
        padding-bottom: 15px;
    }

    .pro-updater-setup-modal-header h2 {
        margin: 0;
        color: #333333;
        font-size: 24px;
        font-weight: 700;
    }

    /* Styling for the Modal Body (form content) */
    .pro-updater-setup-modal-body {
        text-align: left;
    }

    .pro-updater-setup-modal-body p {
        color: #555555;
        font-size: 15px;
        line-height: 1.6;
        margin-bottom: 15px;
    }

    /* Styling for individual form groups */
    .pro-updater-form-group {
        margin-bottom: 15px;
    }

    .pro-updater-form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333333;
        font-size: 14px;
    }

    .pro-updater-form-group input[type="text"],
    .pro-updater-form-group input[type="password"] {
        width: calc(100% - 24px);
        padding: 12px;
        border: 1px solid #dddddd;
        border-radius: 8px;
        font-size: 15px;
        box-sizing: border-box;
        transition: border-color 0.3s ease;
    }

    .pro-updater-form-group input[type="text"]:focus,
    .pro-updater-form-group input[type="password"]:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
    }

    /* Styling for checkbox group */
    .pro-updater-checkbox-group {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        font-size: 15px;
        color: #555555;
    }

    .pro-updater-checkbox-group input[type="checkbox"] {
        margin-right: 10px;
        width: 18px;
        height: 18px;
    }

    /* Styling for modal footer (submit button) */
    .pro-updater-setup-modal-footer {
        margin-top: 25px;
        display: flex;
        justify-content: center;
    }

    /* Styling for submit button */
    .pro-updater-btn-submit {
        padding: 12px 30px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 17px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 600;
        box-shadow: 0 4px 10px rgba(0, 123, 255, 0.2);
    }

    .pro-updater-btn-submit:hover {
        background-color: #0056b3;
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0, 123, 255, 0.3);
    }

    /* Styling for loading indicator */
    .pro-updater-loading-indicator {
        display: none;
        margin-top: 20px;
        font-size: 14px;
        color: #007bff;
    }
    .pro-updater-loading-indicator.active {
        display: block;
        text-align: center;
    }
    /* Styling for messages (success/error) */
    .pro-updater-message {
        margin-top: 15px;
        padding: 10px;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 500;
        display: none;
    }
    .pro-updater-message.success {
        background-color: #d4edda;
        color: #155724;
        border-color: #c3e6cb;
        display: block;
    }
    .pro-updater-message.error {
        background-color: #f8d7da;
        color: #721c24;
        border-color: #f5c6cb;
        display: block;
    }
</style>

{{-- Conditional rendering of the setup modal --}}
@if (Session::has('show_pro_updater_git_setup_modal') && !config('auto-updater.repository_url'))
<div class="pro-updater-setup-modal-overlay" id="proUpdaterSetupModal">
    <div class="pro-updater-setup-modal-content">
        <div class="pro-updater-setup-modal-header">
            <h2>🚀 Pro_Updater_Git Setup</h2>
        </div>
        <div class="pro-updater-setup-modal-body">
            <p>Welcome to Pro_Updater_Git! Let's get your project ready for seamless updates.</p>
            <p>Please provide the following details:</p>

            <form id="proUpdaterSetupForm">
                <div class="pro-updater-form-group">
                    <label for="repoUrl">Git Repository URL:</label>
                    <input type="text" id="repoUrl" name="repository_url" placeholder="e.g., https://github.com/your-org/your-repo.git" required>
                </div>

                <div class="pro-updater-form-group">
                    <label for="branch">Git Branch:</label>
                    <input type="text" id="branch" name="repository_branch" value="main" required>
                </div>

                <div class="pro-updater-form-group">
                    <label for="repoPath">Local Project Path:</label>
                    <input type="text" id="repoPath" name="repository_path" value="{{ base_path() }}" readonly title="This is your Laravel project's base path.">
                    <small style="color: #888; font-size: 13px;">This path is typically your Laravel project's root and is read-only.</small>
                </div>

                <div class="pro-updater-form-group">
                    <label for="versionFile">Version File (relative path):</label>
                    <input type="text" id="versionFile" name="version_file" value="VERSION.txt" required>
                    <small style="color: #888; font-size: 13px;">A text file in your repository containing only the version number (e.g., "1.0.0").</small>
                </div>

                <div class="pro-updater-form-group">
                    <label for="gitBinPath">Git Executable Path:</label>
                    <input type="text" id="gitBinPath" name="git_bin_path" value="git">
                    <small style="color: #888; font-size: 13px;">Leave empty if 'git' is in your system's PATH. Otherwise, provide full path (e.g., /usr/bin/git).</small>
                </div>

                <div class="pro-updater-checkbox-group">
                    <input type="checkbox" id="isPrivateRepo" name="is_private_repo">
                    <label for="isPrivateRepo">This is a private repository (requires token)</label>
                </div>

                <div class="pro-updater-form-group" id="tokenGroup" style="display: none;">
                    <label for="gitToken">Git Personal Access Token:</label>
                    <input type="password" id="gitToken" name="git_token" placeholder="Your Git Personal Access Token">
                    <small style="color: #888; font-size: 13px;">Generate a token with 'repo' scope from your Git provider (GitHub, GitLab, etc.).</small>
                </div>

                <div class="pro-updater-setup-modal-footer">
                    <button type="submit" class="pro-updater-btn-submit">Complete Setup</button>
                </div>
            </form>
            <div id="proUpdaterSetupLoading" class="pro-updater-loading-indicator">
                Setting up... Please wait. This may take a moment.
            </div>
            <div id="proUpdaterSetupMessage" class="pro-updater-message" style="display: none;"></div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const setupModal = document.getElementById('proUpdaterSetupModal');
        const setupForm = document.getElementById('proUpdaterSetupForm');
        const isPrivateRepoCheckbox = document.getElementById('isPrivateRepo');
        const tokenGroup = document.getElementById('tokenGroup');
        const loadingDiv = document.getElementById('proUpdaterSetupLoading');
        const messageDiv = document.getElementById('proUpdaterSetupMessage');

        if (setupModal) {
            isPrivateRepoCheckbox.addEventListener('change', function() {
                tokenGroup.style.display = this.checked ? 'block' : 'none';
            });

            setupForm.addEventListener('submit', function(e) {
                e.preventDefault();

                loadingDiv.style.display = 'block';
                messageDiv.style.display = 'none';
                messageDiv.className = 'pro-updater-message'; // Reset class

                const formData = new FormData(setupForm);
                const data = {};
                for (let [key, value] of formData.entries()) {
                    data[key] = value;
                }
                data['is_private_repo'] = isPrivateRepoCheckbox.checked;

                // Simulate success for demo purposes.
                // In a real scenario, this would be an AJAX call to a backend endpoint
                // that executes the artisan command logic or handles config/token saving.
                setTimeout(() => {
                    loadingDiv.style.display = 'none';
                    messageDiv.innerHTML = `Setup initiated. For the changes to take full effect, please run the following command in your project's root directory: <br><code style="background-color:#eee;padding:5px;border-radius:4px;display:inline-block;margin-top:10px;">php artisan pro-updater-git:setup</code><br>And remember to add the middleware to `app/Http/Kernel.php`.`;
                    messageDiv.classList.add('success');
                }, 2000);
            });
        }
    });
</script>
@endif