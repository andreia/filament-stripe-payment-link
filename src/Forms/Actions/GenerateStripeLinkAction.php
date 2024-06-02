<?php

namespace Andreia\FilamentStripePaymentLink\Forms\Actions;

use Exception;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Notifications\Notification;
use Stripe\StripeClient;

class GenerateStripeLinkAction extends Action
{
    protected $stripeClient = null;

    public static function getDefaultName(): ?string
    {
        return 'generateStripePaymentLink';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-stripe-payment-link::filament-stripe-payment-link.label'));

        $this->tooltip(__('filament-stripe-payment-link::filament-stripe-payment-link.tooltip'));

        $this->modalHeading(__('filament-stripe-payment-link::filament-stripe-payment-link.modal.heading'));

        $this->requiresConfirmation(config('filament-stripe-payment-link.action.requires-confirmation'));

        $this->icon(config('filament-stripe-payment-link.action.icon'));

        $this->form([
            Forms\Components\TextInput::make('product_name')
                ->required(),
            Forms\Components\TextInput::make('amount')
                ->required(),
            Forms\Components\TextInput::make('currency')
                ->required()
                ->helperText(__('filament-stripe-payment-link::filament-stripe-payment-link.field.currency.helper-text')),
        ]);

        $this->action(function (array $data, Component $component): void {
            $paymentLink = $this->createStripePaymentLink($data);

            if ($paymentLink === null) {
                Notification::make()
                    ->title(__('filament-stripe-payment-link::filament-stripe-payment-link.notification.error-message'))
                    ->danger()
                    ->send();
            } else {
                Notification::make()
                    ->title(__('filament-stripe-payment-link::filament-stripe-payment-link.notification.success-message'))
                    ->success()
                    ->send();
            }

            $component->state($paymentLink);
        });
    }

    protected function createStripePaymentLink(array $data):  ?string
    {
        try {
            $stripePaymentLinkKeyLocation = config('filament-stripe-payment-link.payment-link-key-location');

            $this->stripeClient = new StripeClient(config($stripePaymentLinkKeyLocation));

            $priceId = $this->createStripePrice($data);

            $paymentLink = $this->stripeClient->paymentLinks->create([
                'line_items' => [
                    [
                        'price' => $priceId,
                        'quantity' => 1,
                    ],
                ],
            ]);

            return $paymentLink->url ?? null;
        } catch (Exception $e) {
            app('log')->error('Exception when creating payment link: '.$e->getMessage());

            return null;
        }
    }

    protected function createStripeProduct(array $data):  ?string
    {
        $product = $this->stripeClient->products->create([
            'name' => $data['product_name'],
        ]);

        return $product->id ?? null;
    }

    protected function createStripePrice(array $data): ?string
    {
        $productId = $this->createStripeProduct($data);

        if ($productId === null) {
            return null;
        }

        // convert the float into an integer for Stripe API
        $amount = $data['amount'] * 100;

        $price = $this->stripeClient->prices->create([
            'currency' => $data['currency'],
            'unit_amount' => $amount,
            'product' => $productId,
        ]);

        return $price->id ?? null;
    }
}
