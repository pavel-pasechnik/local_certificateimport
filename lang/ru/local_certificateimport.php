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
 * Russian language strings for local_certificateimport.
 *
 * @package   local_certificateimport
 * @copyright 2024 Pavel
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Импорт PDF-сертификатов';
$string['pagetitle'] = 'Импорт CSV/ZIP сертификатов';
$string['page:instructions'] = 'Загрузите CSV в формате <code>userid,templateid,code,filename,timecreated</code> и ZIP с PDF-файлами, где имена совпадают с колонкой <code>filename</code>. После импорта проверяйте выдачи через <code>tool/certificate/index.php</code>. Ниже можно скачать готовый шаблон CSV.';
$string['page:csvtemplate'] = 'Скачать шаблон CSV';

$string['form:csvfile'] = 'CSV-файл';
$string['form:csvfile_help'] = 'CSV должен содержать столбцы: userid, templateid, code, filename, timecreated. Колонка timecreated может содержать UNIX-метку времени или обычную дату (YYYY-MM-DD, DD.MM.YYYY и т. п.).';
$string['form:zipfile'] = 'ZIP-архив с PDF';
$string['form:zipfile_help'] = 'Имена PDF-файлов должны совпадать со значением в колонке <code>filename</code>.';
$string['form:submit'] = 'Импортировать сертификаты';
$string['form:error:required'] = 'Это обязательное поле.';

$string['report:title'] = 'Отчёт об импорте';
$string['result:none'] = 'Выполните импорт, чтобы увидеть отчёт.';
$string['result:summary'] = 'Успешно импортировано {$a->imported} из {$a->total} строк.';
$string['result:table:user'] = 'Пользователь';
$string['result:table:code'] = 'Код';
$string['result:table:status'] = 'Статус';
$string['result:status:imported'] = 'Импортировано';
$string['result:status:filemissing'] = 'Файл не найден';
$string['result:status:error'] = 'Ошибка';
$string['result:message:newissue'] = 'Создана новая выдача и сохранён PDF.';
$string['result:message:updatedissue'] = 'Существующая выдача обновлена новым PDF.';
$string['result:message:filemissing'] = 'PDF «{$a}» не найден в загруженном ZIP.';

$string['error:missingfiles'] = 'Нужно загрузить и CSV, и ZIP.';
$string['error:csvread'] = 'Не удалось прочитать CSV-файл.';
$string['error:csvcolumns'] = 'Строка {$a}: неверный формат (ожидается userid,templateid,code,filename,timecreated).';
$string['error:csvempty'] = 'CSV-файл не содержит данных.';
$string['error:usernotfound'] = 'Пользователь с ID {$a} не найден.';
$string['error:templatenotfound'] = 'Шаблон сертификата с ID {$a} не найден.';
$string['error:codeempty'] = 'Код сертификата не может быть пустым.';
$string['error:filename'] = 'Не удалось определить имя файла для значения «{$a}».';
$string['error:pdfextension'] = 'Можно импортировать только PDF (значение «{$a}»).';
$string['error:zipopen'] = 'Не удалось открыть ZIP-архив (код ошибки {$a}).';
$string['error:zipextract'] = 'Не удалось извлечь файлы из ZIP-архива.';
$string['error:unexpected'] = 'Непредвиденная ошибка: {$a}';

$string['status:available'] = 'Доступен';
$string['status:unavailable'] = 'Недоступен';
$string['status:unavailable:details'] = 'Импорт отключён, пока не будет установлен и включён официальный плагин Certificate (tool_certificate) и его таблицы в базе данных.';

$string['privacy:metadata'] = 'Плагин не хранит персональные данные вне стандартных таблиц tool_certificate.';
$string['certificateimport:import'] = 'Импортировать PDF сертификаты';
