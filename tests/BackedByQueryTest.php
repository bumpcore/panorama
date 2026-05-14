<?php

declare(strict_types=1);

namespace BumpCore\Panorama\Tests;

use BumpCore\Panorama\Eloquent\QueryBackedBuilder;
use BumpCore\Panorama\Exceptions\InvalidQuerySourceException;
use BumpCore\Panorama\Exceptions\MissingQuerySourceException;
use BumpCore\Panorama\Tests\Fixtures\Customer;
use BumpCore\Panorama\Tests\Fixtures\CustomerBalance;
use BumpCore\Panorama\Tests\Fixtures\CustomerBalanceFromQueryBuilder;
use BumpCore\Panorama\Tests\Fixtures\EmptyAliasModel;
use BumpCore\Panorama\Tests\Fixtures\InvalidSourceModel;
use BumpCore\Panorama\Tests\Fixtures\Invoice;
use BumpCore\Panorama\Tests\Fixtures\MissingSourceModel;
use InvalidArgumentException;

final class BackedByQueryTest extends TestCase
{
    public function test_it_wraps_an_eloquent_source_and_hydrates_models_with_late_named_params(): void
    {
        $balance = $this->customerBalanceQuery();
        $balance->where('balance', '>', 100);
        $balance->withParams(customer_id: 1, status: 'open');
        $result = $balance->firstOrFail();

        self::assertSame(1, $result->getAttribute('customer_id'));
        self::assertSame(125.5, $result->getAttribute('balance'));
        self::assertSame(2, $result->getAttribute('invoice_count'));
        self::assertSame('Acme', $result->customer()->getResults()?->getAttribute('name'));
    }

    public function test_it_eager_loads_relations_defined_on_query_backed_models(): void
    {
        $query = $this->customerBalanceQuery();
        $query->with('customer');
        $query->withParams(customer_id: 1, status: 'open');
        $balance = $query->firstOrFail();

        self::assertTrue($balance->relationLoaded('customer'));
        $customer = $balance->customer()->getResults();
        self::assertNotNull($customer);
        self::assertSame('Acme', $customer->getAttribute('name'));
    }

    public function test_normal_models_can_use_query_backed_models_as_relations(): void
    {
        $customer = Customer::query()
            ->with('balance')
            ->whereKey(1)
            ->firstOrFail();

        self::assertTrue($customer->relationLoaded('balance'));
        $balance = $customer->balance()->getResults();
        self::assertNotNull($balance);

        self::assertSame(135.5, $balance->getAttribute('balance'));
        self::assertSame(3, $balance->getAttribute('invoice_count'));
    }

    public function test_query_backed_relations_work_with_where_has(): void
    {
        $customers = Customer::query()
            ->whereHas('balance', static fn ($query) => $query->where('balance', '>', 500))
            ->pluck('name')
            ->all();

        self::assertSame(['Globex'], $customers);
    }

    public function test_it_accepts_array_params_and_keeps_normal_builder_chaining(): void
    {
        $query = $this->customerBalanceQuery();
        $query->where('invoice_count', '=', 2);
        $query->withParams([
            'customer_id' => 1,
            'status' => 'open',
        ]);
        $balances = $query->get();

        self::assertCount(1, $balances);
        self::assertSame(125.5, $balances->sole()->getAttribute('balance'));
    }

    public function test_later_params_replace_earlier_params_like_builder_state(): void
    {
        $query = $this->customerBalanceQuery();
        $query->withParams(customer_id: 2);
        $query->withParams(customer_id: 1, status: 'paid');
        $balance = $query->firstOrFail();

        self::assertSame(10.0, $balance->getAttribute('balance'));
        self::assertSame(1, $balance->getAttribute('invoice_count'));
    }

    public function test_it_supports_sources_returning_base_query_builders(): void
    {
        $query = CustomerBalanceFromQueryBuilder::query();
        self::assertInstanceOf(QueryBackedBuilder::class, $query);

        $query->withParams(customer_id: 2);
        $balance = $query->firstOrFail();

        self::assertSame(2, $balance->getAttribute('customer_id'));
        self::assertEquals(999, $balance->getAttribute('balance'));
    }

    public function test_it_exposes_sql_and_bindings_for_the_wrapped_source(): void
    {
        $query = $this->customerBalanceQuery();
        $query->where('balance', '>', 100);
        $query->withParams(customer_id: 1, status: 'open');

        self::assertStringContainsString('from (select customer_id, sum(amount) as balance', $query->toSql());
        self::assertSame([1, 'open', 100], $query->getBindings());
    }

    public function test_reapplying_an_already_applied_source_is_a_noop(): void
    {
        $query = $this->customerBalanceQuery();
        $query->withParams(customer_id: 1, status: 'open');

        $query->applyQuerySource();
        $sql = $query->toSql();
        $bindings = $query->getBindings();

        $query->applyQuerySource();

        self::assertSame($sql, $query->toSql());
        self::assertSame($bindings, $query->getBindings());
    }

    public function test_models_receive_a_panorama_builder(): void
    {
        self::assertInstanceOf(QueryBackedBuilder::class, CustomerBalance::query());
    }

    public function test_params_must_be_named(): void
    {
        $query = $this->customerBalanceQuery();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must use string keys');

        $query->withParams([1]);
    }

    public function test_query_backed_builders_reject_models_without_the_trait(): void
    {
        $builder = new QueryBackedBuilder(Invoice::query()->getQuery());
        $builder->setModel(new Invoice());

        $this->expectException(InvalidQuerySourceException::class);
        $this->expectExceptionMessage('is not backed by a Panorama query source');

        $builder->get();
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

    /**
     * @return QueryBackedBuilder<CustomerBalance>
     */
    private function customerBalanceQuery(): QueryBackedBuilder
    {
        $query = CustomerBalance::query();

        self::assertInstanceOf(QueryBackedBuilder::class, $query);

        return $query;
    }
}
