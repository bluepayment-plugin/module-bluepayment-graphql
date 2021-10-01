# BlueMedia_BluePaymentGraphQl

Moduł rozszerzający podstawową wersję BlueMedia_BluePayment o API GraphQL.

## Instalacja

### Poprzez composera
1. Wykonaj polecenie
```shell
composer require bluepayment-plugin/module-bluepayment-graphql
```

### Poprzez paczkę .zip
1. Pobrać najnowszą wersję modułu z repozytorium.
2. Wgrać plik .zip do katalogu głównego Magento.
3. Będąc w katalogu głównym Magento, wykonać komendę:
```shell
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

## Własna implementacja komunikacji GraphQL.

### Pobranie dostępnych kanałów płatności

Wszystkie kanały, które są oznaczone jako *Oddzielna metoda płatności*,
są dostępne w obiekcie koszyka w parametrze `available_payment_methods`, przykład:
```graphql
query ($cartId: String!) {
  cart(cart_id: $cartId) {
    id
    available_payment_methods {
      code
      title
    }
  }
}
```
Zwraca odpowiedź
```graphql
{
  "data": {
    "cart": {
      "id": "nt3XNmQLhBl2rNbhfL8g8WuBuYEXwiCe",
      "available_payment_methods": [
        {
          "code": "bluepayment",
          "title": "Płatność Blue Media"
        },
        {
          "code": "bluepayment_509",
          "title": "BLIK"
        },
        {
          "code": "bluepayment_1503",
          "title": "PBC płatność automatyczna"
        },
        {
          "code": "bluepayment_1512",
          "title": "Google Pay"
        },
        {
          "code": "bluepayment_1513",
          "title": "Apple Pay"
        }
      ]
    }
  }
}
```


Aby pobrać wszystkie pozostałe kanały płatności, które są rozwijane po wybraniu głównej metody BlueMedia, wywołaj query `bluepaymentGateways`, przykład:
```graphql
query getBluePaymentGateways($value: Float!, $currency: CurrencyEnum!) {
  bluepaymentGateways(value: $value, currency: $currency) {
    gateway_id
    name
    logo_url
  }
}
```
Gdzie `$value` jest kwotą brutto koszyka (cart.prices.grand_total.value), a `$currency` jest aktualną walutą koszyka (cart.prices.grand_total.currency).
W odpowiedzi otrzymasz tablice obiektów:
```graphql
gateway_id: ID!
name: String!
description: String
sort_order: Int
type: String
logo_url: String!
is_separated_method: Boolean!
```
| Klucz | Opis |
| ------------- | ------------- |
| gateway_id | ID kanału płatności |
| name | Nazwa kanału, zgodnie z ustawieniem w panelu administracyjnym Magento |
| description | Opis kanału (zarządzalny z poziomu panelu adminstracyjnego Magneto) |
| sort_order | Kolejność sortowania (zarządzalna z poziomu panelu adminstracyjnego Magneto) |
| type | Typ kanału płatności (aktuaalnie dostępne opcje: PBL, Szybki Przelew, Raty online, Portfel elektroniczny, BLIK) |
| logo_url | Adres URL do logotypu kanału. Możliwy do nadpisania w panelu administracyjnym Magento |
| is_separated_method | Czy kanał powinien być wyświetlany jako osobna metoda płatności (zgodnie z ustawieniem w panelu administracyjnym Magneto). |


### Pobranie zgód i klauzul dla kanału płatności

Opcja niewymagana. W przypadku nieuzupełnienia ID zgód przy rozpoczęciu płatności - użytkownik zostanie najpierw przekierowany na stronę BM gdzie wyrazi odpowiednie zgody lub zapozna się z klauzulami.
Przykład:
```graphql
query getBluePaymentAgreements($gatewayId: Int!, $currency: CurrencyEnum!, $locale: String!) {
  bluepaymentAgreements(gateway_id: $gatewayId, currency: $currency, locale: $locale) {
    regulation_id
    type
    url
    label_list {
      label_id
      label
      placement
      show_checkbox
      checkbox_required
    }
  }
}
```
Jako `$locale` podajemy język użytkownika w formacie BCP-47, ale podkreśleniem zamiast myślnika (np. pl_PL, en_US).
W odpowiedzi dostajemy listę zgód dla danego kanału płatności.

| Klucz | Opis |
| ------------- | ------------- |
| label_id | Identyfikator klauzuli, przekazywane na potrzeby diagnostyczne (może być przez Partnera ignorowany). |
| label | Treść klauzuli do wyświetlenia w Serwisie w powiązaniu z odpowiednim regulationID. W niektórych przypadkach może zawierać link do regulaminu. |
| placement | Informacja, stanowiąca sugestię, gdzie umieścić klauzule. Aktualne wartości:<br>- TOP_OF_PAGE – na górze Serwisu (np. w okolicach logo/bannera górnego).<br>- NEAR_PAYWALL – w okolicach listy kanałów płatności (bezpośrednio nad, pod lub obok).<br>- ABOVE_BUTTON – nad przyciskiem „Rozpocznij płatność”. |
| show_checkbox | Informacja, czy klauzula powinna być wyświetlana obok checkboxa do akceptacji użytkownika. |
| checkbox_required | Informacja, czy wyświetlany Checkbox musi być zaakceptowany przez użytkownika, aby móc kontynuować płatność.  UWAGA: W przypadku wartości true, należy zablokować przycisk „Rozpocznij płatność”, do czasu zaznaczenia checkboxa. |

Zgody po stronie Magento są cachowane na 15 minut (dla konkretnego kanału płatności, waluty i języka).
 
### Rozpoczęcie płatności

Wywołaj mutację `setPymentMethodOnCart`, podając kod metody płatności na `bluepayment` rozszerzoną o   
Przykład:
```graphql
mutation setPaymentMethodOnCart($cartId: String!, $backUrl: String!, $gatewayId: Int, $agreementsIds: String) {
  setPaymentMethodOnCart(
    input: {
      cart_id: $cartId
      payment_method: {
        code: "bluepayment"
        bluepayment: {
          create_payment: true,
          back_url: $backUrl,
          gateway_id: $gatewayId,
          agreements_ids: $agreementsIds
        }
      }
    }
  ) @connection(key: "setPaymentMethodOnCart") {
    cart {
      id
      selected_payment_method {
        code
        title
      }
    }
  }
} 
```
| Klucz | Opis |
| ------------- | ------------- |
| cart_id | Identyfikator koszyka |
| create_payment | Czy utworzyć transakcję płatniczą w BlueMedia |
| back_url | Informacja, stanowiąca sugestię, gdzie umieścić klauzule. Aktualne wartości:<br>- TOP_OF_PAGE – na górze Serwisu (np. w okolicach logo/bannera górnego).<br>- NEAR_PAYWALL – w okolicach listy kanałów płatności (bezpośrednio nad, pod lub obok).<br>- ABOVE_BUTTON – nad przyciskiem „Rozpocznij płatność”. |
| gateway_id | Opcjonalnie - ID kanału płatności |
| agreements_ids | Opcjonalne - lista zaakceptowanych (show_checkbox=true) lub wyświetlonych (show_checkbox=false) klauzul, rozdzielone przecinkami (np. `1,10,20`) |

Jeśli `create_payment` zostało ustawione na `True`, po złożeniu zamówienia, zostanie rozpoczęta transakcja w BlueMedia.
Następnie pobierz `redirectUrl` dla danego zamówienia i przekieruj użytkownika na podany adres.

## Pobranie redirectUrl

Wywołaj query `redirectUrl`, przykła:
```graphql
query getRedirectUrl($orderNumber: String!) {
  redirectUrl(order_number: $orderNumber)
}
```

W odpowiedzi otrzymasz `redirectUrl` z adresem na jaki należy przekierować użytkownika.
Po zakończeniu płatności, użytkownik zostanie przekierowany na adres podany w parametrze `back_url`
