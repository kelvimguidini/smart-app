<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class CopyFiles extends Command
{
    protected $signature = 'files:copy';

    protected $description = 'Copy files from storage/app/public to public/storage';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $sourcePath = storage_path('app/public');
        $destinationPath = public_path('storage');

        // Use a função File::allFiles() para obter a lista de arquivos na origem
        $sourceFiles = File::allFiles($sourcePath);

        foreach ($sourceFiles as $file) {
            // Obtenha o caminho relativo do arquivo
            $relativePath = $file->getRelativePathname();


            if (!File::exists($destinationPath . '/' . $relativePath)) {
                // Copie o arquivo para o destino
                File::copy($sourcePath . '/' . $relativePath, $destinationPath . '/' . $relativePath);
                $this->info('Copied: ' . $relativePath);
            }
        }

        $this->info('Synchronization complete.');
    }
}
