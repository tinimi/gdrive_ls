<?php

namespace MyApp;

class Compare {
    public function compare($file1, $file2)
    {
        $uploaded = [];
        $f2 = fopen($file2, 'r');
        while ($fileName = fgets($f2)) {
            $uploaded[] = $fileName;
        }
        fclose($f2);

        $f1 = fopen($file1, 'r');
        while ($fileName = fgets($f1)) {
            $fileName = trim($fileName, '.');
            $fileName = trim($fileName, '/');
            if (!in_array($fileName, $uploaded)) {
                yield $fileName;
            }
        }
        fclose($f1);
    }
}