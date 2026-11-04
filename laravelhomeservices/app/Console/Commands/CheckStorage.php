<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Media;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\Features\SupportFileUploads\GenerateSignedUploadUrl;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use RuntimeException;
use Throwable;

/**
 * Proves that images and receipts can really be stored, end to end, using the same classes
 * as a real upload. It runs with the daily cron, so a broken bucket or expired key shows up
 * in the logs before a provider runs into it. Only touches its own files, and removes them.
 */
final class CheckStorage extends Command
{
    protected $signature = 'storage:check';

    protected $description = 'Write, read and delete a test file on the media and receipts disks, and test direct uploads';

    private bool $failed = false;

    public function handle(): int
    {
        $this->step('fotos', fn () => $this->checkMedia());
        $this->step('comprobantes', fn () => $this->roundTrip($this->disk('receipts'), 'salud/' . Str::random(16) . '.txt'));

        if (FileUploadConfiguration::isUsingS3()) {
            $this->step('subida directa', fn () => $this->checkDirectUpload());
        }

        return $this->failed ? self::FAILURE : self::SUCCESS;
    }

    private function step(string $name, callable $check): void
    {
        try {
            $check();
            Log::info("storage:check {$name}: ok");
            $this->info("{$name}: ok");
        } catch (Throwable $exception) {
            $this->failed = true;
            Log::error("storage:check {$name}: " . $exception->getMessage());
            $this->error("{$name}: " . $exception->getMessage());
        }
    }

    private function checkMedia(): void
    {
        $file = Str::random(16) . '.txt';
        $disk = $this->disk(config('filesystems.default'));

        $disk->put('salud/' . $file, 'ok');

        try {
            if (config('filesystems.disks.' . config('filesystems.default') . '.driver') === 's3') {
                $this->assert(Http::get(Media::url('salud', $file))->body() === 'ok', 'el archivo no se puede leer desde su dirección pública');
            }
        } finally {
            $disk->delete('salud/' . $file);
        }

        $this->assert(! $disk->exists('salud/' . $file), 'el archivo no se pudo borrar');
    }

    private function roundTrip(Filesystem $disk, string $path): void
    {
        $disk->put($path, 'ok');
        $this->assert($disk->get($path) === 'ok', 'el archivo guardado no coincide');
        $disk->delete($path);
        $this->assert(! $disk->exists($path), 'el archivo no se pudo borrar');
    }

    /**
     * The route of a real upload: signed URL, PUT to the bucket as the browser does, then copy to the media disk.
     */
    private function checkDirectUpload(): void
    {
        $signed = app(GenerateSignedUploadUrl::class)->forS3(UploadedFile::fake()->create('salud.png', 1, 'image/png'));
        $hashName = basename(rawurldecode((string) parse_url($signed['url'], PHP_URL_PATH)));
        $headers = collect($signed['headers'])->except(['Host', 'host'])->map(fn ($value) => is_array($value) ? implode(',', $value) : $value)->all();

        $response = Http::withHeaders($headers)->withBody('ok', $headers['Content-Type'] ?? 'image/png')->put($signed['url']);
        $this->assert($response->successful(), 'el bucket rechazó la subida firmada (' . $response->status() . '): ' . Str::limit($response->body(), 200));

        $temporary = TemporaryUploadedFile::createFromLivewire($hashName);
        $name = Str::random(16) . '.png';

        try {
            $this->assert($temporary->exists(), 'la subida no quedó en el disco temporal');
            $this->assert(Http::get($temporary->temporaryUrl())->successful(), 'la vista previa de la subida no se puede abrir');
            Media::store($temporary, 'salud', $name);
            $this->assert($this->disk(config('filesystems.default'))->exists('salud/' . $name), 'la subida no se copió al disco de fotos');
        } finally {
            $this->disk(config('filesystems.default'))->delete('salud/' . $name);
            $temporary->delete();
        }
    }

    /**
     * Same disk as the app, but raising errors: the app's disks fail silently and here we need the reason.
     */
    private function disk(string $name): Filesystem
    {
        return Storage::build(array_merge(config('filesystems.disks.' . $name), ['throw' => true]));
    }

    private function assert(bool $condition, string $message): void
    {
        if (! $condition) {
            throw new RuntimeException($message);
        }
    }
}
