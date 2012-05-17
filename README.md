PHP EST kütüphanesi için README dosyasıdır.

AÇIKLAMA
========
  
  Bu arabirim EST tabanlı sanal pos arabirimlerine bağlanıp, belirli metodları çalıştırmak için tasarlanmıştır.

  Arabirim php programlama dili ile geliştirilmiştir.

  Önemli not: Bu arabirim sadece Garanti Bankası, İş Bankası ve Akbank, Anadolubank, Halkbank, Finansbank Sanal POS arabirimleri ile uyumludur.
  Diğer EST tabanlı arabirimler için kullanılamamaktadır.

  Geliştiren: `Özgür Vatansever <ozgurvt@gmail.com>`

  EST Türkiye'nin e-Ticaret güvenli ödeme sistem ve hizmetleri sağlayıcısıdır. Türkiye'de e-Ticaret ödemeleri alanında faaliyet gösteren
  bankaların tamamına yakını bunu EST Ürün, Çözüm, Hizmetlerinden bir veya daha fazlasını kullanarak gerçekleştirmektedir.



KURULUM
=======

  Kütüphaneyi bilgisayarınıza indirmek için;

  1) SVN yardımıyla kurmak için:
     - svn checkout http://php-est.googlecode.com/svn/trunk/ php-est-read-only


KULLANIMI
=========

  Bu arabirimi kullanabilmeniz için "İşyeri No", "Kullanıcı adı" ve "Parola" bilgileri gereklidir. 
  Garanti, İş Bankası ve Akbank Sanal POS arabirimlerinin her biri için ayrı ayrı bu bilgileri edinmeniz gerekmektedir.
  Bu bilgileri edinmek istiyorsanız "destek@est.com.tr" adresine e-posta yollayınız.

  Bu arabirim;
     - Sipariş vermek,
     - Siparişi iptal etme,
     - Siparişten belli bir miktarı iade etme,
     - Yapılmış bir siparişin detaylarını görebilme,
  
  fasilitelerine sahiptir.


  Sanal POS sistemine yapılan bütün istekler EST sınıfı tarafından düzenlenmektedir.

  EST sınıfının yukarıdaki işlemleri yapabilmesi için aşağıdaki metodlar tanımlanmıştır.
     - purchase()   ~ Sipariş vermek için bu metod çağrılır.
     - cancel()     ~ Siparişi iptal etmek için bu metod çağrılır.   
     - refund()     ~ Siparişten belli bir miktar para iade etmek için bu metod çağrılır.
     - getDetail()  ~ Bir siparişin detaylarını görmek için bu metod çağrılır.


  Dökümantasyonun tamamını görüntülemek için lütfen aşağıdaki adresi ziyaret edin.
  - http://code.google.com/p/php-est/


LISANS
======
  Bu arabirim MIT License lisansı ile lisanslanmıştır. Lisansı, aşağıdaki linkte
  görebilirsiniz. 

   - http://www.opensource.org/licenses/mit-license.php
