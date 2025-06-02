<?php

namespace App\Service;

use Spatie\Dropbox\Client;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DropboxService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client($_ENV['DROPBOX_ACCESS_TOKEN']);
    }

    public function uploadFile(UploadedFile $file): array
    {
        $fileName = $file->getClientOriginalName();
        $dropboxPath = '/Duolinctionary/' . $fileName;
        $fileContent = file_get_contents($file);

        // Upload file
        $response = $this->client->upload($dropboxPath, $fileContent, 'overwrite');

        return [
            'path' => $response['path_display'],
        ];
    }
}
