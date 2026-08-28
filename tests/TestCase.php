<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        // Force testing env vars via putenv() so dotenv's PutenvAdapter
        // cannot override them with .env values.
        // PHPUnit only sets $_ENV/$_SERVER, but dotenv reads getenv() first.
        putenv('APP_ENV=testing');
        putenv('SESSION_DRIVER=array');
        putenv('SESSION_PATH=/');
        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=:memory:');
        putenv('CACHE_STORE=array');
        putenv('QUEUE_CONNECTION=sync');
        putenv('MAIL_MAILER=array');
        putenv('BROADCAST_CONNECTION=null');
        putenv('BCRYPT_ROUNDS=4');
        putenv('APP_MAINTENANCE_DRIVER=file');

        $_ENV['APP_ENV'] = 'testing';
        $_SERVER['APP_ENV'] = 'testing';
        $_ENV['SESSION_DRIVER'] = 'array';
        $_SERVER['SESSION_DRIVER'] = 'array';

        parent::setUp();
    }
}
