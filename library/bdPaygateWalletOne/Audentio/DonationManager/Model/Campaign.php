<?php

class bdPaygateWalletOne_Audentio_DonationManager_Model_Campaign extends XFCP_bdPaygateWalletOne_Audentio_DonationManager_Model_Campaign
{
    public function getCurrencies($currency = false)
    {
        $currencies = parent::getCurrencies();

        $currencies[bdPaygateWalletOne_Processor::CURRENCY_RUB] = array(
            'name' => 'RUB',
            'suffix' => '₽'
        );
        $currencies[bdPaygateWalletOne_Processor::CURRENCY_UAH] = array(
            'name' => 'UAH',
            'suffix' => '₴'
        );
        $currencies[bdPaygateWalletOne_Processor::CURRENCY_KZT] = array(
            'name' => 'KZT',
            'suffix' => '₸'
        );

        if ($currency && array_key_exists($currency, $currencies)) {
            return $currencies[$currency];
        }

        return $currencies;
    }
}