<?php

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

class slava_newmodule extends CModule
{
    //region Vars&Const
    var $MODULE_ID = "slava.newmodule";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;

    protected $tables = [
        'Newmodule\Table', //массив для таблиц, устанавливаемых модулем. Соблюдать формат и пространство имен.
    ];
    var $errors; //массив ошибок для вывода

    //endregion Vars&Const
    function __construct()
    {
        global $APPLICATION;
        $arModuleVersion = array();
        include(dirname(__FILE__) . '/version.php');

        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];

        $this->MODULE_NAME = Loc::getMessage('MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('MODULE_DESCRIPTION');
        $this->PARTNER_NAME = Loc::getMessage('PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage('PARTNER_URI');

    }

    //region Установка модуля
    protected function InstallCheck()
    {
        $elements = [
            Loc::getMEssage('INSTALL_DB') => $this->InstallDB(),
            Loc::getMEssage('INSTALL_IBLOCKS') => $this->InstalIBlocks(),
            Loc::getMEssage('INSTALL_EVENTS') => $this->InstallEvent(),
            Loc::getMEssage('INSTALL_HlBLocks') => $this->InstallHlBlocks(),
            Loc::getMEssage('INSTALL_FIlES') => $this->InstallFiles(),
            Loc::getMEssage('INSTALL_DIRECTORIES') => $this->InstallDirectories()
        ];
        foreach ($elements as $element => $action) {
            if (!$action)
                $this->errors = [
                    $element
                ];
        }
        return $this->errors;
    }

    function DoInstall()
    {
        global $APPLICATION;
        ModuleManager::registerModule($this->MODULE_ID);
        if (!empty($this->InstallCheck())) {
            $errors = implode(',<br>', $this->errors);
            $APPLICATION->ThrowException(Loc::getMessage('INSTALL_ERROR') . $errors);
        }
        $APPLICATION->IncludeAdminFile(
            "Проверка установки",
            $this->getPath() . '/install/step1.php'
        );
    }

    function DoUnInstall()
    {
        global $APPLICATION;
        $error = false;
    }
    //endregion Установка
    //region База данных
    function InstallDB()
    {
        foreach ($this->TableNameCheck() as $table) {
            $table->createDbTable();
        }
        if (!empty($this->TableNameCheck())) {
            return false;
        } else {
            return true;
        }
    }

    function UnInstallDB()
    {
        if (empty($this->TableNameCheck())) {
            foreach ($this->TablesToEntity() as $entity) {
                //TODO удаление базы данных
            }
        }
    }

    protected function TablesToEntity()
    {
        $entities = [];
        Loader::includeModule($this->MODULE_ID);
        foreach ($this->tables as $table) {
            $entities = [
                $table::getEntity()
            ];
        }
        return $entities;
    }
    protected function TableNameCheck()
    {
        $tablesToCreate = is_array();
        foreach ($this->TablesToEntity() as $entity) {
            $tableName = $entity->getDBTableName();
            if (!$this->connection()->isTableExists($tableName)) {
                $tablesToCreate = [
                    $entity
                ];
            }
        }
        return $tablesToCreate;
    }
    //endregion База данных ло
    //region Инфо-блоки

    function InstalIBlocks()
    {
        //TODO переделать под массив
        Loader::includeModule($this->MODULE_ID);
        return Slava\Migrations\IBlock::up();
    }

    function UnInstalIBlocks()
    {
        Loader::includeModule($this->MODULE_ID);
        return Slava\Migrations\IBlock::down();
    }
    //endregion Инфо-блоки
    //region События
    function InstallEvent()
    {
        $eventManager = EventManager::getInstance();
        /** Регистрация очередного события
         *  Для каждого события заполнить необходимые модуль, событие, класс и метод
         */
        $eventManager->registerEventHandler(
            'module',
            'eventType',
            $this->MODULE_ID,
            'toClass',
            'toMethod'
        );
    }

    function UnInstallEvent()
    {
        $eventManager = EventManager::getInstance();
        /** Удаление зарегистрированных событий
         *  Для каждого события заполнить необходимые модуль, событие, класс и метод
         */
        $eventManager->unRegisterEventhandler(
            'module',
            'eventType',
            $this->MODULE_ID,
            'toClass',
            'toMethod'
        );
    }
    //endregion События
    //region Highload-блоки
    function InstallHlBlocks()
    {
        Loader::includeModule($this->MODULE_ID);
        return Slava\Migrations\IBlock::up();
    }

    function UnInstallHlBlocks()
    {
        Loader::includeModule($this->MODULE_ID);
        return Slava\Migrations\IBlock::down();
    }
    //endregion Highload-блоки
    //region Файлы и Папки
    function InstallFiles()
    {
        copyDirFiles(); // для каждого файла своя функция
    }

    function UnInstallFiles()
    {
        $files = []; //массив для всех файлов, которые необходимо удалить
        foreach ($files as $file) {
            IO\File::deleteFile($file);
        }
    }

    function InstallDirectories()
    {
        $folders = [];
        foreach ($folders as $folder) {
            copyDirDiles(
                __DIR__ . $folder,
                Application::getDocumentRoot() . '/local' . $folder,
                true,
                true
            );
        }

    }

    function UnInstallDirectories()
    {
        $folders = [];
        foreach ($folders as $folder) {
            IO\Directory::deleteDirectory($folder);
        }
    }

    //endregion Файлы и Папки
    //region Extra
    protected function getPath($notDocumentRoot = false)
    {
        $path = dirname(__DIR__);
        $path = str_replace("\\", "/", $path);
        return ($notDocumentRoot)
            ? preg_replace("#^(.*)\/(local|bitrix)\/modules#", "/$2/modules", $path)
            : $path;
    }

    protected function isVersionD7()

    {
        return CheckVersion(ModuleManager::getVersion('main'), '14.00.00');
    }

    function request()
    {
        $context = Main\Application::getInstance()->getContext();
        return $context->getRequest();
    }

    function connection()
    {
        return Main\Application::getConnection();
    }
    //endregion Extra
}
