### 1.2.6
- Dostosowaliśmy moduł do wersji BlueMedia_BluePayment 2.21.2.
- Dodaliśmy obsługę minimalnej i maksymalnej kwoty dla poszczególnych kanałów płatności.
- Poprawiliśmy błąd związany z pobieraniem kwoty zamówienia z nieistniejącej sesji.
- Typ `gateway_id` od teraz zawsze jest ID.

### 1.2.5
- Dodaliśmy nazwę wybranego kanału płatności dla query `customer { orders (...) { items { payment_methods { name } } } } }`
- Poprawiliśmy problem z brakiem `gateway_id` w query `cart { available_payment_methods { ... } }`

### 1.2.4
- Dodaliśmy walidację parametrów dla mutacji `setPaymentMethodOnCart`
- Zmieniliśmy typ `gateway_id` na string
- Poprawiliśmy query `cart {available_payment_methods { ... }}` dla "oddzielnych metod płatności"
- Poprawiliśmy sortowanie kanałów i metod płatności

### 1.2.3
- Dodaliśmy **gateway_id** do `cart { available_payment_methods { ... } }`
- W mutacji **setPaymentMethodOnCart** jako code możemy już wysłać kod w formacie `bluepayment_id`, który jest zwracany przez `available_payment_methods`

### 1.2.2
- Poprawiliśmy błąd występujący przy query `cart(cart_id: '...') { selected_payment_method { ... } }`

### 1.2.0
- Obsługa zgód formalnych

### 1.1.1
- Dodanie whitelabel
- Oznaczanie kanałów jako "oddzielna metoda płatności"

### 1.0.0
- Inicjalna wersja
