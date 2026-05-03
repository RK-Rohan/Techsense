<?php

namespace App\Http\Controllers;

use App\Utils\Util;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Log;
use Storage;

class BackUpController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $commonUtil;

    public function __construct(Util $commonUtil)
    {
        $this->commonUtil = $commonUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! auth()->user()->can('backup')) {
            abort(403, 'Unauthorized action.');
        }

        $disk = Storage::disk(config('backup.backup.destination.disks')[0]);

        $files = $disk->files(config('backup.backup.name'));

        $backups = [];
        // make an array of backup files, with their filesize and creation date
        foreach ($files as $k => $f) {
            // only take the zip files into account
            if (substr($f, -4) == '.zip' && $disk->exists($f)) {
                $backups[] = [
                    'file_path' => $f,
                    'file_name' => str_replace(str_replace('\\', '/', config('backup.backup.name')).'/', '', $f),
                    'file_size' => $disk->size($f),
                    'last_modified' => $disk->lastModified($f),
                ];
            }
        }
        // reverse the backups, so the newest one would be on top
        $backups = array_reverse($backups);

        $cron_job_command = $this->commonUtil->getCronJobCommand();
        $backup_clean_cron_job_command = $this->commonUtil->getBackupCleanCronJobCommand();

        return view('backup.index')
            ->with(compact('backups', 'cron_job_command', 'backup_clean_cron_job_command'));
    }

    /**
     * Create a resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! auth()->user()->can('backup')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            //Disable in demo
            $notAllowed = $this->commonUtil->notAllowedInDemo();
            if (! empty($notAllowed)) {
                return $notAllowed;
            }

            // Start backup in detached mode to avoid HTTP timeout on large backups.
            $phpBinary = escapeshellarg(PHP_BINARY);
            $artisan = escapeshellarg(base_path('artisan'));

            $logDirectory = storage_path('logs');
            if (! File::exists($logDirectory)) {
                File::makeDirectory($logDirectory, 0755, true);
            }

            $resolvedLogFile = File::isDirectory($logDirectory)
                ? storage_path('logs/backup-run.log')
                : '/tmp/backup-run.log';

            $logFile = escapeshellarg($resolvedLogFile);
            $command = "{$phpBinary} {$artisan} backup:run >> {$logFile} 2>&1 &";
            exec($command);

            Log::info('Backpack\\BackupManager -- backup dispatched from admin interface.');

            $output = ['success' => 1,
                'msg' => __('lang_v1.success').'. Backup started in background.',
            ];
        } catch (\Throwable $e) {
            Log::error('Backpack\\BackupManager -- failed to dispatch backup: '.$e->getMessage());
            $output = ['success' => 0,
                'msg' => $e->getMessage(),
            ];
        }

        return back()->with('status', $output);
    }

    /**
     * Downloads a backup zip file.
     *
     * TODO: make it work no matter the flysystem driver (S3 Bucket, etc).
     */
    public function download($file_name)
    {
        if (! auth()->user()->can('backup')) {
            abort(403, 'Unauthorized action.');
        }

        //Disable in demo
        if (config('app.env') == 'demo') {
            $output = ['success' => 0,
                'msg' => 'Feature disabled in demo!!',
            ];

            return back()->with('status', $output);
        }

        $file = config('backup.backup.name').'/'.$file_name;
        $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
        if ($disk->exists($file)) {
            $fs = Storage::disk(config('backup.backup.destination.disks')[0])->getDriver();
            $stream = $fs->readStream($file);
            //var_dump($fs->size($file));exit;

            return \Response::stream(function () use ($stream) {
                fpassthru($stream);
            }, 200, [
                'Content-Type' => $fs->mimeType($file),
                //'Content-Length' => $fs->getSize($file),
                'Content-disposition' => 'attachment; filename="'.basename($file).'"',
            ]);
        } else {
            abort(404, "The backup file doesn't exist.");
        }
    }

    /**
     * Deletes a backup file.
     */
    public function delete($file_name)
    {
        
        if (! auth()->user()->can('backup')) {
            abort(403, 'Unauthorized action.');
        }

        //Disable in demo
        if (config('app.env') == 'demo') {
            $output = ['success' => 0,
                'msg' => 'Feature disabled in demo!!',
            ];

            return back()->with('status', $output);
        }

        $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
        if ($disk->exists(config('backup.backup.name').'/'.$file_name)) {
            $disk->delete(config('backup.backup.name').'/'.$file_name);

            return redirect()->back();
        } else {
            abort(404, "The backup file doesn't exist.");
        }
    }
}
