<?php

declare(strict_types=1);

namespace Bumpcore\Panorama\Tests\Fixtures;

use Illuminate\Database\Eloquent\Builder;

/**
 * @extends Builder<CustomBuilderCustomerBalance>
 */
class CustomCustomerBalanceBuilder extends Builder
{
    /**
     * Adds the large-balance filter used to prove custom builders survive wrapping.
     */
    public function whereLargeBalance(): self
    {
        $this->where('balance', '>', 500);

        return $this;
    }
}
