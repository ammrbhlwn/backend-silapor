<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class SupabaseStorageService
{
    protected $url;
    protected $key;
    protected $bucket;

    public function __construct()
    {
        $this->url = config('supabase.url');
        $this->key = config('supabase.key');
        $this->bucket = config('supabase.bucket');
    }

    public function uploadImage(UploadedFile $file, string $path): string
    {
        $filename = $path . '/' . uniqid() . '.' . $file->getClientOriginalExtension();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->key,
        ])
            ->attach('file', file_get_contents($file), $filename)
            ->post("{$this->url}/storage/v1/object/{$this->bucket}/{$filename}");

        if ($response->successful()) {
            return "{$this->url}/storage/v1/object/public/{$this->bucket}/{$filename}";
        } else {
            throw new \Exception('Failed to upload image to Supabase: ' . $response->body());
        }
    }

    public function getSignedUrl(string $filepath)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->key,
        ])->post("{$this->url}/storage/v1/object/sign/{$this->bucket}/{$filepath}", [
            'expiresIn' => 3600
        ]);

        if ($response->successful()) {
            return $this->url . "/storage/v1" . $response->json()['signedURL'];
        } else {
            throw new \Exception('Failed to retrieve signed URL from Supabase: ' . $response->body());
        }
    }
}
