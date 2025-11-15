<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Ukrainian language strings for local_certificateimport.
 *
 * @package   local_certificateimport
 * @copyright 2024 Pavel
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Імпорт PDF-сертифікатів';
$string['pagetitle'] = 'Імпорт CSV/ZIP сертифікатів';
$string['page:instructions'] = 'Оберіть шаблон сертифіката нижче, далі завантажте CSV у форматі <code>userid,filename,timecreated</code> (стовпець <code>timecreated</code> можна залишити порожнім) та ZIP з PDF-файлами, імена яких збігаються зі стовпцем <code>filename</code>. Після імпорту перевіряйте видані сертифікати через <code>tool/certificate/index.php</code>. Нижче доступний готовий шаблон CSV.';
$string['page:csvtemplate'] = 'Завантажити шаблон CSV';

$string['form:template'] = 'Шаблон сертифіката';
$string['form:template_help'] = 'Оберіть шаблон, у який будуть імпортовані сертифікати. Значення з CSV ігнорується.';
$string['form:template:choose'] = 'Оберіть шаблон';
$string['form:csvfile'] = 'Файл CSV';
$string['form:csvfile_help'] = 'CSV повинен містити стовпці: userid, filename, timecreated. Стовпець timecreated можна залишити порожнім або вказати UNIX-мітку часу/дату (YYYY-MM-DD, DD.MM.YYYY тощо).';
$string['form:zipfile'] = 'ZIP-архів з PDF';
$string['form:zipfile_help'] = 'Назви PDF мають збігатися зі значенням у стовпці <code>filename</code>.';
$string['form:submit'] = 'Імпортувати сертифікати';
$string['form:error:required'] = 'Це обовʼязкове поле.';

$string['report:title'] = 'Звіт про імпорт';
$string['result:export'] = 'Експортувати звіт у CSV';
$string['result:export:empty'] = 'Немає останніх результатів імпорту для експорту.';
$string['result:none'] = 'Щоб побачити звіт, виконайте імпорт.';
$string['result:summary'] = '{$a->ready} з {$a->total} рядків конвертовано та додано до черги.';
$string['result:table:user'] = 'Користувач';
$string['result:table:code'] = 'Код';
$string['result:table:status'] = 'Статус';
$string['result:status:imported'] = 'Імпортовано';
$string['result:status:queued'] = 'В черзі';
$string['result:status:registered'] = 'Зареєстровано';
$string['result:status:filemissing'] = 'Файл не знайдено';
$string['result:status:error'] = 'Помилка';
$string['result:message:newissue'] = 'Створено нову видачу та збережено PDF.';
$string['result:message:updatedissue'] = 'Існуючу видачу оновлено новим PDF.';
$string['result:message:filemissing'] = 'PDF «{$a}» не знайдено у завантаженому ZIP.';
$string['result:message:queued'] = 'PDF перетворено у JPEG та поставлено в чергу на реєстрацію.';

$string['issue:status:queued'] = 'В черзі';
$string['issue:status:active'] = 'Активний';
$string['issue:status:revoked'] = 'Відкликаний';
$string['issue:status:missing'] = 'Відсутній';

$string['error:missingfiles'] = 'Потрібно завантажити і CSV, і ZIP.';
$string['error:csvread'] = 'Не вдалося прочитати файл CSV.';
$string['error:csvcolumns'] = 'Рядок {$a}: неправильний формат (очікується userid,filename,timecreated). Перші два стовпці обовʼязкові.';
$string['error:csvempty'] = 'CSV-файл не містить даних.';
$string['error:usernotfound'] = 'Користувача з ID {$a} не знайдено.';
$string['error:templatenotfound'] = 'Шаблон сертифіката з ID {$a} не знайдено.';
$string['error:filename'] = 'Не вдалося визначити назву файлу для значення «{$a}».';
$string['error:pdfextension'] = 'Можна імпортувати лише PDF (значення «{$a}»).';
$string['error:zipopen'] = 'Не вдалося відкрити ZIP-архів (код помилки {$a}).';
$string['error:zipextract'] = 'Не вдалося розпакувати файли з ZIP-архіву.';
$string['error:batchinprogress'] = 'Цей пакет вже обробляється або завершений.';
$string['error:batchreadyempty'] = 'У пакеті немає записів, що очікують реєстрації.';
$string['error:backgroundmissing'] = 'Не знайдено конвертований фон для «{$a}».';
$string['error:convertermissing'] = 'Для конвертації PDF потрібен Imagick або утиліта ImageMagick (convert). Встановіть один з варіантів.';
$string['error:converterimagick'] = 'Imagick не зміг конвертувати PDF: {$a}';
$string['error:convertercli'] = 'ImageMagick не зміг конвертувати PDF (код {$a}).';
$string['error:unexpected'] = 'Неочікувана помилка: {$a}';

$string['status:available'] = 'Доступний';
$string['status:unavailable'] = 'Недоступний';
$string['status:unavailable:details'] = 'Імпорт вимкнено, доки не буде встановлено та увімкнено офіційний плагін Certificate (tool_certificate) і його таблиці в базі даних.';
$string['error:notemplates'] = 'Не знайдено жодного шаблону сертифіката. Створіть його в tool_certificate перед імпортом.';

$string['batch:list:title'] = 'Пакети імпорту';
$string['batch:none'] = 'Черга імпорту поки порожня.';
$string['batch:table:template'] = 'Шаблон';
$string['batch:table:created'] = 'Створено';
$string['batch:table:status'] = 'Статус';
$string['batch:table:queued'] = 'В черзі / Всього';
$string['batch:table:registered'] = 'Видано';
$string['batch:table:errors'] = 'Помилки';
$string['batch:table:actions'] = 'Дії';
$string['batch:register'] = 'Запустити реєстрацію';
$string['batch:register:confirm'] = 'Видати сертифікати з цього пакета через tool_certificate?';
$string['batch:register:success'] = 'Видано {$a->success} сертифікат(ів). Помилок: {$a->errors}.';
$string['batch:status:pending'] = 'В черзі';
$string['batch:status:processing'] = 'Виконується';
$string['batch:status:completed'] = 'Завершено';
$string['batch:status:completed_errors'] = 'Завершено з помилками';
$string['batch:status:failed'] = 'Помилка';

$string['filter:template'] = 'Шаблон сертифіката';
$string['filter:template:any'] = 'Усі шаблони';
$string['filter:status'] = 'Статус';
$string['filter:status:any'] = 'Усі статуси';
$string['filter:user'] = 'Користувач (імʼя або email)';
$string['filter:datefrom'] = 'Імпортовано після';
$string['filter:dateto'] = 'Імпортовано до';
$string['filter:perpage'] = 'На сторінку';
$string['filter:apply'] = 'Застосувати';
$string['filter:reset'] = 'Скинути';

$string['report:issues:title'] = 'Імпортовані сертифікати';
$string['report:issues:description'] = 'Переглядайте всі сертифікати, імпортовані через плагін, фільтруйте їх за шаблоном/статусом/датою, експортуйте у CSV та перестворюйте відкликані записи.';
$string['report:menu'] = 'Звіт про імпортовані сертифікати';
$string['report:col:number'] = '№';
$string['report:col:certificate'] = 'Шаблон';
$string['report:col:user'] = 'Користувач';
$string['report:col:imported'] = 'Імпортовано';
$string['report:col:issued'] = 'Видано';
$string['report:col:status'] = 'Статус';
$string['report:col:code'] = 'Код сертифіката';
$string['report:reissue:selected'] = 'Перестворити вибрані сертифікати';
$string['report:reissue:none'] = 'Виберіть хоча б один відкликаний сертифікат для перестворення.';
$string['report:reissue:success'] = 'Перестворено {$a->success} сертифікат(ів).';
$string['report:reissue:errors'] = 'Не вдалося перестворити {$a->errors} сертифікат(ів).';

$string['privacy:metadata'] = 'Плагін не зберігає персональні дані поза стандартними таблицями tool_certificate.';
$string['certificateimport:import'] = 'Імпортувати PDF сертифікати';
