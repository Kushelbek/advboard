<?php
/**
 * Notify user: advboard is moderated
 *
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 *
 * You can use templates: advboard.notify_user_moderated.<lang>.<categoty>.php
 */

/** @var advboard_model_Advert $advert */
$advert = $this->advert;
$title = (!empty($advert->title)) ? $advert->title : cot::$L['advboard_no_title'];

if($advert->owner['user_id'] == 0) {
?>
Добрый день!
<?php } else { ?>
    Добрый день, <?=htmlspecialchars($advert->owner['full_name'])?>!
<?php } ?>

<p>
    Ваше объявление: <a href="<?=$this->advertUrl?>"><?=htmlspecialchars($title)?></a> успешно прошло модерацию и доступно для просмотра другими пользователями.
</p>
<p>
    Статус объявления: <b><?=cot::$L['advboard_state_'.$advert->state]?></b>
</p>
<?php if($this->advertText != '') { ?>
    <hr />
    <?=$this->advertText?>
    <hr />
<?php } ?>
<p>
    Объявление находится по адресу: <a href="<?=$this->advertUrl?>"><?=$this->advertUrl?></a>
</p>