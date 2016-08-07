<?php

class bdPaygateWalletOne_XenForo_Model_Option extends XFCP_bdPaygateWalletOne_XenForo_Model_Option
{
    // this property must be static because XenForo_ControllerAdmin_UserUpgrade::actionIndex
    // for no apparent reason use XenForo_Model::create to create the optionModel
    // (instead of using XenForo_Controller::getModelFromCache)
    private static $_bdPaygateWalletOne_hijackOptions = false;

    public function getOptionsByIds(array $optionIds, array $fetchOptions = array())
    {
        if (self::$_bdPaygateWalletOne_hijackOptions === true)
        {
            $optionIds[] = 'bdPaygateWalletOne_ID';
            $optionIds[] = 'bdPaygateWalletOne_SecretKey';
            $optionIds[] = 'bdPaygateWalletOne_SuccessUrl';
            $optionIds[] = 'bdPaygateWalletOne_FailUrl';
        }

        $options = parent::getOptionsByIds($optionIds, $fetchOptions);

        self::$_bdPaygateWalletOne_hijackOptions = false;

        return $options;
    }

    public function bdPaygateWalletOne_hijackOptions()
    {
        self::$_bdPaygateWalletOne_hijackOptions = true;
    }
}