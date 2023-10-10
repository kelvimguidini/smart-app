<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class DeleteOrphanFiles extends Command
{

    protected $signature = 'files:delete-orphan';

    protected $description = 'Delete orphan files from public/storage';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $sourcePath = storage_path('app/public');
        $destinationPath = public_path('storage');

        // Use a função File::allFiles() para obter a lista de arquivos na origem
        $sourceFiles = File::allFiles($destinationPath);

        foreach ($sourceFiles as $file) {
            // Obtenha o caminho relativo do arquivo
            $relativePath = $file->getRelativePathname();

            // Verifique se o arquivo existe na origem antes de copiá-lo
            if (!File::exists($sourcePath . '/' . $relativePath)) {
                // Copie o arquivo para o destino
                File::delete($file->getPathname());

                $this->info('Deleted: ' . $relativePath);
            }
        }

        $this->info('Orphan files deletion complete.');
    }
}
