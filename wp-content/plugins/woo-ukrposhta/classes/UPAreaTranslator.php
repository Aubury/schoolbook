<?php

namespace deliveryplugin\Ukrposhta\classes;

class UPAreaTranslator
{
  private $translates = [
    '71508128-9b87-11de-822f-000c2965ae0e' => 'АРК',
    '71508129-9b87-11de-822f-000c2965ae0e' => 'Винницкая',
    '7150812a-9b87-11de-822f-000c2965ae0e' => 'Волынская',
    '7150812b-9b87-11de-822f-000c2965ae0e' => 'Днепропетровская',
    '7150812c-9b87-11de-822f-000c2965ae0e' => 'Донецкая',
    '7150812d-9b87-11de-822f-000c2965ae0e' => 'Житомирская',
    '7150812e-9b87-11de-822f-000c2965ae0e' => 'Закарпатская',
    '7150812f-9b87-11de-822f-000c2965ae0e' => 'Запорожская',
    '71508130-9b87-11de-822f-000c2965ae0e' => 'Ивано-Франковская',
    '71508131-9b87-11de-822f-000c2965ae0e' => 'Киевская',
    '71508132-9b87-11de-822f-000c2965ae0e' => 'Кировоградская',
    '71508133-9b87-11de-822f-000c2965ae0e' => 'Луганская',
    '71508134-9b87-11de-822f-000c2965ae0e' => 'Львовская',
    '71508135-9b87-11de-822f-000c2965ae0e' => 'Николаевская',
    '71508136-9b87-11de-822f-000c2965ae0e' => 'Одесская',
    '71508137-9b87-11de-822f-000c2965ae0e' => 'Полтавская',
    '71508138-9b87-11de-822f-000c2965ae0e' => 'Ровенская',
    '71508139-9b87-11de-822f-000c2965ae0e' => 'Сумская',
    '7150813a-9b87-11de-822f-000c2965ae0e' => 'Тернопольская',
    '7150813b-9b87-11de-822f-000c2965ae0e' => 'Харьковская',
    '7150813c-9b87-11de-822f-000c2965ae0e' => 'Херсонская',
    '7150813d-9b87-11de-822f-000c2965ae0e' => 'Хмельницкая',
    '7150813e-9b87-11de-822f-000c2965ae0e' => 'Черкасская',
    '7150813f-9b87-11de-822f-000c2965ae0e' => 'Черновицкая',
    '71508140-9b87-11de-822f-000c2965ae0e' => 'Черниговская'
  ];

  public function translateAreas($areas)
  {
    if (get_option('morkva_ukrposhta_up_lang') === 'ru') {
      foreach ($areas as &$area) {
        if (isset($this->translates[ $area['ref'] ])) {
          $area['description'] = $this->translates[ $area['ref'] ];
        }
      }
    }

    return $areas;
  }
}