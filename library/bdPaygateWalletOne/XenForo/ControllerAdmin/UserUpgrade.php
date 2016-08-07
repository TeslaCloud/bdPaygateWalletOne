<?php

class bdPaygateWalletOne_XenForo_ControllerAdmin_UserUpgrade extends XFCP_bdPaygateWalletOne_XenForo_ControllerAdmin_UserUpgrade
{
	public function actionIndex()
	{
		$optionModel = $this->getModelFromCache('XenForo_Model_Option');
		$optionModel->bdPaygateWalletOne_hijackOptions();
		
		return parent::actionIndex();
	}
}