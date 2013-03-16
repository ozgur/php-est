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