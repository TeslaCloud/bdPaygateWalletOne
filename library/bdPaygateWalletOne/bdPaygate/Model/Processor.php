<?php

class bdPaygateWalletOne_bdPaygate_Model_Processor extends XFCP_bdPaygateWalletOne_bdPaygate_Model_Processor
{
    public function getCurrencies()
    {
        $currencies = parent::getCurrencies();

        $currencies[bdPaygateWalletOne_Processor::CURRENCY_RUB] = 'RUB';
        $currencies[bdPaygateWalletOne_Processor::CURRENCY_UAH] = 'UAH';
        $currencies[bdPaygateWalletOne_Processor::CURRENCY_KZT] = 'KZT';

        return $currencies;
    }

    public function getProcessorNames()
    {
        $names = parent::getProcessorNames();

        $names['walletone'] = 'bdPaygateWalletOne_Processor';

        return $names;
    }
}