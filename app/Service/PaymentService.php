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
        $response = $client->createReceipt(
            array(
                'customer' => array(
                    'email' => Auth::user()['email'],
                    'phone' => '79000000000',
                ),
                'type' => 'payment',
                'payment_id' => '24e89cb0-000f-5000-9000-1de77fa0d6df',
                'on_behalf_of' => '123',
                'send' => true,
                'items' => array(
                    array(
                        'description' => 'Платок Gucci',
                        'quantity' => '1.00',
                        'amount' => array(
                            'value' => '30.00',
                            'currency' => 'RUB',
                        ),
                        'vat_code' => 2,
                        'payment_mode' => 'full_prepayment',
                        'payment_subject' => 'commodity',
                    ),
                ),
                'tax_system_code' => 1,
                'settlements' => array(
                    array(
                        'type' => 'cashless',
                        'amount' => array(
                            'value' => '30.00',
                            'currency' => 'RUB',
                        )
                    ),
                ),
            ),
            uniqid('', true)
        );
        dd($response->getId());


        $payment = $client->createPayment([
            'amount' => [
                'value' => $amount,
                'currency' => 'RUB',
            ],
            'capture' => false,
            'confirmation' => [
                'type' => 'redirect',
                'return_url' => route('payment.callback'),
            ],
            'receipt' => [
                'customer' => [
                    'full_name' => 'ыфв фывыф вфыв',
                    'inn' => '7743013902',
                    'email' => 'nstranger10@gmail.com',
                    'phone' => '79000000000'
                ],
                'items' => [
                    'description' => 'test 1',
                    'amount' => [
                        'value' => $amount,
                        'currency' => 'RUB',
                    ]
                ],
                'phone' => Auth::user()['phone_number'],
                'email' => Auth::user()['email'],
                'tax_system_code' => 1,
                'receipt_industry_details' => [

                ]
            ],
            'metadata' => [
                'transaction_id' => $options['transaction_id'],
            ],
            'description' => $description,


        ], uniqid('', true));
        return $payment->getConfirmation()->getConfirmationUrl();

    }
}
