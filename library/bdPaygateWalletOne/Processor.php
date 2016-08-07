<?php

class bdPaygateWalletOne_Processor extends bdPaygate_Processor_Abstract
{
    const CURRENCY_RUB = 'rub';
    const CURRENCY_UAH = 'uah';
    const CURRENCY_KZT = 'kzt';

    public function getSupportedCurrencies()
    {
        $currencies = array();
        $currencies[] = self::CURRENCY_RUB;
        $currencies[] = self::CURRENCY_UAH;
        $currencies[] = self::CURRENCY_KZT;
        $currencies[] = self::CURRENCY_USD;
        $currencies[] = self::CURRENCY_EUR;

        return $currencies;
    }

    public function isAvailable()
    {
        $options = XenForo_Application::getOptions();
        // Единая Касса не поддерживает тестовый режим,
        // поэтому на всякий случай отключаем её, если включён "Sandbox"
        if (empty($options->bdPaygateWalletOne_ID) || empty($options->bdPaygateWalletOne_SecretKey) || $this->_sandboxMode()) {
            return false;
        }

        return true;
    }

    public function isRecurringSupported()
    {
        return false;
    }

    public function validateCallback(Zend_Controller_Request_Http $request, &$transactionId, &$paymentStatus, &$transactionDetails = array(), &$itemId)
    {
        // TODO: Пофиксить алгоритм
        $input = new XenForo_Input($request);
        $transactionDetails = $input->getInput();

        $signature = $transactionDetails['WMI_SIGNATURE'];

        $transactionId = (!empty($transactionDetails['WMI_ORDER_ID']) ? ('walletone_' . $transactionDetails['WMI_ORDER_ID']) : '');
        $paymentStatus = bdPaygate_Processor_Abstract::PAYMENT_STATUS_OTHER;

        $processorModel = $this->getModelFromCache('bdPaygate_Model_Processor');
        $options = XenForo_Application::get('options');
        $walletone_key = $options->bdPaygateWalletOne_SecretKey;

        // Проверяем, не была ли уже проведена такая операция
        $log = $processorModel->getLogByTransactionId($transactionId);
        if (!empty($log)) {
            $this->_setError("Transaction {$transactionId} has already been processed");
            echo "WMI_RESULT=RETRY";
            return false;
        }

        /// Генерация MD5 подписи
        // Удаление лишних элементов из массива
        unset($transactionDetails['WMI_SIGNATURE'], $transactionDetails['p'], $transactionDetails['0'], $transactionDetails['_callbackIp']);
        // Сортировка эл-тов массива в алфавитном порядке
        ksort($transactionDetails, SORT_STRING);
        // Конкатенация значений
        $crc = implode($transactionDetails);
        // Кодирование MD5 хэша в BASE64
        $crc = base64_encode(pack("H*", md5($crc . $walletone_key)));

        // Сверяем нашу подпись с той, которую мы получили
        if ($crc != $signature) {
            $this->_setError('Request not validated + ' . $crc . ' + ' . $signature);
            return false;
        }

        // https://www.walletone.com/ru/merchant/documentation/#step5
        switch ($transactionDetails['WMI_ORDER_STATE']) {
            case "Accepted":
                // Платеж успешно проведен
                $itemId = $transactionDetails['PAYMENT_ITEM'];
                $paymentStatus = bdPaygate_Processor_Abstract::PAYMENT_STATUS_ACCEPTED;
                echo "WMI_RESULT=OK";
                break;
            default:
                $paymentStatus = bdPaygate_Processor_Abstract::PAYMENT_STATUS_REJECTED;
        }

        return true;
    }

    public function generateFormData($amount, $currency, $itemName, $itemId, $recurringInterval = false, $recurringUnit = false, array $extraData = array())
    {
        $this->_assertAmount($amount);
        $this->_assertCurrency($currency);
        $this->_assertItem($itemName, $itemId);
        $this->_assertRecurring($recurringInterval, $recurringUnit);

        $formAction = 'https://wl.walletone.com/checkout/checkout/Index';
        $callToAction = new XenForo_Phrase('bdpaygate_walletone_call_to_action');

        $visitor = XenForo_Visitor::getInstance();
        $options = XenForo_Application::get('options');
        $walletone_key = $options->bdPaygateWalletOne_SecretKey;

        switch (utf8_strtoupper($currency)) {
            case 'RUR':
                $currency_id = 643;
                break;
            case 'USD':
                $currency_id = 840;
                break;
            case 'EUR':
                $currency_id = 978;
                break;
            case 'UAH':
                $currency_id = 980;
                break;
            case 'KZT':
                $currency_id = 398;
                break;
            default:
                $currency_id = 643;
        }

        $payment = array(
            'PAYMENT_ITEM' => $itemId,
            'WMI_DESCRIPTION' => "BASE64:" . base64_encode($itemName),
            'WMI_PAYMENT_AMOUNT' => $amount,
            'WMI_CURRENCY_ID' => $currency_id,
            'WMI_MERCHANT_ID' => $options->bdPaygateWalletOne_ID,
            'WMI_SUCCESS_URL' => $options->bdPaygateWalletOne_SuccessUrl,
            'WMI_FAIL_URL' => $options->bdPaygateWalletOne_FailUrl,
            '_csrfToken' => $visitor['csrf_token_page'],
        );

        /// Генерация MD5 подписи для формы
        // Сортировка эл-тов массива в алфавитном порядке
        ksort($payment, SORT_STRING);
        // Конкатенация значений
        $crc = implode($payment);
        // Кодирование MD5 хэша в BASE64
        $crc = base64_encode(pack("H*", md5($crc . $walletone_key)));;

        $form = <<<EOF
            <form action="{$formAction}" method="POST">
                <input type="hidden" name="WMI_MERCHANT_ID"     value="{$payment['WMI_MERCHANT_ID']}" />
                <input type="hidden" name="PAYMENT_ITEM"        value="{$payment['PAYMENT_ITEM']}" />
                <input type="hidden" name="WMI_DESCRIPTION"     value="{$payment['WMI_DESCRIPTION']}" />
                <input type="hidden" name="WMI_PAYMENT_AMOUNT"  value="{$payment['WMI_PAYMENT_AMOUNT']}" />
                <input type="hidden" name="WMI_CURRENCY_ID"     value="{$payment['WMI_CURRENCY_ID']}" />
                <input type="hidden" name="WMI_SUCCESS_URL"     value="{$payment['WMI_SUCCESS_URL']}" />
                <input type="hidden" name="WMI_FAIL_URL"        value="{$payment['WMI_FAIL_URL']}" />
                <input type="hidden" name="WMI_SIGNATURE"       value="{$crc}" />
                <input type="hidden" name="_csrfToken"          value="{$payment['_csrfToken']}" />
                
                <input type="submit" value="{$callToAction}" class="button" />
            </form>
EOF;

        return $form;
    }
}