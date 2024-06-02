<?php

namespace Andreia\FilamentStripePaymentLink\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Andreia\FilamentStripePaymentLink\FilamentStripePaymentLink
 */
class FilamentStripePaymentLink extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Andreia\FilamentStripePaymentLink\FilamentStripePaymentLink::class;
    }
}
