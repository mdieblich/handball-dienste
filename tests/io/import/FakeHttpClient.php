<?php

require_once __DIR__ . '/../../../src/io/import/HttpClient.php';

class FakeHttpClient implements HttpClient {

    private array $data = [];

    public function set(string $url, string $data): void {
        $this->data[$url] = $data;
    }
    
    public function fetch(string $url): string {
        return $this->data[$url] ??'';
    }

}