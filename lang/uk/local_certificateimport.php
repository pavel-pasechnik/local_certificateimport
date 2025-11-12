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
$string['result:summary'] = 'Успішно імпортовано {$a->imported} з {$a->total} рядків.';
$string['result:table:user'] = 'Користувач';
$string['result:table:code'] = 'Код';
$string['result:table:status'] = 'Статус';
$string['result:status:imported'] = 'Імпортовано';
$string['result:status:filemissing'] = 'Файл не знайдено';
$string['result:status:error'] = 'Помилка';
$string['result:message:newissue'] = 'Створено нову видачу та збережено PDF.';
$string['result:message:updatedissue'] = 'Існуючу видачу оновлено новим PDF.';
$string['result:message:filemissing'] = 'PDF «{$a}» не знайдено у завантаженому ZIP.';

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
$string['error:unexpected'] = 'Неочікувана помилка: {$a}';

$string['status:available'] = 'Доступний';
$string['status:unavailable'] = 'Недоступний';
$string['status:unavailable:details'] = 'Імпорт вимкнено, доки не буде встановлено та увімкнено офіційний плагін Certificate (tool_certificate) і його таблиці в базі даних.';
$string['error:notemplates'] = 'Не знайдено жодного шаблону сертифіката. Створіть його в tool_certificate перед імпортом.';

$string['privacy:metadata'] = 'Плагін не зберігає персональні дані поза стандартними таблицями tool_certificate.';
$string['certificateimport:import'] = 'Імпортувати PDF сертифікати';
