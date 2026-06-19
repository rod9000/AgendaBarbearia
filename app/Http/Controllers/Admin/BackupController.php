<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function index()
    {
        $files = Storage::disk('local')->files('backups');
        $backups = [];

        foreach ($files as $file) {
            $backups[] = [
                'name' => basename($file),
                'path' => $file,
                'date' => date('d/m/Y H:i', Storage::disk('local')->lastModified($file)),
                'size' => $this->formatBytes(Storage::disk('local')->size($file)),
            ];
        }

        $backups = array_reverse($backups);

        return view('admin.settings.backup', compact('backups'));
    }

    public function run()
    {
        Artisan::call('backup:run');
        $output = Artisan::output();

        preg_match('/Backup created: (\S+)/', $output, $matches);
        $path = $matches[1] ?? null;

        if ($path && Storage::disk('local')->exists($path)) {
            $filename = basename($path);
            return response()->download(
                storage_path("app/{$path}"),
                $filename
            );
        }

        return redirect()->back()->with('success', 'Backup gerado com sucesso!');
    }

    public function download($filename)
    {
        $path = 'backups/' . $filename;
        if (!Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return response()->download(storage_path("app/{$path}"), $filename);
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
    }
}
