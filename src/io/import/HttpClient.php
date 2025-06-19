<?php

interface HttpClient {
    public function fetch(string $url): string;
}