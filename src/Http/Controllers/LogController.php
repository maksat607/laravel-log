<?php

namespace Maksatsaparbekov\LaravelLog\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class LogController extends Controller
{
    private function getLogFiles(): array
    {
        $logPath = storage_path('logs');

        if (!is_dir($logPath)) {
            return [];
        }

        $files = glob($logPath . '/*.log');

        if (!$files) {
            return [];
        }

        $logs = [];
        foreach ($files as $file) {
            $logs[] = [
                'name'     => basename($file),
                'path'     => $file,
                'size'     => $this->formatBytes(filesize($file)),
                'modified' => filemtime($file),
                'modified_formatted' => date('Y-m-d H:i:s', filemtime($file)),
            ];
        }

        usort($logs, fn($a, $b) => $b['modified'] - $a['modified']);

        return $logs;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }

    public function index()
    {
        $logs = $this->getLogFiles();

        return view('laravel-log::index', compact('logs'));
    }

    public function view(Request $request)
    {
        $filename = $request->query('file');

        if (!$filename || str_contains($filename, '..') || str_contains($filename, '/')) {
            abort(400, 'Invalid file name.');
        }

        $path = storage_path('logs/' . $filename);

        if (!file_exists($path) || !str_ends_with($filename, '.log')) {
            abort(404, 'Log file not found.');
        }

        $content = file_get_contents($path);
        $entries = $this->parseLogEntries($content);

        return view('laravel-log::view', [
            'filename' => $filename,
            'entries'  => $entries,
        ]);
    }

    public function delete(Request $request)
    {
        $filename = $request->query('file');

        if (!$filename || str_contains($filename, '..') || str_contains($filename, '/')) {
            abort(400, 'Invalid file name.');
        }

        $path = storage_path('logs/' . $filename);

        if (!file_exists($path) || !str_ends_with($filename, '.log')) {
            abort(404, 'Log file not found.');
        }

        unlink($path);

        return redirect()->route('laravel-log.index')->with('success', "Log file \"{$filename}\" deleted.");
    }

    private function parseLogEntries(string $content): array
    {
        // Split on lines that start with a log timestamp pattern [YYYY-MM-DD HH:MM:SS]
        $pattern = '/(\[\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}[^\]]*\])/';
        $parts = preg_split($pattern, $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        $entries = [];
        $i = 0;
        while ($i < count($parts)) {
            if (preg_match('/^\[\d{4}-\d{2}-\d{2}/', $parts[$i])) {
                $timestamp = trim($parts[$i], '[]');
                $body = isset($parts[$i + 1]) ? trim($parts[$i + 1]) : '';
                $i += 2;

                // Determine level
                $level = 'info';
                if (preg_match('/\.(ERROR|WARNING|CRITICAL|ALERT|EMERGENCY|DEBUG|INFO|NOTICE)/i', $body, $m)) {
                    $level = strtolower($m[1]);
                }

                $entries[] = [
                    'timestamp' => $timestamp,
                    'level'     => $level,
                    'body'      => $body,
                    'date'      => substr($timestamp, 0, 10),
                ];
            } else {
                // Orphaned text before first timestamp
                if ($parts[$i] !== '') {
                    $entries[] = [
                        'timestamp' => null,
                        'level'     => 'info',
                        'body'      => trim($parts[$i]),
                        'date'      => null,
                    ];
                }
                $i++;
            }
        }

        return array_reverse($entries);
    }
}
