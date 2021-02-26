<?php

namespace PayXpert\Connect2Pay\containers\response;

use PayXpert\Connect2Pay\containers\Container;
use PayXpert\Connect2Pay\containers\response\TransactionAttempt;

class ExportTransactionsResponse extends Container
{

    private $transactions;

    public function getTransactions()
    {
        return $this->transactions;
    }

    public function setTransactions($transactions)
    {
        $this->transactions = $transactions;
        return $this;
    }

    public static function getFromJson($dataJson)
    {
        $response = null;

        if ($dataJson != null && is_object($dataJson)) {

            $response = new ExportTransactionsResponse();
            $response->transactions = array();

            if (isset($dataJson->transactions) && is_array($dataJson->transactions)) {
                foreach ($dataJson->transactions as $transactionJson) {
                    $response->transactions[] = TransactionAttempt::getFromJson($transactionJson);
                }
            }
        }

        return $response;
    }
}