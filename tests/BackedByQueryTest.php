<?php

declare(strict_types=1);

namespace BumpCore\Panorama\Tests;

use BumpCore\Panorama\Eloquent\QueryBackedBuilder;
use BumpCore\Panorama\Exceptions\InvalidQuerySourceException;
use BumpCore\Panorama\Exceptions\MissingQuerySourceException;
use BumpCore\Panorama\Tests\Fixtures\CustomerBalance;
use BumpCore\Panorama\Tests\Fixtures\CustomerBalanceFromQueryBuilder;
use BumpCore\Panorama\Tests\Fixtures\EmptyAliasModel;
use BumpCore\Panorama\Tests\Fixtures\InvalidSourceModel;
use BumpCore\Panorama\Tests\Fixtures\MissingSourceModel;

final class BackedByQueryTest extends TestCase
{
    public function test_it_wraps_an_eloquent_source_and_hydrates_models_with_late_named_params(): void
    {
        $balance = CustomerBalance::query()
            ->where('balance', '>', 100)
            ->withParams(customer_id: 1, status: 'open')
            ->firstOrFail();

        self::assertSame(1, $balance->customer_id);
        self::assertSame(125.5, $balance->balance);
        self::assertSame(2, $balance->invoice_count);
        self::assertSame('Acme', $balance->customer()->firstOrFail()->name);
    }

    public function test_it_accepts_array_params_and_keeps_normal_builder_chaining(): void
    {
        $balances = CustomerBalance::query()
            ->where('invoice_count', '=', 2)
            ->orderByDesc('balance')
            ->withParams([
                'customer_id' => 1,
                'status' => 'open',
            ])
            ->get();

        self::assertCount(1, $balances);
        self::assertSame(125.5, $balances->sole()->balance);
    }

    public function test_later_params_replace_earlier_params_like_builder_state(): void
    {
        $balance = CustomerBalance::query()
            ->withParams(customer_id: 2)
            ->withParams(customer_id: 1, status: 'paid')
            ->firstOrFail();

        self::assertSame(10.0, $balance->balance);
        self::assertSame(1, $balance->invoice_count);
    }

    public function test_it_supports_sources_returning_base_query_builders(): void
    {
        $balance = CustomerBalanceFromQueryBuilder::queryWithParams(customer_id: 2)
            ->firstOrFail();

        self::assertSame(2, $balance->customer_id);
        self::assertSame('999', (string) $balance->balance);
    }

    public function test_it_exposes_sql_and_bindings_for_the_wrapped_source(): void
    {
        $query = CustomerBalance::query()
            ->where('balance', '>', 100)
            ->withParams(customer_id: 1, status: 'open');

        self::assertStringContainsString('from (select customer_id, sum(amount) as balance', $query->toSql());
        self::assertSame([1, 'open', 100], $query->getBindings());
    }

    public function test_models_receive_a_panorama_builder(): void
    {
        self::assertInstanceOf(QueryBackedBuilder::class, CustomerBalance::query());
    }

    public function test_missing_source_definitions_fail_clearly(): void
    {
        $this->expectException(MissingQuerySourceException::class);
        $this->expectExceptionMessage('must override newSource');

        MissingSourceModel::query()->get();
    }

    public function test_invalid_source_return_values_fail_clearly(): void
    {
        $this->expectException(InvalidQuerySourceException::class);
        $this->expectExceptionMessage('newSource() must return');

        InvalidSourceModel::query()->get();
    }

    public function test_empty_source_aliases_fail_clearly(): void
    {
        $this->expectException(InvalidQuerySourceException::class);
        $this->expectExceptionMessage('empty query source alias');

        EmptyAliasModel::query()->get();
    }
}
