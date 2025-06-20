<?php

require_once __DIR__ . '/../../../src/io/import/HttpClient.php';

class FakeHttpClient implements HttpClient {

    private array $data = [];

    public function set(string $url, string $data): void {
        $this->data[$url] = $data;
    }
    
    public function fetch(string $url): string {
        if(!isset($this->data[$url])) {
            echo "WARNING: URL '$url' not found in FakeHttpClient data.\n";
        }
        return $this->data[$url] ??'';
    }

}