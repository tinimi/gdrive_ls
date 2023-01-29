<?php

namespace MyApp;

use Google\Service\Drive;

class GdriveLs {
    const MIME_FOLDER = 'application/vnd.google-apps.folder';
    
    protected Drive $service;
    protected string $lsFile;

    public function __construct(Drive $service, string $lsFile)
    {
        $this->service = $service;
        $this->lsFile = $lsFile;
    }

    public function run()
    {
        $dataId = $this->getDataId();

        $f = fopen("../" . $this->lsFile, 'w');
        if (!$f) {
            die('can\'t open file');
        }
        foreach ($this->ls($dataId) as $file) {
            fwrite($f, $file . "\n");
        };
        fclose($f);
    }

    protected function getDataId()
    {
        $response = $this->service->files->listFiles(array(
            'q' => "'root' in parents and name = 'data'",
            'spaces' => 'drive',
            'fields' => 'files(id)',
        ));
        return $response->files[0]->id;

    }

    protected function ls($folderId = 'root')
    {
        $pageToken = null;

        do {
            $response = $this->service->files->listFiles(array(
                'q' => "'{$folderId}' in parents",
                'spaces' => 'drive',
                'pageToken' => $pageToken,
                'fields' => 'nextPageToken, files(id, name, mimeType)',
            ));
            foreach ($response->files as $file) {
                if (self::MIME_FOLDER == $file->mimeType) {
                    yield $file->name;
                    foreach ($this->ls($file->id) as $file2) {
                        yield $file->name . '/' . $file2;
                    };
                } else {
                    yield $file->name;
                }
            }

            $pageToken = $response->nextPageToken;
        } while ($pageToken != null);
    }
}
