# [bd]Paygate: WalletOne
Данная модификация позволяет вам настроить приём платежей на своем форуме через платежный шлюз [Единая Касса](https://w1.ru/)

## Требования
Требуется аддон [bd] Paygates версии не ниже 1.5.2

## Установка и настройка

### Настройка магазина WalletOne

* Откройте Настройки и выберите вкладку "Интеграция"
 * Выберите Метод формирования ЭЦП "MD5"
 * Укажите URL скрипта в формате 'http://domain.com/bdpaygate/callback.php?p=walletone'. Тип запроса должен быть "POST".
* Все должно быть примерно так:
![Image](https://matew.pw/screens/clip-2016-08-07-23-13-48-44676571.png)

### Настройка XenForo

* Укажите в настройках данные, которые вы получили на странице своего магазина. Только первые два поля являются обязательными, остальное - опционально, но там желательно указать ссылку на главную страницу форума.
![Image](https://matew.pw/screens/clip-2016-08-07-23-07-02-89895451.png)
