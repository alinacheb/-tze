<?

use Bitrix\Highloadblock\HighloadBlockTable;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("ТЗ");



\Bitrix\Main\Loader::includeModule('iblock');
\Bitrix\Main\Loader::includeModule('highloadblock');

$iblock = CIBlock::GetList([], ['CODE' => 'tz'])->Fetch();
if ($iblock) {

    $arHlData = [];
    $hlblock = HighloadBlockTable::getList([
        'filter' => ['TABLE_NAME' => 'hl_tz']
    ])->fetch();
    if ($hlblock) {
        $entity = HighloadBlockTable::compileEntity($hlblock);
        $dataClass = $entity->getDataClass();
        $result = $dataClass::getList([]);
        while ($record = $result->fetch()) {
            $arHlData[$record['UF_XML_ID']] = $record;
        }
    } else {
        echo 'Highload-блок не найден.';
    }


    $iblockId = $iblock['ID'];

    $itemsPerPage = 20; // Количество элементов на странице
    $arFilter = ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y'];

    $pageNum = (isset($_GET['PAGEN_1']) ? intval($_GET['PAGEN_1']) : 1);
    $totalCount = \CIBlockElement::GetList([], $arFilter, []);
    $totalPages = ceil($totalCount / $itemsPerPage);

    $res = \CIBlockElement::GetList(
        ['ID' => 'ASC'],
        $arFilter,
        false,
        ['nPageSize' => $itemsPerPage, 'iNumPage' => $pageNum],
        ['ID', 'NAME', 'PROPERTY_TEXT', 'PROPERTY_LIST', 'PROPERTY_HL_LIST']
    );
    while ($arFields = $res->Fetch()) {
        ?>
        <div class="element_item">
            <div class="_name"><strong><?=$arFields['NAME']?></strong></div>
            <div class="_name">Текстовое: <?=$arFields['PROPERTY_TEXT_VALUE']?></div>
            <div class="_name">Списочное: <?=$arFields['PROPERTY_LIST_VALUE'] ? implode(', ', $arFields['PROPERTY_LIST_VALUE']) : ''?></div>
            <div class="_name">Справочник: <?=$arHlData[$arFields['PROPERTY_HL_LIST_VALUE']] ? $arHlData[$arFields['PROPERTY_HL_LIST_VALUE']]['UF_NAME'] : ''?></div>
        </div>
        <?
    }

    if ($totalPages > 1) {
        ?>
        <div class="pagination">
            <?for ($i = 1; $i <= $totalPages; $i++) {?>
                <div class="pag_item">
                    <?if ($i == $pageNum) {?>
                        <strong><?=$i?></strong>
                    <?} else {
                        $href = $i == 1 ? $APPLICATION->GetCurPage() : '?PAGEN_1='.$i;
                        ?>
                        <a href="<?=$href?>"><?=$i?></a>
                    <?}?>
                </div>
            <?}?>
        </div>
        <?
    }


} else {
    echo "Инфоблок не найден.";
}
?>
<style>
    .element_item{
        font-size: 14px;
        margin-bottom: 20px;
    }
    .pagination{
        display: flex;
        margin-bottom: 20px;
    }
    .pag_item{
        padding: 0 5px;
    }
</style>
<?


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>