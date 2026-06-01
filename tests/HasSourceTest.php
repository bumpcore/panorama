<?php

declare(strict_types=1);

namespace Bumpcore\Panorama\Tests;

use Bumpcore\Panorama\Eloquent\QuerySourceScope;
use Bumpcore\Panorama\Exceptions\InvalidQuerySourceException;
use Bumpcore\Panorama\Exceptions\MissingQuerySourceException;
use Bumpcore\Panorama\Tests\Fixtures\CustomBuilderCustomerBalance;
use Bumpcore\Panorama\Tests\Fixtures\CustomCustomerBalanceBuilder;
use Bumpcore\Panorama\Tests\Fixtures\Customer;
use Bumpcore\Panorama\Tests\Fixtures\CustomerBalance;
use Bumpcore\Panorama\Tests\Fixtures\CustomerBalanceFromQueryBuilder;
use Bumpcore\Panorama\Tests\Fixtures\EmptyAliasModel;
use Bumpcore\Panorama\Tests\Fixtures\InvalidSourceModel;
use Bumpcore\Panorama\Tests\Fixtures\Invoice;
use Bumpcore\Panorama\Tests\Fixtures\MissingSourceModel;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

class HasSourceTest extends TestCase
{
    /**
     * Verifies that named params hydrate an Eloquent source-backed model.
     */
    public function test_it_wraps_an_eloquent_source_and_hydrates_models_with_late_named_params(): void
    {
        $balance = $this->customerBalanceQuery();
        $balance->where('balance', '>', 100);
        $this->withParams($balance, customer_id: 1, status: 'open');
        $result = $balance->firstOrFail();

        self::assertSame(1, $result->getAttribute('customer_id'));
        self::assertSame(125.5, $result->getAttribute('balance'));
        self::assertSame(2, $result->getAttribute('invoice_count'));
        self::assertSame('Acme', $result->customer()->getResults()?->getAttribute('name'));
    }

    /**
     * Verifies that relations on query-backed models can be eager loaded.
     */
    public function test_it_eager_loads_relations_defined_on_query_backed_models(): void
    {
        $query = $this->customerBalanceQuery();
        $query->with('customer');
        $this->withParams($query, customer_id: 1, status: 'open');
        $balance = $query->firstOrFail();

        self::assertTrue($balance->relationLoaded('customer'));
        $customer = $balance->customer()->getResults();
        self::assertNotNull($customer);
        self::assertSame('Acme', $customer->getAttribute('name'));
    }

    /**
     * Verifies that regular models can reference query-backed relation models.
     */
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

    /**
     * Verifies that whereHas works through query-backed relations.
     */
    public function test_query_backed_relations_work_with_where_has(): void
    {
        $customers = Customer::query()
            ->whereHas('balance', static fn ($query) => $query->where('balance', '>', 500))
            ->pluck('name')
            ->all();

        self::assertSame(['Globex'], $customers);
    }

    /**
     * Verifies array params compose with normal Eloquent builder chaining.
     */
    public function test_it_accepts_array_params_and_keeps_normal_builder_chaining(): void
    {
        $query = $this->customerBalanceQuery();
        $query->where('invoice_count', '=', 2);
        $this->withParams($query, [
            'customer_id' => 1,
            'status' => 'open',
        ]);
        $balances = $query->get();

        self::assertCount(1, $balances);
        self::assertSame(125.5, $balances->sole()->getAttribute('balance'));
    }

    /**
     * Verifies later params replace earlier values like normal builder state.
     */
    public function test_later_params_replace_earlier_params_like_builder_state(): void
    {
        $query = $this->customerBalanceQuery();
        $this->withParams($query, customer_id: 2);
        $this->withParams($query, customer_id: 1, status: 'paid');
        $balance = $query->firstOrFail();

        self::assertSame(10.0, $balance->getAttribute('balance'));
        self::assertSame(1, $balance->getAttribute('invoice_count'));
    }

    /**
     * Verifies source methods may return base query builders.
     */
    public function test_it_supports_sources_returning_base_query_builders(): void
    {
        $query = CustomerBalanceFromQueryBuilder::query();

        (new CustomerBalanceFromQueryBuilder())->scopeWithParams($query, customer_id: 2);
        $balance = $query->firstOrFail();

        self::assertSame(2, $balance->getAttribute('customer_id'));
        self::assertEquals(999, $balance->getAttribute('balance'));
    }

    /**
     * Verifies the wrapped source exposes inspectable SQL and bindings.
     */
    public function test_it_exposes_sql_and_bindings_for_the_wrapped_source(): void
    {
        $query = $this->customerBalanceQuery();
        $query->where('balance', '>', 100);
        $this->withParams($query, customer_id: 1, status: 'open');

        self::assertStringContainsString('from (select customer_id, sum(amount) as balance', $query->toBase()->toSql());
        self::assertSame([1, 'open', 100], $query->toBase()->getBindings());
    }

    /**
     * Verifies consumer-defined Eloquent builders remain in use.
     */
    public function test_consumers_can_keep_their_own_eloquent_builder(): void
    {
        $query = CustomBuilderCustomerBalance::query();
        self::assertInstanceOf(CustomCustomerBalanceBuilder::class, $query);

        $query->whereLargeBalance();
        (new CustomBuilderCustomerBalance())->scopeWithParams($query, customer_id: 2);
        $balance = $query->firstOrFail();

        self::assertSame(999.0, $balance->getAttribute('balance'));
    }

    /**
     * Verifies source params must be keyed by argument name.
     */
    public function test_params_must_be_named(): void
    {
        $query = $this->customerBalanceQuery();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must use string keys');

        $this->withParams($query, [1]);
    }

    /**
     * Verifies the scope rejects models that do not use the source trait.
     */
    public function test_query_source_scope_rejects_models_without_the_trait(): void
    {
        $this->expectException(InvalidQuerySourceException::class);
        $this->expectExceptionMessage('is not backed by a Panorama query source');

        (new QuerySourceScope())->apply(Invoice::query(), new Invoice());
    }

    /**
     * Verifies missing source definitions fail with a clear exception.
     */
    public function test_missing_source_definitions_fail_clearly(): void
    {
        $this->expectException(MissingQuerySourceException::class);
        $this->expectExceptionMessage('must override newSource');

        MissingSourceModel::query()->get();
    }

    /**
     * Verifies unsupported source return values fail with a clear exception.
     */
    public function test_invalid_source_return_values_fail_clearly(): void
    {
        $this->expectException(InvalidQuerySourceException::class);
        $this->expectExceptionMessage('newSource() must return');

        InvalidSourceModel::query()->get();
    }

    /**
     * Verifies empty source aliases fail with a clear exception.
     */
    public function test_empty_source_aliases_fail_clearly(): void
    {
        $this->expectException(InvalidQuerySourceException::class);
        $this->expectExceptionMessage('empty query source alias');

        EmptyAliasModel::query()->get();
    }

    /**
     * Creates the typed query used by most source tests.
     *
     * @return Builder<CustomerBalance>
     */
    private function customerBalanceQuery(): Builder
    {
        return CustomerBalance::query();
    }

    /**
     * Applies source params through the fixture trait method.
     *
     * @param Builder<CustomerBalance> $query
     * @param array<array-key, mixed> $params
     * @return Builder<CustomerBalance>
     */
    private function withParams(Builder $query, array $params = [], mixed ...$namedParams): Builder
    {
        return (new CustomerBalance())->scopeWithParams($query, $params, ...$namedParams);
    }
}
