<?php

use Paynow\Service\Payment;

trait PaynowFunctions
{
    /**
     * payment
     *
     * @return bool
     */
    private function payment(): bool
    {
        $payment = new Payment($this->client);
        $idempotencyKey = uniqid($this->orderId . '_');
        $this->response = $payment->authorize($this->getRequestBody(), $idempotencyKey);
        return true;
    }

    /**
     * validatePaymentStatus
     *
     * @param  array $paymentId
     * @return bool
     */
    private function validatePaymentStatus(array $paymentId): bool
    {
        $payment = new Payment($this->client);
        $this->response = $payment->status(current($paymentId));
        return true;
    }
}
