<?php
namespace Mdkaif\ProUpdaterGit\Http\Controllers; // Updated vendor namespace

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class UpdateController extends Controller
{
    /**
     * Trigger the update process.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        Log::info('Update requested via web controller.');
        try {
            Artisan::call('pro-updater-git:update');
            $output = Artisan::output();
            Log::info('Pro_Updater_Git command output: ' . $output);

            return response()->json([
                'status' => 'success',
                'message' => 'Update initiated successfully. Please refresh the page to see changes.',
                'output' => $output
            ]);
        } catch (\Exception $e) {
            Log::error('Error during update: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Update failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}