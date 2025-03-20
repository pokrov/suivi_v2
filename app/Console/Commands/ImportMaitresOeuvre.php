<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MaitreOeuvre;
use League\Csv\Reader;


class ImportMaitresOeuvre extends Command
{
    protected $signature = 'import:maitres_oeuvre';
    protected $description = 'Importe la liste des Maîtres d\'Œuvre depuis un fichier CSV';

    public function handle()
    {
        $csv = Reader::createFromPath(storage_path('app/maitres_oeuvre.csv'), 'r');
        $csv->setHeaderOffset(0);

        foreach ($csv as $row) {
            MaitreOeuvre::firstOrCreate(['nom' => trim($row['nom'])]);
        }

        $this->info('Importation terminée avec succès !');
    }
}
