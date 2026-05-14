<?php

declare(strict_types=1);

namespace BumpCore\Panorama\Tests\Fixtures;

use Illuminate\Database\Eloquent\Builder;

/**
 * @extends Builder<CustomBuilderCustomerBalance>
 */
final class CustomCustomerBalanceBuilder extends Builder
{
    public function whereLargeBalance(): self
    {
        $this->where('balance', '>', 500);

        return $this;
    }
}
