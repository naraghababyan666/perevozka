<?php

namespace App\Service;
use Illuminate\Support\Facades\Auth;
use YooKassa\Client;

class PaymentService
{
    private function getClient(): Client{
        $client = new Client();
        $client->setAuth(config('services.yookassa.shop_id'), config('services.yookassa.secret_key') );
        return $client;
    }

    /**
     * @throws \YooKassa\Common\Exceptions\NotFoundException
     * @throws \YooKassa\Common\Exceptions\ResponseProcessingException
     * @throws \YooKassa\Common\Exceptions\ApiException
     * @throws \YooKassa\Common\Exceptions\ExtensionNotFoundException
     * @throws \YooKassa\Common\Exceptions\BadApiRequestException
     * @throws \YooKassa\Common\Exceptions\AuthorizeException
     * @throws \YooKassa\Common\Exceptions\InternalServerError
     * @throws \YooKassa\Common\Exceptions\ForbiddenException
     * @throws \YooKassa\Common\Exceptions\TooManyRequestsException
     * @throws \YooKassa\Common\Exceptions\UnauthorizedException
     * @throws \YooKassa\Common\Exceptions\ApiConnectionException
     */
    public function createPayment(float $amount, string $description, array $options = []){

        $client = $this->getClient();
        $idempotenceKey = uniqid('', true);
        $payment = $client->createPayment(
            array(
                'amount' => array(
                    'value' => $amount,
                ),
                'capture' => false,
                'payment_method_data' => array(
                    'type' => 'bank_card',
                ),
                'confirmation' => array(
                    'type' => 'redirect',
                    'return_url' => 'https://transagro.pro/',
                ),
                'description' => $description ?? '',
            ),
            $idempotenceKey
        );

        return $payment->getConfirmation()->getConfirmationUrl();

    }
}
