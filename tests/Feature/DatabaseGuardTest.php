<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseGuardTest extends TestCase
{
     public function test_uses_test_database(): void
     {
          $this->assertSame('db_pritani_test', DB::connection()->getDatabaseName());
     }
}