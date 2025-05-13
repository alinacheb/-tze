<?
namespace Tz\Eksmo;

use Bitrix\Highloadblock\HighloadBlockTable;

class Create
{

    private $siteId = 's1';

    private $hlBlockId;
    private $hlBlockName = 'HlTz';
    private $hlBlockCode = 'hl_tz';
    private $arHlXmlId = [];

    private $iblockName = 'ТЗ';
    private $iblockCode = 'tz';
    private $iblockId;

    private $arPropListId = [];

    private $arErrors = [];


    public function __construct()
    {
        \Bitrix\Main\Loader::includeModule('iblock');
        \Bitrix\Main\Loader::includeModule('highloadblock');
    }

    public function createEntities()
    {
        $this->createHighloadBlock();
        $this->createInfoblock();
        if($this->hlBlockId > 0 && $this->iblockId){
            $this->createProperty([
                'NAME' => 'Текстовое',
                'CODE' => 'TEXT',
                'PROPERTY_TYPE' => 'S'
            ]);
            $this->createProperty([
                'NAME' => 'Списочное',
                'CODE' => 'LIST',
                'PROPERTY_TYPE' => 'L',
                'MULTIPLE' => 'Y',
                'VALUES' => [['VALUE' => 'Простой'], ['VALUE' => 'Сложный'], ['VALUE' => 'Красивый'], ['VALUE' => 'Ненужный']]
            ]);
            $this->createProperty([
                'NAME' => 'Справочник',
                'CODE' => 'HL_LIST',
                'PROPERTY_TYPE' => 'S',
                'USER_TYPE' => 'directory',
                'USER_TYPE_SETTINGS' => [
                    'size' => '1',
                    'width' => '0',
                    'group' => 'N',
                    'multiple' => 'N',
                    'TABLE_NAME' => $this->hlBlockCode
                ]
            ]);
        }
    }

    public function full(int $count)
    {
        if(!$this->arErrors){
            for ($i = 1; $i <= $count; $i++) {
                $name = 'Элемент '.$i;
                $textValue = 'Текстовое '. $i;
                $this->createInfoblockElement($name, $textValue);
            }
        }
        if($this->arErrors){
            echo 'ERRORS:<br />';
            echo '<pre>';
            print_r($this->arErrors);
            echo '</pre>';
        }
    }

    private function createHighloadBlock()
    {
        $data = [
            'NAME' => $this->hlBlockName,
            'TABLE_NAME' => $this->hlBlockCode,
        ];
        $res = HighloadBlockTable::add($data);
        if ($res->isSuccess()) {
            $this->hlBlockId = $res->getId();
            $userField = new \CUserTypeEntity();
            $userField->Add([
                'ENTITY_ID' => 'HLBLOCK_' . $this->hlBlockId,
                'FIELD_NAME' => 'UF_XML_ID',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => 'UF_XML_ID',
                'SORT' => 100,
            ]);
            $userField->Add([
                'ENTITY_ID' => 'HLBLOCK_' . $this->hlBlockId,
                'FIELD_NAME' => 'UF_NAME',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => 'UF_NAME',
                'SORT' => 200,
            ]);
            $entity = HighloadBlockTable::compileEntity($this->hlBlockId);
            $dataClass = $entity->getDataClass();
            foreach (['Красный', 'Желтый', 'Синий', 'Черный', 'Белый'] as $k => $color) {
                $xmlId = 'color'.($k+1);
                $r = $dataClass::add(['UF_NAME' => $color, 'UF_XML_ID' => $xmlId]);
                if ($r->isSuccess()) {
                    $this->arHlXmlId[] = $xmlId;
                }
            }
        } else {
            $this->arErrors['hl'] = $res->getErrorMessages();
        }
    }

    private function createInfoblock()
    {
        $obBlocktype = new \CIBlockType;
        $iblockTypeId = $obBlocktype->Add([
            'ID' => 'tztype',
            'SECTIONS' => 'Y',
            'LANG'=> [
                'ru' => [
                    'NAME' => 'Для ТЗ',
                    'SECTION_NAME' => 'Раздел',
                    'ELEMENT_NAME' => 'Элемент'
                ]
            ]
        ]);
        if($iblockTypeId) {
            $ib = new \CIBlock;
            $iblockFields = [
                'NAME' => $this->iblockName,
                'CODE' => $this->iblockCode,
                'IBLOCK_TYPE_ID' => $iblockTypeId,
                'SITE_ID' => [$this->siteId],
                'VERSION' => '2',
                'GROUP_ID' => ['2' => 'R', '3' => 'R'],
            ];
            $res = $ib->add($iblockFields);
            if ($res) {
                $this->iblockId = $res;
            } else {
                $this->arErrors['ib'] = $ib->LAST_ERROR;
            }
        } else {
            $this->arErrors['it'] = $obBlocktype->LAST_ERROR;
        }
    }

    private function createProperty($arData)
    {
        $propertyFields = $arData;
        $propertyFields['IBLOCK_ID'] = $this->iblockId;
        $ibp = new \CIBlockProperty;
        $propertyId = $ibp->add($propertyFields);
        if ($propertyId) {
            if($arData['PROPERTY_TYPE'] == 'L'){
                $rsEnum = \Bitrix\Iblock\PropertyEnumerationTable::getList([
                    'filter' => ['PROPERTY_ID' => $propertyId]
                ]);
                while($enum = $rsEnum->fetch()) {
                    $this->arPropListId[] = $enum['ID'];
                }
            }
        }
        else {
            $this->arErrors['pr'] = $ibp->LAST_ERROR;
        }
    }

    public function createInfoblockElement($name, $textValue)
    {
        $el = new \CIBlockElement();
        $fields = [
            'IBLOCK_ID' => $this->iblockId,
            'NAME' => $name,
            'PROPERTY_VALUES' => [
                'TEXT' => $textValue,
                'LIST' => array_intersect_key($this->arPropListId, array_flip(array_rand($this->arPropListId, rand(2, 3)))),
                'HL_LIST' => $this->arHlXmlId[array_rand($this->arHlXmlId)]
            ],
        ];
        $result = $el->add($fields);
        if (!$result) {
            $this->arErrors['el'] = $el->LAST_ERROR;
        }
    }

}
