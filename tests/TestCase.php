<?php

declare(strict_types=1);

namespace BumpCore\Panorama\Tests;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected Capsule $database;

    protected function setUp(): void
    {
        parent::setUp();

        $this->database = new Capsule();
        $this->database->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $this->database->setAsGlobal();
        $this->database->bootEloquent();

        $this->createSchema();
        $this->seedData();
    }

    private function createSchema(): void
    {
        $schema = $this->database->schema();

        $schema->create('customers', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
        });

        $schema->create('invoices', static function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('customer_id');
            $table->string('status');
            $table->decimal('amount', 12, 2);
        });
    }

    private function seedData(): void
    {
        Capsule::table('customers')->insert([
            ['id' => 1, 'name' => 'Acme'],
            ['id' => 2, 'name' => 'Globex'],
        ]);

        Capsule::table('invoices')->insert([
            ['customer_id' => 1, 'status' => 'open', 'amount' => 100.00],
            ['customer_id' => 1, 'status' => 'open', 'amount' => 25.50],
            ['customer_id' => 1, 'status' => 'paid', 'amount' => 10.00],
            ['customer_id' => 2, 'status' => 'open', 'amount' => 999.00],
        ]);
    }
}
