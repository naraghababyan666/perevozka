<?php

namespace App\Service;
use App\Models\Transactions;
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
    public function createPayment(float $amount, string $description){

        $client = $this->getClient();
        $idempotenceKey = uniqid('', true);
        $payment = $client->createPayment(
            array(
                'description' => $description ?? '',
                'payment_method_data' => array(
                    'type' => 'bank_card',
                ),
                'confirmation' => array(
                    'type' => 'redirect',
                    'return_url' => 'https://transagro.pro/after-payment-redirect',
                    'enforce' => true
                ),
                'capture' => true,
                'amount' => array(
                    'value' => $amount,
                    'currency' => 'RUB'
                ),
            ),
            $idempotenceKey
        );
        if($payment->getStatus() != 'canceled'){
            Transactions::query()->create([
                'order_id' => $payment->getId(),
                'company_id' => Auth::id(),
                'amount' => $amount
            ]);
        }
        return $payment->getConfirmation()->getConfirmationUrl();

    }

    public function checkPayment($paymentId){
        $client = $this->getClient();
        try {
//            $payment = $client->getPaymentInfo($paymentId);
//            $amount = $payment->getAmount()->value;
//            $currency = $payment->getAmount()->currency;
//            $idempotenceKey = uniqid('', true);
            $response = $client->getPaymentInfo($paymentId);
            dd($response->getId());
            if($response->getStatus() == 'succeeded'){
                return true;
            }else{
                return response()->json(['success' => false, 'message' => 'Payment in processing']);

            }
        }catch (\Exception $exception){
            if($exception->getCode() == 404){
                return response()->json(['success' => false, 'message' => 'Invalid order id']);
            }
        }
    }
}
