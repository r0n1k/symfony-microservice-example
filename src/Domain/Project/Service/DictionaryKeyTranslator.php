<?php


namespace App\Domain\Project\Service;


use App\Domain\Project\Entity\Dictionary\Key;

class DictionaryKeyTranslator
{

   protected $mappings = [
      // основные справочники
      '^declarant$' => 'Данные Заявителя',
      '^customer$' => 'Данные Технического Заказчика',
      '^developer$' => 'Данные Застройщика',
      '^expertise_organization$' => 'Данные Экспертной организации',
      '^build_object$' => 'Данные объекта строительства',

      // build object
      'build_object.name$' => 'Наименование объекта строительства',

      // bank
      'bank$' => 'Данные банковского счета',
      'bank.name$' => 'Наименование банка',
      'bank.account$' => 'Номер счета',
      'bank.bik$' => 'БИК банка',
      'bank.correspondence_account$' => 'Корреспондентский счет банка',

      // agent
      'declarant.agent$' => 'Представитель заявителя',
      'declarant.agent.passport$' => 'Паспортные данные представителя заявителя',
      'agent.passport$' => 'Паспортные данные представителя',

      // passport
      'passport$' => 'Паспортные данные',
      'passport.serial$' => 'Серия и номер паспорта',
      'passport.code$' => 'Код подразделения',
      'passport.issuer$' => 'Кем выдан',
      'passport.date$' => 'Дата выдачи',

      // procuratory
      'procuratory$' => 'Реквизиты доверенности представителя',
      'declarant.procuratory$' => 'Реквизиты доверенности представителя заявителя',
      'procuratory.date$' => 'Дата выдачи',
      'procuratory.timelimitation$' => 'Срок действия до',

      // misc
      '(developer|customer|declarant).type$' => 'Юридическая форма',
      'customer.contract$' => 'Реквизиты договора на выполнение функций технического заказчика',

      // scalar values
      'lastname$' => 'Фамиля',
      'firstname$' => 'Имя',
      'middlename$' => 'Отчество',
      'last_name$' => 'Фамиля',
      'first_name$' => 'Имя',
      'middle_name$' => 'Отчество',
      'birthday$' => 'Дата рождения',
      'phone$' => 'Телефон',
      'address$' => 'Адрес',
      'org_name$' => 'Наименование организации',
      'lawpost$' => 'Юридический адрес',
      'fullname$' => 'Полное наименование',
      'realpost$' => 'Фактический адрес',
      'foundations$' => 'Основания для действия от имени ИП или ООО',
      'boss_fullname$' => 'ФИО руководителя организации',
      'boss_position$' => 'Должность руководителя организации',
      'date$' => 'Дата',
      'number$' => 'Номер',
      'inn$' => 'ИНН',
      'kpp$' => 'КПП',
      'snils$' => 'СНИЛС',
      'ogrn$' => 'ОГРН',
      'email$' => 'E-Mail',
      'post$' => 'Место жительства с почтовым индексом',
   ];

   public function translate(Key $key): ?string
   {
      foreach ($this->mappings as $regexp => $translation) {
         if (preg_match("/{$regexp}/", $key->getValue())) {
            return $translation;
         }
      }
      return $key->getValue();
   }

}
