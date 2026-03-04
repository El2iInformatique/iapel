<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\SecretStore;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('secrets:set {key} {value} {--updated-by=cli}', function () {
    app(SecretStore::class)->set(
        $this->argument('key'),
        $this->argument('value'),
        $this->option('updated-by')
    );

    $this->info('Secret mis à jour.');
})->purpose('Met à jour un secret en base (chiffré)');

Artisan::command('secrets:forget {key}', function () {
    app(SecretStore::class)->forget($this->argument('key'));
    $this->info('Secret supprimé de la base (fallback config/secrets.php actif).');
})->purpose('Supprime un secret runtime');
