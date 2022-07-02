<?php

use Tests\FakeServer;
use Tests\FakeClient;

uses(Tests\TestCase::class)->in('Feature');

uses()
    ->beforeEach(function () {
        test()->server = new FakeServer();
        test()->client = new FakeClient();
    })
    ->afterEach(function () {
        test()->server->close();
    })
    ->in('Feature');

function fixture($name)
{
    return hex2binary(file_get_contents(__DIR__.'/Feature/fixtures/'.$name.'.txt'));
}
