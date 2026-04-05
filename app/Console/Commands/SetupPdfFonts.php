<?php

namespace App\Console\Commands;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SetupPdfFonts extends Command
{
    protected $signature = 'pdf:setup-fonts';

    protected $description = 'Install and register Japanese fonts (IPAex Gothic) for PDF output';

    public function handle(): int
    {
        $fontDir = storage_path('fonts');

        if (! is_dir($fontDir)) {
            mkdir($fontDir, 0755, true);
        }

        $ttfPath = $fontDir.DIRECTORY_SEPARATOR.'ipaexg.ttf';

        // ─── Step 1: Ensure the TTF file exists ────────────────────────────

        if (! file_exists($ttfPath)) {
            if (! $this->downloadFont($ttfPath)) {
                return self::FAILURE;
            }
        } else {
            $this->line('  <comment>Skip download</comment>  ipaexg.ttf (already exists)');
        }

        // ─── Step 2: Register the font with DomPDF ─────────────────────────

        $this->info('Registering font with DomPDF ...');

        $options = new Options([
            'fontDir'   => $fontDir,
            'fontCache' => $fontDir,
            'chroot'    => realpath(base_path()),
        ]);

        $dompdf = new Dompdf($options);
        $fontMetrics = $dompdf->getFontMetrics();

        $fileUri = 'file://'.str_replace('\\', '/', $ttfPath);

        // Step 2a: normal を1回登録して .ttf/.ufm キャッシュを生成 & ハッシュを取得
        $ok = $fontMetrics->registerFont(
            ['family' => 'IPAexGothic', 'weight' => 'normal', 'style' => 'normal'],
            $fileUri
        );
        if (! $ok) {
            $this->error('Font registration failed.');

            return self::FAILURE;
        }
        $fontMetrics->saveFontFamilies();

        // JSON から normal の相対ファイル名を取得してハッシュを抽出
        $jsonPath   = $fontDir.DIRECTORY_SEPARATOR.'installed-fonts.json';
        $jsonData   = json_decode(file_get_contents($jsonPath), true);
        $normalName = $jsonData['ipaexgothic']['normal'] ?? null;  // e.g. "ipaexgothic_normal_abc123"
        if (! $normalName || ! str_starts_with($normalName, 'ipaexgothic_')) {
            $this->error('Could not read registered font name from installed-fonts.json');

            return self::FAILURE;
        }
        // "ipaexgothic_normal_<hash>" → "<hash>"
        $hash = substr($normalName, strlen('ipaexgothic_normal_'));
        $this->line("  Normal registered. Hash: {$hash}");

        // Step 2b: bold/italic/bold_italic 用のキャッシュファイルを normal からコピー
        $variantKeys = ['bold', 'italic', 'bold_italic'];
        foreach ($variantKeys as $key) {
            $baseName = "ipaexgothic_{$key}_{$hash}";
            foreach (['.ttf', '.ufm'] as $ext) {
                $src  = $fontDir.DIRECTORY_SEPARATOR."ipaexgothic_normal_{$hash}{$ext}";
                $dest = $fontDir.DIRECTORY_SEPARATOR.$baseName.$ext;
                if (! file_exists($dest)) {
                    copy($src, $dest);
                }
            }
            $this->line("  Copied cache: {$baseName}");
        }

        // Step 2c: installed-fonts.json を全バリアント・相対パスで上書き
        $jsonData['ipaexgothic'] = [
            'normal'      => "ipaexgothic_normal_{$hash}",
            'bold'        => "ipaexgothic_bold_{$hash}",
            'italic'      => "ipaexgothic_italic_{$hash}",
            'bold_italic' => "ipaexgothic_bold_italic_{$hash}",
        ];
        file_put_contents($jsonPath, json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->info('  installed-fonts.json updated with all 4 variants (relative paths).');
        $this->info('');
        $this->info('Done. PDF output will now render Japanese text correctly.');

        return self::SUCCESS;
    }

    private function downloadFont(string $dest): bool
    {
        $zipUrl = 'https://moji.or.jp/wp-content/ipafont/IPAexfont/ipaexg00401.zip';
        $zipPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'ipaexg.zip';

        $this->info('Downloading IPAex Gothic from IPA official site ...');

        $response = Http::timeout(120)->withoutVerifying()->get($zipUrl);

        if (! $response->successful()) {
            $this->error("Failed to download: HTTP {$response->status()}");
            $this->line('Manual install: copy ipaexg.ttf to '.storage_path('fonts/'));

            return false;
        }

        file_put_contents($zipPath, $response->body());
        $this->info('  Downloaded. Extracting ...');

        $extracted = false;

        if (class_exists('ZipArchive')) {
            $zip = new \ZipArchive;
            if ($zip->open($zipPath) === true) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $name = $zip->getNameIndex($i);
                    if (str_ends_with(strtolower($name), 'ipaexg.ttf')) {
                        file_put_contents($dest, $zip->getFromIndex($i));
                        $extracted = true;
                        break;
                    }
                }
                $zip->close();
            }
        } elseif (PHP_OS_FAMILY === 'Windows') {
            $extractDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'ipaexg_extract';
            $ps = sprintf('Expand-Archive -Path %s -DestinationPath %s -Force',
                escapeshellarg($zipPath), escapeshellarg($extractDir));
            exec('powershell -Command "'.$ps.'"', $out, $code);

            if ($code === 0) {
                $files = glob($extractDir.DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'ipaexg.ttf');
                if (! empty($files)) {
                    copy($files[0], $dest);
                    $extracted = true;
                }
            }
        }

        @unlink($zipPath);

        if (! $extracted) {
            $this->error('Could not extract ipaexg.ttf. Please install it manually to: '.$dest);

            return false;
        }

        $this->info('  Saved to: '.$dest);

        return true;
    }
}
