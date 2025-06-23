<?php
namespace Mdkaif\ProUpdaterGit\Http\Middleware; // Updated vendor namespace

use Closure;
use Illuminate\Http\Request;
use Mdkaif\ProUpdaterGit\GitService; // Updated namespace
use Mdkaif\ProUpdaterGit\Models\UpdateToken; // Updated namespace
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CheckForUpdate
{
    protected $gitService;

    public function __construct(GitService $gitService)
    {
        $this->gitService = $gitService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!config('auto-updater.repository_url') || !config('auto-updater.repository_branch')) {
            Log::info('Pro_Updater_Git not fully configured. Skipping update check.');
            if (!$request->is('pro-updater/setup*') && !Session::has('pro_updater_git_setup_shown')) {
                Session::flash('show_pro_updater_git_setup_modal', true);
                Session::put('pro_updater_git_setup_shown', true);
            }
            return $next($request);
        }

        $lastCheck = Session::get('pro_updater_git_last_check', 0);
        $checkInterval = config('auto-updater.check_interval_minutes') * 60;

        if (time() - $lastCheck >= $checkInterval) {
            Log::info('Performing periodic Pro_Updater_Git check.');
            Session::put('pro_updater_git_last_check', time());

            $repoUrl = config('auto-updater.repository_url');
            $branch = config('auto-updater.repository_branch');
            $token = UpdateToken::first()->token ?? null;

            $currentVersion = $this->gitService->getCurrentVersion();
            $remoteVersion = $this->gitService->getRemoteVersion($repoUrl, $branch, $token);

            if ($remoteVersion && version_compare($remoteVersion, $currentVersion, '>')) {
                Session::flash('show_pro_updater_git_modal', true);
                Session::flash('pro_updater_git_current_version', $currentVersion);
                Session::flash('pro_updater_git_remote_version', $remoteVersion);
                Log::info("New update available! Current: {$currentVersion}, Remote: {$remoteVersion}");
            } else {
                Session::forget('show_pro_updater_git_modal');
                Session::forget('pro_updater_git_current_version');
                Session::forget('pro_updater_git_remote_version');
                Log::info("No new update found or remote version could not be retrieved.");
            }
        } else {
            Log::debug('Skipping Pro_Updater_Git check due to interval.');
        }

        return $next($request);
    }
}