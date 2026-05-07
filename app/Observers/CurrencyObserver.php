<?php

namespace App\Observers;

use App\Models\Currency;

class CurrencyObserver
{
    /**
     * Handle the Currency "saved" event.
     */
    public function saved(Currency $currency): void
    {
        Currency::clearDefaultIdCache();
    }

    /**
     * Handle the Currency "deleted" event.
     */
    public function deleted(Currency $currency): void
    {
        Currency::clearDefaultIdCache();
    }
}
