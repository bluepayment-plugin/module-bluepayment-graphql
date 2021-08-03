# BlueMedia_BluePaymentGraphQl

Moduł rozszerzający podstawową wersję BlueMedia_BluePayment o API GraphQL.

## Instalacja

### Poprzez composera
@ToDo

### Poprzez paczkę .zip
1. Pobrać najnowszą wersję modułu z repozytorium.
2. Wgrać plik .zip do katalogu głównego Magento.
3. Będąc w katalogu głównym Magento, wykonać komendę:
```bash
unzip -o -d app/code/BlueMedia/BluePaymentGraphQl bm-bluepayment-graph-ql-*.zip && rm bm-bluepayment-graph-ql-*.zip
```
4. Przejść do aktywacji modułu.


## Aktywacja
1. Będąc w katalogu głównym Magento, wykonać polecenia:
- ```bin/magento module:enable BlueMedia_BluePaymentGraphQl --clear-static-content```
- ```bin/magento setup:upgrade```
- ```bin/magento setup:di:compile```
- ```bin/magento cache:flush```
2. Moduł został aktywowany.

## Tabela kompatybilności

| BlueMedia_BluePayment  | BlueMedia_BluePaymentGraphQl | bluemedia/bluepayment-pwa (JS) | Magento | Magento PWA |
| ------------- | ------------- | ------------- | ------------- | ------------- |
| 2.16.0 | 1.2.0 | 0.0.4 | 2.4.2 | 10.x |
| 2.15.0 | 1.1.0 | 0.0.3 | 2.4.2 | 10.x |
