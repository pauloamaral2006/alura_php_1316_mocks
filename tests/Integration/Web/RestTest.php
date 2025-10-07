<?php

namespace Alura\Leilao\Tests\Integration\web;

use PHPUnit\Framework\TestCase;

class RestTest extends TestCase
{

    public function testApiRestDeveRetornarArraysDeLeiloes()
    {

        $response = file_get_contents('http://localhost:8000/rest.php');
        $this->assertStringContainsString('200 OK', $http_response_header[0]);
        $this->assertIsArray(json_decode($response));
    }

}