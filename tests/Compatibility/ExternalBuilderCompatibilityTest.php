<?php

declare(strict_types=1);

namespace BumpCore\Panorama\Tests\Compatibility;

use BumpCore\Panorama\Tests\Fixtures\CteCustomerBalance;
use BumpCore\Panorama\Tests\Fixtures\EnhancedPostgresCustomerBalance;
use BumpCore\Panorama\Tests\Fixtures\Invoice;
use BumpCore\Panorama\Tests\TestCase;
use Staudenmeir\LaravelCte\Query\Builder as CteBuilder;
use Tpetry\PostgresqlEnhanced\Query\Builder as EnhancedPostgresBuilder;

final class ExternalBuilderCompatibilityTest extends TestCase
{
    public function test_it_preserves_staudenmeir_cte_query_builder_methods(): void
    {
        $query = CteCustomerBalance::query();

        self::assertInstanceOf(CteBuilder::class, $query->getQuery());

        $query->__call('withExpression', ['open_invoices', Invoice::query()->where('status', 'open')]);
        (new CteCustomerBalance())->scopeWithParams($query, customer_id: 1);

        $sql = $query->toBase()->toSql();

        self::assertStringStartsWith('with "open_invoices" as', $sql);
        self::assertStringContainsString('from (select customer_id, sum(amount) as balance', $sql);
        self::assertSame(['open', 1], $query->toBase()->getBindings());
    }

    public function test_it_preserves_tpetry_postgresql_enhanced_query_builder_methods(): void
    {
        $query = EnhancedPostgresCustomerBalance::query();

        self::assertInstanceOf(EnhancedPostgresBuilder::class, $query->getQuery());

        $lateralQuery = Invoice::query();
        $lateralBaseQuery = $lateralQuery->getQuery();
        $lateralBaseQuery->selectRaw('customer_id as lateral_customer_id');
        $lateralBaseQuery->whereColumn('invoices.customer_id', 'enhanced_postgres_customer_balances.customer_id');
        $lateralBaseQuery->limit(1);

        $query->__call('withExpression', ['open_invoices', Invoice::query()->where('status', 'open')]);
        $query->__call('leftJoinSubLateral', [
            $lateralQuery,
            'latest_invoice',
            'latest_invoice.lateral_customer_id',
            '=',
            'enhanced_postgres_customer_balances.customer_id',
        ]);
        (new EnhancedPostgresCustomerBalance())->scopeWithParams($query, customer_id: 2);

        $sql = $query->toBase()->toSql();

        self::assertStringStartsWith('with "open_invoices" as', $sql);
        self::assertStringContainsString('left join lateral', $sql);
        self::assertStringContainsString('from (select customer_id, sum(amount) as balance', $sql);
        self::assertSame(['open', 2], $query->toBase()->getBindings());
    }
}
