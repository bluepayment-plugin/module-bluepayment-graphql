## Tabela kompatybilności

| BlueMedia_BluePayment | BlueMedia_BluePaymentGraphQl | bluemedia/bluepayment-pwa (JS) | Magento       | Magento PWA |
|-----------------------|------------------------------|--------------------------------|---------------|-------------|
| 2.21.2                | 1.2.6                        | 0.0.8                          | 2.4.2 - 2.4.5 | 10.x        |
| 2.17.1                | 1.2.4-1.2.5                  | 0.0.8                          | 2.4.2 - 2.4.3 | 10.x        |
| 2.16.0                | 1.2.2                        | 0.0.4                          | 2.4.2 - 2.4.3 | 10.x        |
| 2.15.0                | 1.1.0                        | 0.0.3                          | 2.4.2         | 10.x        |

## Instalacja modułu

Wykonaj polecenie poprzez composer: 
```bash
composer require bluepayment-plugin/module-bluepayment-graphql
```

## Aktywacja

1. Wejdź do katalgu głównego Magento i wykonaj następujące polecenia:
```bash
bin/magento module:enable BlueMedia_BluePaymentGraphQl --clear-static-content
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:flush
```

2. Gotowe. Moduł jest już aktywny.
<br>

## Szczegóły techniczne

<br>

1. **Czy w dostępnych kanałach płatności w query bluepaymentGateways() są od razu BLIK, płatność kartą i lista banków do szybkich przelewów? Jeśli w standardzie jest zwracanie listy banków, to czy można dostać sam "szybki przelew/PBL" z opcją wyboru konkretnego banku już w WebView?**

Zostaną zwrócone wszystkie kanały, które zostały podpięte w ramach danego serwisu. Oczywiście istnieje możliwość podpięcia jedynie szybkich przelewów/PBL. 

Doprecyzowując, **available_payment_methods** zwraca najpierw wszystkie metody, które są oznaczone jako 'Oddzielna metoda płatności'. To znaczy, że otrzymujemy np.  Płatności Blue Media, BLIK, karty. Następnie można wywołać **bluepaymentGateways** – to query da nam wszystkie kanały dostępne w ramach głównej metody płatności (tj. nieoznaczone jako 'Oddzielna metoda płatności'), np. w przypadku Płatność Blue Media zwracane są poszczególne banki w ramach PBL/szybki przelew.

<br>

2. **Czy klucze płatności gateway_id są stałe i czy czy można je bezpiecznie zapisać na sztywno w kodzie, np. po stronie aplikacji lub backendu?**

Klucze płatności **gateway_id** nie są stałe. Każdy z nich może w przyszłości ulec zmianie, ale takie modyfikacje są zazwyczaj rzadkością. Zdarzają się, gdy dochodzi do fuzji banków lub innej zmiany, która wymusza na nas zmianę danego kanału.

<br>

3. **Czym się różni PBL od szybkich przelewów?**

W PBL dane są podstawiane w bankowości (klient jedynie zatwierdza płatność),  w przypadku szybkiego przelewu klient musi przepisać otrzymane dane. To jedyna różnica między tymi dwiema metodami. Każdy z przelewów jest realizowany wewnątrzbankowo.

<br>

4. **Kiedy możemy mieć do czynienia z is_separated_method?**

Oznacza to, że można rozdzielić kanały jako osobne formy płatności, np wyodrębnić karty od pozostałych, lub pokazać te kanały, z których klienci najczęściej korzystają.

**Gdzie można to ustawić? W panelu Magento?**

Tak, można to ustawić bezpośrednio w panelu Magento 2 – w edycji konkretnego kanału. Można w ten sposób wyodrębnić np. Płatności Blue Media, BLIK, płatności kartą. To znaczy, że np. karty mogą być traktowane jako oddzielna metoda, pokazywane podczas wyboru głównej metody Płatności Blue Media lub być jedną z opcji wśród dostępnych kanałów płatności.

<br>

5. **Dlaczego w mutacji setPaymentMethodOnCart() parametr gateway_id jest opcjonalny?** 

Parametr jest opcjonalny, ponieważ możemy wywołać konkretne kanały, wskazując wówczas ich gateway_id. Nie jest to jednak konieczne – możemy pokazać wszystkie dostępne kanały po przekierowaniu użytkownika na stronę BlueMedia.

<br>
    
6. **Czy redirecturl służy wyłącznie do odnotowania zakończenia procesu płatności, ale nie jest on tożsamy z pozytywnym/negatywnym/oczekującym statusem płatności? Czy jedyną opcją pobrania aktualnego statusu płatności jest cykliczne odpytywanie serwera?**

RedirectUrl odnośni się jedynie do strony powrotu po płatności – widoku, na który klient ma zostać przekierowany po transakcji. Aktualny status transakcji przekazujemy w komunikacie ITN. 

<br>

7. **W jaki sposób pobrać nazwę aktualnie wybranego sposobu płatności, np. "Przelew Volkswagen Bank"?**

Proszę wykonać następującą zapytanie:
```bash
query{
  cart(cart_id: „..."){
    id
    email
    selected_payment_method {
      title
    }
  }
}
Zapytanie zwraca nazwę kanału, np. "PG płatność testowa”.
```
<br>

8. **Przekazując bluemedia_509 mamy techniczną możliwość nieprzekazania payment_method:{bluepayment:{back_url}}. Czy dobrze zakładam, że jednak zawsze należy ją podać?**

Jeżeli nie zostanie podana, wówczas użytkownik zostanie przekierowany na adres powrotu ustawiony w panelu oplacasie-accept.bm.pl (lub oplacasie.bm.pl dla wersji produkcyjnej).

<br>

9. **Skąd pobrać aktualną konfigurację back_url, którą trzeba przekazać przy mutacji setPaymentMethodOnCart? Czy jest ona zdefiniowana gdzieś w panelu Blue Media? Czy może ten parametr jest niezależny od konfiguracji i możemy tutaj przekazać dowolne URL?**

Można przekazać dowolny URL, zaczynający się od http:// lub https://.

<br>

10. **Czy available_payment_methods.code: bluepayment jest stały, czy jest szansa zmiany tego klucza?**

Klucz jest i będzie stały. Nie ma możliwości jego zmiany.

<br>
    
11. **Ile czasu zajmuje powiadomienie serwera klienta/merchanta o statusie płatności?**

System przekazuje powiadomienia o zmianie statusu transakcji niezwłocznie po otrzymaniu takiej informacji z Kanału Płatności (komunikat zawsze dotyczy pojedynczej transakcji). 

<br>

12. **Zapytanie bluepaymentOrder(hash: String!, order_number: String!): BluePaymentOrder! wymaga podania hash, którego nie znamy i nie otrzymujemy ani przy mutacji placeOrder, ani w żadnym innym miejscu. Co teraz?**

W tym przypadku hash jest doklejany do adresu back_url.
    Np. dla ustawionego back_url na https://pwa-studio-latest-accept.blue.pl/bluepayment użytkownik zostanie przekierowany na:
https://pwa-studio-latest-accept.blue.pl/bluepayment?ServiceID=101636&OrderID=k8s_000000139&Hash=30df99b5c49c3568ee465943e3cdab3742aef804f12646df8de66c39c281ee0e 

Hash jest liczony wg. wzoru:
sha256($id_serwisu|$orderId|$klucz_serwisu)

<br>

13. **Jak pobrać aktualnie wybrany kanał płatności?**

Do available_payment_methods został dodany gateway_id. Dla oddzielnych metod płatności zwraca ID kanału bluemedia, dla wszystkich pozostałych metod płatności jest nullem. 

Przykład:
    
```
query getPaymentInformation($cartId: String!) {
  cart(cart_id: $cartId) {
    id
    selected_payment_method {
      code
      __typename
    }
    ...AvailablePaymentMethodsFragment
    __typename
  }
}
fragment AvailablePaymentMethodsFragment on Cart {
  id
  available_payment_methods {
    code
    title
    gateway_id
    __typename
  }
  __typename
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
| Klucz               | Opis                                                                                                                       |
|---------------------|----------------------------------------------------------------------------------------------------------------------------|
| gateway_id          | ID kanału płatności                                                                                                        |
| name                | Nazwa kanału, zgodnie z ustawieniem w panelu administracyjnym Magento                                                      |
| description         | Opis kanału (zarządzalny z poziomu panelu adminstracyjnego Magneto)                                                        |
| sort_order          | Kolejność sortowania (zarządzalna z poziomu panelu adminstracyjnego Magneto)                                               |
| type                | Typ kanału płatności (aktuaalnie dostępne opcje: PBL, Szybki Przelew, Raty online, Portfel elektroniczny, BLIK)            |
| logo_url            | Adres URL do logotypu kanału. Możliwy do nadpisania w panelu administracyjnym Magento                                      |
| is_separated_method | Czy kanał powinien być wyświetlany jako osobna metoda płatności (zgodnie z ustawieniem w panelu administracyjnym Magneto). |

<br>

14. **Czy można przekazać kanał płatności, podając kod w formacie bluepayment_509?**

Dla mutacji **setPaymentMethodOnCart** została dodana obsługa kodów w formacie **bluepayment_509**. Jeśli w taki sposób zostanie wysłany kod metody płatności – backendowo zostanie on „przepisany” na odpowiednio bluepayment z kanałem 509.

Przykład:
```graphql
query getBluePaymentAgreements($gatewayId: ID!, $currency: CurrencyEnum!, $locale: String!) {
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

| Klucz             | Opis                                                                                                                                                                                                                                                                                                          |
|-------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| label_id          | Identyfikator klauzuli, przekazywane na potrzeby diagnostyczne (może być przez Partnera ignorowany).                                                                                                                                                                                                          |
| label             | Treść klauzuli do wyświetlenia w Serwisie w powiązaniu z odpowiednim regulationID. W niektórych przypadkach może zawierać link do regulaminu.                                                                                                                                                                 |
| placement         | Informacja, stanowiąca sugestię, gdzie umieścić klauzule. Aktualne wartości:<br>- TOP_OF_PAGE – na górze Serwisu (np. w okolicach logo/bannera górnego).<br>- NEAR_PAYWALL – w okolicach listy kanałów płatności (bezpośrednio nad, pod lub obok).<br>- ABOVE_BUTTON – nad przyciskiem „Rozpocznij płatność”. |
| show_checkbox     | Informacja, czy klauzula powinna być wyświetlana obok checkboxa do akceptacji użytkownika.                                                                                                                                                                                                                    |
| checkbox_required | Informacja, czy wyświetlany Checkbox musi być zaakceptowany przez użytkownika, aby móc kontynuować płatność.  UWAGA: W przypadku wartości true, należy zablokować przycisk „Rozpocznij płatność”, do czasu zaznaczenia checkboxa.                                                                             |

Zgody po stronie Magento są cachowane na 15 minut (dla konkretnego kanału płatności, waluty i języka).
 
### Rozpoczęcie płatności

Wywołaj mutację `setPymentMethodOnCart`, podając kod metody płatności na `bluepayment` rozszerzoną o   
Przykład:
```graphql
mutation setPaymentMethodOnCart($cartId: String!, $backUrl: String!, $gatewayId: ID, $agreementsIds: String) {
  setPaymentMethodOnCart(
    input: {
      cart_id: $cartId
      payment_method: {
          code: "bluepayment_1899"
            bluepayment: {
              create_payment: true
              back_url: $backUrl,
            agreements_ids: $agreementsIds
            }
      }
  }) {
    cart {
      selected_payment_method {
        code
        title
      }
    }
  }
} 
```
| Klucz          | Opis                                                                                                                                                                                                                                                                                                          |
|----------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| cart_id        | Identyfikator koszyka                                                                                                                                                                                                                                                                                         |
| create_payment | Czy utworzyć transakcję płatniczą w BlueMedia                                                                                                                                                                                                                                                                 |
| back_url       | Informacja, stanowiąca sugestię, gdzie umieścić klauzule. Aktualne wartości:<br>- TOP_OF_PAGE – na górze Serwisu (np. w okolicach logo/bannera górnego).<br>- NEAR_PAYWALL – w okolicach listy kanałów płatności (bezpośrednio nad, pod lub obok).<br>- ABOVE_BUTTON – nad przyciskiem „Rozpocznij płatność”. |
| gateway_id     | Opcjonalnie - ID kanału płatności                                                                                                                                                                                                                                                                             |
| agreements_ids | Opcjonalne - lista zaakceptowanych (show_checkbox=true) lub wyświetlonych (show_checkbox=false) klauzul, rozdzielone przecinkami (np. `1,10,20`)                                                                                                                                                              |

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
