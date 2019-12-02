<?php

declare(strict_types=1);

namespace App\Presenters;


use SlevomatCsobGateway\Api\ApiClient;
use SlevomatCsobGateway\Api\Driver\CurlDriver;
use SlevomatCsobGateway\Api\HttpMethod;
use SlevomatCsobGateway\Call\PayMethod;
use SlevomatCsobGateway\Call\PayOperation;
use SlevomatCsobGateway\Cart;
use SlevomatCsobGateway\Crypto\CryptoService;
use SlevomatCsobGateway\Currency;
use SlevomatCsobGateway\Language;
use SlevomatCsobGateway\RequestFactory;

final class HomepagePresenter extends BasePresenter
{
    private $privateKeyFile;
    private $bankPublicKeyFile;

    public function __construct(
        $privateKeyFile,
        $bankPublicKeyFile
    )
    {
        $this->privateKeyFile = $privateKeyFile;
        $this->bankPublicKeyFile = $bankPublicKeyFile;
    }

    public function renderDefault(): void
    {
        $apiClient = new ApiClient(
            new CurlDriver(),
            new CryptoService(
                $this->privateKeyFile,
                $this->bankPublicKeyFile
            ),
            'https://api.platebnibrana.csob.cz/api/v1.8'
        );

        $requestFactory = new RequestFactory('A4292qDdPE');

        $cart = new Cart(Currency::get(Currency::EUR));
        $cart->addItem('Nákup', 1, (int)round(1.9 * 100));
        try {
            $paymentResponse = $requestFactory->createInitPayment(
                '1',
                PayOperation::get(PayOperation::PAYMENT),
                PayMethod::get(PayMethod::CARD),
                true,
                'redirct',
                HttpMethod::get(HttpMethod::POST),
                $cart,
                'Description',
                null,
                null,
                Language::get(Language::CZ)
            )->send($apiClient);
            $payId = $paymentResponse->getPayId();

            $processPaymentResponse = $requestFactory->createProcessPayment($payId)->send($apiClient);

            header('Location: ' . $processPaymentResponse->getGatewayLocationUrl());
        } catch (\Exception $exception) {
            dump($exception);
        }
    }
}
