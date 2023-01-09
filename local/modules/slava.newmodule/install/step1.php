<?php

use Bitrix\Main\Localization\Loc;

if (!check_bitrix_sessid())
    return;

global $APPLICATION;

?>
<? if ($ex = $APPLICATION->GetException()): ?>
    <? $exString = $ex->GetString();
    CAdminMessage::ShowMessage(
        [
            'TYPE' => 'ERROR',
            'MESSAGE' => $exString,
            'DETAILS' => $ex->GetString(),
            'HTML' => true
        ]
    ); ?>
    <form action="<? $APPLICATION->GetCurPage() ?>">
        <input type="hidden" name="lang" value="<?= LANG; ?>"/>
        <input type="hidden" name="uninstall" value="Y"/>
        <input type="submit" name="" value="<?= Loc::getMessage('HM_INST_BACK'); ?>"/>
    </form>
<? else: ?>
    <? {
        CAdminMessage::ShowNote(
            Loc::getMessage('HM_INST_OK')
        );
    }
    ?>
    <form action="<? $APPLICATION->GetCurPage() ?>">
        <input type="hidden" name="lang" value="<?= LANG; ?>"/>
        <input type="submit" name="" value="<?= Loc::getMessage('HM_INST_BACK'); ?>"/>
    </form>
<!--    <form action="http://b24/bitrix/admin/settings.php">-->
<!--        <input type="hidden" name="lang" value="--><?php //= LANG; ?><!--"/>-->
<!--        <input type="hidden" name="mid" value="hive.mymodule"/>-->
<!--        <input type="submit" name="" value="--><?php //= Loc::getMessage('HM_INST_TO_OPTION'); ?><!--"/>-->
<!--    </form>-->
<? endif; ?>