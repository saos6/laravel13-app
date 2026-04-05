<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SetupPdfFonts extends Command
{
    protected $signature = 'pdf:setup-fonts';

    protected $description = 'Install Japanese fonts (IPAex Gothic) for PDF output';

    public function handle(): int
    {
        $fontDir = storage_path('fonts');

        if (! is_dir($fontDir)) {
            mkdir($fontDir, 0755, true);
        }

        $dest = $fontDir.DIRECTORY_SEPARATOR.'ipaexg.ttf';

        if (file_exists($dest)) {
            $this->line('  <comment>Skip</comment>  ipaexg.ttf (already exists)');
            $this->info('Japanese fonts are ready.');

            return self::SUCCESS;
        }

        // Download IPAex Gothic ZIP from IPA official website
        $zipUrl = 'https://moji.or.jp/wp-content/ipafont/IPAexfont/ipaexg00401.zip';
        $zipPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'ipaexg.zip';

        $this->info('Downloading IPAex Gothic from IPA official site ...');
        $this->line('  URL: '.$zipUrl);

        $response = Http::timeout(120)->withoutVerifying()->get($zipUrl);

        if (! $response->successful()) {
            $this->error("Failed to download: HTTP {$response->status()}");
            $this->line('');
            $this->line('Manual install:');
            $this->line('  1. Download ipaexg00401.zip from https://moji.or.jp/ipafont/');
            $this->line('  2. Extract ipaexg.ttf from the ZIP');
            $this->line('  3. Copy it to: '.storage_path('fonts/ipaexg.ttf'));

            return self::FAILURE;
        }

        file_put_contents($zipPath, $response->body());
        $this->info('  Download complete. Extracting ...');

        $extracted = false;

        if (class_exists('ZipArchive')) {
            $zip = new \ZipArchive;
            if ($zip->open($zipPath) !== true) {
                $this->error('Failed to open ZIP archive.');

                return self::FAILURE;
            }

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (str_ends_with(strtolower($name), 'ipaexg.ttf')) {
                    file_put_contents($dest, $zip->getFromIndex($i));
                    $extracted = true;
                    $this->info("  Extracted: {$name}");
                    break;
                }
            }
            $zip->close();
        } elseif (PHP_OS_FAMILY === 'Windows') {
            // Fallback: use PowerShell on Windows
            $extractDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'ipaexg_extract';
            $psCmd = sprintf(
                'Expand-Archive -Path %s -DestinationPath %s -Force',
                escapeshellarg($zipPath),
                escapeshellarg($extractDir)
            );
            exec('powershell -Command "'.$psCmd.'"', $out, $code);

            if ($code === 0) {
                $files = glob($extractDir.DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'ipaexg.ttf');
                if (! empty($files)) {
                    copy($files[0], $dest);
                    $extracted = true;
                    $this->info('  Extracted via PowerShell.');
                }
            }
        }

        @unlink($zipPath);

        if (! $extracted) {
            $this->error('Could not extract ipaexg.ttf. ZipArchive extension is not available.');
            $this->line('Please extract ipaexg.ttf manually and copy to: '.$dest);

            return self::FAILURE;
        }

        $this->info('  Saved to: '.$dest);
        $this->info('');
        $this->info('Done. Run the application and generate a PDF to verify Japanese text renders correctly.');

        return self::SUCCESS;
    }
}
