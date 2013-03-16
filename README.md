# PHP EST

PHP tabanlı EST Sanal POS Sistemleri Arabirimi

[![EST](http://www.asseco-see.com.tr/images/est_logo.jpg)](http://www.asseco-see.com.tr/index.asp)

Bu arabirim EST tabanlı sanal pos arabirimlerine bağlanıp, kredi kartı ile sipariş verme işleri için geliştirilmiştir.

Bu arabirim sadece **İş Bankası**, **Akbank**, **Anadolubank**, **Halkbank**, **Finansbank** Sanal POS arabirimleri ile uyumludur. Diğer EST tabanlı arabirimler için kullanılamamaktadır.

EST Türkiye'nin e-Ticaret güvenli ödeme sistem ve hizmetleri sağlayıcısıdır. Türkiye'de e-Ticaret ödemeleri alanında faaliyet gösteren bankaların tamamına yakını bunu EST Ürün, Çözüm, Hizmetlerinden bir veya daha fazlasını kullanarak gerçekleştirmektedir.


## Kurulum

Bu arabirim PHP 5.3.2 ya da daha yüksek sürümlerinde kullanılabilmektedir. Kurulum için [composer](http://getcomposer.org/download/) paket yöneticisi kurmanız gerekmektedir.

    $ php composer.phar install


## Kullanımı

Bu arabirimi kullanabilmeniz için **İşyeri No**, **Kullanıcı adı** ve **Parola** bilgileri gereklidir. Her bir bankanın kendi sanal POS arabirimlerini kullanabilmek için ayrı ayrı bu bilgileri edinmeniz gerekmektedir. Bu bilgileri edinmek istiyorsanız **destek@est.com.tr** adresine e-posta yollayınız.

Bu arabirim ile aşağıdaki pos işlemleri yapılabilir;

  * Sipariş vermek,
  * Siparişi iptal etme,
  * Siparişten belli bir miktarı iade etme,
  * Yapılmış bir siparişin detaylarını görebilme,

Sanal POS sistemine yapılan bütün istekler EST sınıfı tarafından düzenlenmektedir. EST sınıfının yukarıdaki işlemleri yapabilmesi için aşağıdaki metodlar tanımlanmıştır.

  * purchase()   ~ Sipariş vermek için bu metod çağrılır.
  * postAuth()   ~ Bloke edilen miktarı karttan çekmek için bu metod çağrılır.
  * cancel()     ~ Siparişi iptal etmek için bu metod çağrılır.
  * refund()     ~ Siparişten belli bir miktar para iade etmek için bu metod çağrılır.
  * getDetail()  ~ Bir siparişin detaylarını görmek için bu metod çağrılır.

```php
require 'est.php';
$api = new EST("akbank", "100100000", "AKTEST", "AKTEST123", $debug=TRUE);
```

Eğer test sunucusu değil de gerçek ortamda çalışmak istiyorsanız **debug** parametresini FALSE olarak set ediniz.

Sipariş verme isteği göndermemiz için **.pay()** metodunu çağırmanız gerekmektedir. Bu metodu çağırmak için sırasıyla aşağıdaki parametreler gerekmektedir.

```php
$cc_num = "5456165456165454"; // kart numarası
$cc_cvv = "000";
$month = "12";
$year = "12";
$amount = 10.00;
$taksit = 0; // peşin
$order_num = "qwaszx"; // sipariş numarası
$result = $api->pay($cc_num, $cc_cvv, $month, $year, $amount, $taksit, $order_num);
print_r($result);
Array
(
    [orderid] => qwaszx
    [transid] => 10177-TeYF-1-1543
    [groupid] => qwaszx
    [response] => Approved
    [return_code] => 00
    [error_msg] =>
    [host_msg] => Onay
    [auth_code] => 116745
    [result] => 1
    [transaction_time] => Array
        (
            [tm_sec] => 24
            [tm_min] => 30
            [tm_hour] => 19
            [tm_mday] => 26
            [tm_mon] => 5
            [tm_year] => 110
            [tm_wday] => 6
            [tm_yday] => 176
            [unparsed] =>
        )

)
```

Aynı sipariş numarasıyla tekrar istek yaparsak hata alırız.

```php
$result = $api->pay($cc_num, $cc_cvv, $month, $year, $amount, $taksit, $order_num);
print_r($result);
Array
(
    [orderid] => qwaszx
    [transid] => 10177-TfgE-1-1544
    [groupid] => qwaszx
    [response] => Error
    [return_code] => 99
    [error_msg] => Bu siparis numarasi ile zaten basarili bir siparis var.
    [host_msg] =>
    [auth_code] =>
    [result] =>
    [transaction_time] => Array
        (
            [tm_sec] => 32
            [tm_min] => 31
            [tm_hour] => 19
            [tm_mday] => 26
            [tm_mon] => 5
            [tm_year] => 110
            [tm_wday] => 6
            [tm_yday] => 176
            [unparsed] =>
        )

)
```

Ekstra parametrelerde **fatura adresi** ve **teslimat adresi** ile ilgili detayları set edebilirsiniz.

```php
$extra = array("shipping_address_name" => "Ev Adresim", "billing_address_name" => "Fatura Adresim");
$api->pay($cc_num, $cc_cvv, $month, $year, $amount, $taksit, $order_num, $typ="Auth", $extra=$extra);
```

Kullanıcının kredi kartındaki belli bir miktara bloke koymak için *typ* parametresini **PreAuth** olarak göndermeniz gerekmektedir.

```php
$api->pay($cc_num, $cc_cvv, $month, $year, $amount, $taksit, $order_num, $typ="PreAuth");
```

Bloke koyduğumuz miktarı kullanıcının kartından çekmek için **.postAuth()** metodunu çekmek istediğiniz miktar ile çağırmanız gerekmektedir.

```php
$result = $api->postAuth($amount, $order_num);
print_r($result);
Array
(
    [orderid] => qwaszx
    [transid] => 10177-TpIF-1-1549
    [groupid] => qwaszx
    [response] => Approved
    [return_code] => 00
    [error_msg] =>
    [host_msg] =>
    [auth_code] => 691348
    [host_ref_num] => 017719080777
    [result] => 1
    [transaction_time] => Array
        (
            [tm_sec] => 8
            [tm_min] => 41
            [tm_hour] => 19
            [tm_mday] => 26
            [tm_mon] => 5
            [tm_year] => 110
            [tm_wday] => 6
            [tm_yday] => 176
            [unparsed] =>
        )

)
```

Siparişi yada yaptığınız postAuth isteğini iptal etmek için **.cancel()** metodu çağrılmalıdır. Sipariş numarası parametre olarak verilmelidir.

```php
$result = $api->cancel($order_num);
print_r($result);
Array
(
    [orderid] => qwaszx
    [transid] => 10177-TpIF-1-1549
    [groupid] => qwaszx
    [response] => Approved
    [return_code] => 00
    [error_msg] =>
    [host_msg] =>
    [auth_code] => 691348
    [host_ref_num] => 017719080777
    [result] => 1
    [transaction_time] => Array
        (
            [tm_sec] => 8
            [tm_min] => 41
            [tm_hour] => 19
            [tm_mday] => 26
            [tm_mon] => 5
            [tm_year] => 110
            [tm_wday] => 6
            [tm_yday] => 176
            [unparsed] =>
        )

)
```

Var olmayan ya da daha önceden iptal edilmiş bir siparişi tekrar iptal etmeye çalışılırsa sunucudan aşağıdaki gibi bir cevap alınır.

```php
$result = $api->cancel("123456abcdef");
print_r($result);
Array
(
    [orderid] => 123456abcdef
    [transid] => 10177-TtuB-1-1556
    [groupid] => 123456abcdef
    [response] => Error
    [return_code] => 99
    [error_msg] => İptal edilmeye uygun satış işlemi bulunamadı.
    [host_msg] =>
    [auth_code] =>
    [host_ref_num] =>
    [result] =>
    [transaction_time] => Array
        (
            [tm_sec] => 46
            [tm_min] => 45
            [tm_hour] => 19
            [tm_mday] => 26
            [tm_mon] => 5
            [tm_year] => 110
            [tm_wday] => 6
            [tm_yday] => 176
            [unparsed] =>
        )

)
```

Siparişten belli bir miktarın tutarının müşterinin kartına geri yüklenmesi işlem için **.refund()** metodu çağrılmalıdır.

```php
$result = $api->refund($amount = 5.00, $orderid = $order_num);
print_r($result);
Array
(
    [orderid] => qwaszx
    [transid] => 10177-TxYA-1-1558
    [groupid] => qwaszx
    [response] => Approved
    [return_code] => 00
    [error_msg] =>
    [host_msg] => Onay
    [auth_code] => 154681
    [host_ref_num] => 017719080780
    [result] => 1
    [transaction_time] => Array
        (
            [tm_sec] => 24
            [tm_min] => 49
            [tm_hour] => 19
            [tm_mday] => 26
            [tm_mon] => 5
            [tm_year] => 110
            [tm_wday] => 6
            [tm_yday] => 176
            [unparsed] =>
        )

)
```

Eğer sipariş tutarından daha büyük bir tutar iptal edilmeye çalışırsa aşağıdaki cevap alınır.

```php
$result = $api->refund($amount = 9999.0, $orderid = $order_num);
print_r($result);
Array
(
    [orderid] => qwaszx
    [transid] => 10177-TybA-1-1559
    [groupid] => qwaszx
    [response] => Error
    [return_code] => 99
    [error_msg] => Net miktardan fazlasi iade edilemez.
    [host_msg] =>
    [auth_code] =>
    [host_ref_num] =>
    [result] =>
    [transaction_time] => Array
        (
            [tm_sec] => 27
            [tm_min] => 50
            [tm_hour] => 19
            [tm_mday] => 26
            [tm_mon] => 5
            [tm_year] => 110
            [tm_wday] => 6
            [tm_yday] => 176
            [unparsed] =>
        )

)
```

Eğer **.refund()** metodu ile yaptığınız iade isteğini iptal etmek istiyorsanız iade işleminden size cevap olarak gönderilen **transid** ve **orderid** değerlerini **.cancel()** metoduna göndermeniz gerekmektedir.

```php
$result = $api->cancel($orderid = $order_num, $transid = '10177-TxYA-1-1558');
print_r($result);
Array
(
    [orderid] => qwaszx
    [transid] => 10177-TxYA-1-1558
    [groupid] => qwaszx
    [response] => Approved
    [return_code] => 00
    [error_msg] =>
    [host_msg] =>
    [auth_code] => 154681
    [host_ref_num] => 017719080780
    [result] => 1
    [transaction_time] => Array
        (
            [tm_sec] => 24
            [tm_min] => 49
            [tm_hour] => 19
            [tm_mday] => 26
            [tm_mon] => 5
            [tm_year] => 110
            [tm_wday] => 6
            [tm_yday] => 176
            [unparsed] =>
        )

)
```

Daha önceden verilmiş bir siparişin detayı öğrenilmek isteniyorsa **.getDetail()** metodu kullanılmalıdır. Bu metoda sipariş numarası parametre olarak verilir.

```php
$result = $api->getDetail($order_num);
print_r($result);
Array
(
    [transid] => 10177-TK3E-1-1540
    [orderid] => testorderid01234
    [return_code] => 00
    [host_ref_num] => 017719080774
    [error_msg] => Record(s) found for testorderid01234
    [charge_type] => S
    [auth_code] => 931005
    [amount] => 10
    [transaction_time] => Array
        (
            [tm_sec] => 53
            [tm_min] => 10
            [tm_hour] => 19
            [tm_mday] => 26
            [tm_mon] => 5
            [tm_year] => 110
            [tm_wday] => 6
            [tm_yday] => 176
            [unparsed] =>
        )

)
```

## İptal ve İade Arasındaki Farklar

Bilindiği gibi sanal POS'larda da gerçek POS hesapları gibi gün sonu kavramı vardır. Gün sonu kavramı; gün içinde POS ile ilgili yapılan işlemlerin (para çekimi ve para iadesi gibi) gün sonunda POS sahibinin banka hesabına aktarılması demektir.

Siparişin iptal işlemi gün sonu gelmeden **sadece** aynı gün içinde yapılabilir. Önceki güne ait siparişler iptal edilemezler. Önceki güne ait siparişler ancak **.refund()** metodu ile siparişin tutarı girilerek iade edilirler.

Eğer sipariş iptal edilirse; siparişin yapıldığı ve iptal edildiği gibi detaylar kart sahibinin ektresinde görünmez. Eğer iade yapılırsa iade işlemi kart sahibinin ekstresine yansır. Bankaların çoğunda gün sonu akşam saat **22:00**'dir. Fakat bu saati bankalar durumlarına göre değiştirebilirler.
