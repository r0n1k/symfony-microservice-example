<?php


namespace App\Domain\Project\Entity\Dictionary;

/**
 * Ключ словаря
 */
class Key
{
   /**
    * @var string
    *
    * elements:
    *    experts_org // экспертная организация
    *       inn
    *       kpp
    *       ogrn
    *       address
    *       phone
    *       email
    *       site_url
    *    declarant // заявитель
    *       law_form // юр лицо, ип или физ
    *       org_name
    *       inn
    *       kpp
    *       ogrn
    *       address
    *       phone
    *       email
    *       declarant_delegate / представитель заявителя
    *          last_name
    *          first_name
    *          middle_name
    *          birthday
    *          passport_serial // серия и номер
    *          passport_date // дата выдачи
    *          passport_issuer // кем выдан
    *          passport_issuer_code // код подразделения
    *          address
    *          snils
    *          inn
    *          phone
    *          email
    *       procuratory / доверенность
    *          number
    *          issuer // кем удостоверена
    *          date_from // дата выдачи
    *          data_to // срок действия до
    *    builder // застройщик
    *    technical_customer // технический заказчик
    *    teps // тэпы
    *    build_object // свденения об объекте кап строительства
    *    docs_developers // сведения о лицах, подготовивших доки
    */
   protected string $value;

   public function __construct(string $key)
   {
      $this->value = $key;
   }

    public static function of($key)
    {
       return new self((string)$key);
    }

    public function getValue()
   {
      return $this->value;
   }

   public function __toString()
   {
      return $this->value ?: '';
   }
}
