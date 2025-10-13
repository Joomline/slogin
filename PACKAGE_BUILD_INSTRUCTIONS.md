# Инструкции по созданию пакета установки SLogin для Joomla

## Обзор изменений

Файл манифеста `pkg_slogin.xml` был обновлен для создания единого архива пакета установки Joomla. Теперь манифест ссылается на папки с исходным кодом вместо отдельных ZIP-файлов.

## Структура пакета

Для создания единого архива пакета необходимо включить следующие файлы и папки:

### Корневые файлы
- `pkg_slogin.xml` - главный манифест пакета
- `script.php` - скрипт установки пакета

### Папки компонентов
- `com_slogin/` - основной компонент SLogin
- `mod_slogin/` - модуль SLogin
- `libraries/slogin/` - библиотека SLogin OAuth
- `libraries/amcharts/` - библиотека AmCharts

### Плагины
- `plugins/authentication/slogin/` - плагин аутентификации
- `plugins/user/plg_slogin/` - пользовательский плагин
- `plugins/slogin_integration/profile/` - плагин интеграции профиля

### Плагины провайдеров OAuth
- `plugins/slogin_auth/facebook/`
- `plugins/slogin_auth/bitbucket/`
- `plugins/slogin_auth/github/`
- `plugins/slogin_auth/google/`
- `plugins/slogin_auth/mail/`
- `plugins/slogin_auth/odnoklassniki/`
- `plugins/slogin_auth/twitter/`
- `plugins/slogin_auth/vkontakte/`
- `plugins/slogin_auth/yandex/`
- `plugins/slogin_auth/linkedin/`
- `plugins/slogin_auth/ulogin/`
- `plugins/slogin_auth/live/`
- `plugins/slogin_auth/yahoo/`
- `plugins/slogin_auth/wordpress/`
- `plugins/slogin_auth/instagram/`
- `plugins/slogin_auth/twitch/`
- `plugins/slogin_auth/telegram/`

### Языковые файлы пакета
- `language/en-GB/en-GB.pkg_slogin.sys.ini`
- `language/ru-RU/ru-RU.pkg_slogin.sys.ini`

## Команда для создания архива

Для создания единого архива пакета выполните следующую команду из корневой папки проекта:

```bash
zip -r pkg_slogin.zip \
  pkg_slogin.xml \
  script.php \
  com_slogin/ \
  mod_slogin/ \
  libraries/slogin/ \
  libraries/amcharts/ \
  plugins/authentication/slogin/ \
  plugins/user/plg_slogin/ \
  plugins/slogin_integration/profile/ \
  plugins/slogin_auth/ \
  language/
```

Или используйте PowerShell в Windows:

```powershell
Compress-Archive -Path pkg_slogin.xml, script.php, com_slogin, mod_slogin, libraries, plugins, language -DestinationPath pkg_slogin.zip -Force
```

## Результат

После выполнения команды будет создан файл `pkg_slogin.zip`, который можно установить в Joomla через менеджер расширений как единый пакет.

## Преимущества нового подхода

1. **Единый архив**: Все компоненты пакета находятся в одном архиве
2. **Упрощенная установка**: Не нужно устанавливать отдельные ZIP-файлы
3. **Лучшая организация**: Исходный код остается в читаемой структуре папок
4. **Поддержка языков**: Языковые файлы пакета включены в манифест
5. **Совместимость**: Полная совместимость с системой пакетов Joomla 5.x

## Примечания

- Убедитесь, что все папки содержат необходимые файлы манифестов (XML)
- Проверьте права доступа к файлам перед созданием архива
- Тестируйте установку пакета на чистой установке Joomla
