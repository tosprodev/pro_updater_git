<style>
    /* Styling for the Update Button Container */
    .pro-updater-status-container {
        font-family: 'Inter', sans-serif;
        padding: 10px 15px;
        background-color: #f0f8ff; /* Light blue background */
        border: 1px solid #add8e6;
        border-radius: 8px;
        display: inline-flex; /* Use inline-flex to keep elements on one line and align them */
        align-items: center; /* Vertically center items */
        gap: 10px; /* Space between items */
        font-size: 14px;
        color: #333333;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05); /* Subtle shadow */
        margin: 10px; /* Example margin for placement */
    }

    /* Styling for status icons */
    .pro-updater-status-icon {
        font-size: 20px;
    }

    .pro-updater-status-icon.up-to-date {
        color: #28a745; /* Green for up-to-date */
    }

    .pro-updater-status-icon.update-available {
        color: #ffc107; /* Yellow/Amber for update available */
    }

    /* Styling for the Update Button */
    .pro-updater-button {
        padding: 8px 15px;
        background-color: #007bff; /* Blue button */
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        transition: background-color 0.3s ease, transform 0.2s ease; /* Smooth hover effects */
        box-shadow: 0 2px 5px rgba(0, 123, 255, 0.2);
    }

    .pro-updater-button:hover {
        background-color: #0056b3; /* Darker blue on hover */
        transform: translateY(-1px); /* Slight lift effect */
        box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
    }
</style>

@php
    // Get the GitService instance from the Laravel service container.
    $gitService = app(\Mdkaif\ProUpdaterGit\GitService::class); // Updated namespace

    // Get the current local version of the application.
    $currentVersion = $gitService->getCurrentVersion();

    // Retrieve the remote version from the session (set by the middleware if an update is found).
    $remoteVersion = Session::get('pro_updater_git_remote_version'); // Updated session key

    // Determine if an update is available based on session data.
    $updateAvailable = Session::has('show_pro_updater_git_modal'); // Updated session key
@endphp

<div class="pro-updater-status-container">
    @if($updateAvailable)
        {{-- Display if an update is available --}}
        <span class="pro-updater-status-icon update-available">
            {{-- Lucide icon for download --}}
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-down-to-line"><path d="M12 17V3"/><path d="m6 11 6 6 6-6"/><path d="M19 21H5"/></svg>
        </span>
        <span>Update Available!</span>
        <span>(Current: {{ $currentVersion ?? 'N/A' }} | New: {{ $remoteVersion ?? 'N/A' }})</span>
        {{-- Button to open the update modal --}}
        <button class="pro-updater-button" onclick="document.getElementById('proUpdaterModal').style.display='flex'">View Update</button>
    @else
        {{-- Display if the application is up to date --}}
        <span class="pro-updater-status-icon up-to-date">
            {{-- Lucide icon for check circle --}}
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>
        </span>
        <span>Up to Date</span>
        <span>(Version: {{ $currentVersion ?? 'N/A' }})</span>
    @endif
</div>

{{-- Include the update modal Blade view. It will be displayed based on session flash data. --}}
@include('pro-updater-git::update-modal')
{{-- Include the setup modal Blade view. It will be displayed if setup is incomplete. --}}
@include('pro-updater-git::setup-modal')