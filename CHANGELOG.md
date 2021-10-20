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
