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
$string['page:instructions'] = 'Выберите шаблон сертификата ниже, затем загрузите CSV в формате <code>userid,filename,timecreated</code> (столбец <code>timecreated</code> можно оставить пустым) и ZIP с PDF-файлами, где имена совпадают с колонкой <code>filename</code>. После импорта проверяйте выдачи через <code>tool/certificate/index.php</code>. Ниже можно скачать готовый шаблон CSV.';
$string['page:csvtemplate'] = 'Скачать шаблон CSV';

$string['form:template'] = 'Шаблон сертификата';
$string['form:template_help'] = 'Выберите шаблон, в который будут импортированы сертификаты. Значение из CSV будет проигнорировано.';
$string['form:template:choose'] = 'Выберите шаблон';
$string['form:csvfile'] = 'CSV-файл';
$string['form:csvfile_help'] = 'CSV должен содержать столбцы: userid, filename, timecreated. Столбец timecreated можно оставить пустым или указать UNIX-метку времени либо дату (YYYY-MM-DD, DD.MM.YYYY и т. п.).';
$string['form:zipfile'] = 'ZIP-архив с PDF';
$string['form:zipfile_help'] = 'Имена PDF-файлов должны совпадать со значением в колонке <code>filename</code>.';
$string['form:submit'] = 'Импортировать сертификаты';
$string['form:error:required'] = 'Это обязательное поле.';

$string['report:title'] = 'Отчёт об импорте';
$string['result:export'] = 'Экспорт отчёта в CSV';
$string['result:export:empty'] = 'Нет последних результатов импорта для экспорта.';
$string['result:none'] = 'Выполните импорт, чтобы увидеть отчёт.';
$string['result:summary'] = '{$a->ready} из {$a->total} строк конвертированы и поставлены в очередь.';
$string['result:table:user'] = 'Пользователь';
$string['result:table:code'] = 'Код';
$string['result:table:status'] = 'Статус';
$string['result:status:imported'] = 'Импортировано';
$string['result:status:queued'] = 'В очереди';
$string['result:status:registered'] = 'Зарегистрировано';
$string['result:status:filemissing'] = 'Файл не найден';
$string['result:status:error'] = 'Ошибка';
$string['result:message:newissue'] = 'Создана новая выдача и сохранён PDF.';
$string['result:message:updatedissue'] = 'Существующая выдача обновлена новым PDF.';
$string['result:message:filemissing'] = 'PDF «{$a}» не найден в загруженном ZIP.';
$string['result:message:queued'] = 'PDF конвертирован в JPEG и поставлен в очередь на регистрацию.';

$string['issue:status:queued'] = 'В очереди';
$string['issue:status:active'] = 'Активен';
$string['issue:status:revoked'] = 'Отозван';
$string['issue:status:missing'] = 'Отсутствует';

$string['error:missingfiles'] = 'Нужно загрузить и CSV, и ZIP.';
$string['error:csvread'] = 'Не удалось прочитать CSV-файл.';
$string['error:csvcolumns'] = 'Строка {$a}: неверный формат (ожидается userid,filename,timecreated). Первые два столбца обязательны.';
$string['error:csvempty'] = 'CSV-файл не содержит данных.';
$string['error:usernotfound'] = 'Пользователь с ID {$a} не найден.';
$string['error:templatenotfound'] = 'Шаблон сертификата с ID {$a} не найден.';
$string['error:filename'] = 'Не удалось определить имя файла для значения «{$a}».';
$string['error:pdfextension'] = 'Можно импортировать только PDF (значение «{$a}»).';
$string['error:zipopen'] = 'Не удалось открыть ZIP-архив (код ошибки {$a}).';
$string['error:zipextract'] = 'Не удалось извлечь файлы из ZIP-архива.';
$string['error:batchinprogress'] = 'Этот пакет уже обрабатывается или завершён.';
$string['error:batchreadyempty'] = 'В пакете нет записей, ожидающих регистрации.';
$string['error:backgroundmissing'] = 'Не найден сконвертированный фон для «{$a}».';
$string['error:convertermissing'] = 'Для конвертации PDF необходим Imagick или одна из консольных утилит (convert, pdftoppm, gs). Установите любой из вариантов.';
$string['error:converterimagick'] = 'Imagick не смог конвертировать PDF: {$a}';
$string['error:convertercli'] = 'ImageMagick не смог конвертировать PDF (код {$a}).';
$string['error:converterpdftoppm'] = 'pdftoppm не смог конвертировать PDF (код {$a}).';
$string['error:convertergs'] = 'Ghostscript не смог конвертировать PDF (код {$a}).';
$string['error:unexpected'] = 'Непредвиденная ошибка: {$a}';

$string['status:available'] = 'Доступен';
$string['status:unavailable'] = 'Недоступен';
$string['status:unavailable:details'] = 'Импорт отключён, пока не будет установлен и включён официальный плагин Certificate (tool_certificate) и его таблицы в базе данных.';
$string['error:notemplates'] = 'Не найдено ни одного шаблона сертификата. Создайте его в tool_certificate перед импортом.';

$string['batch:list:title'] = 'Пакеты импорта';
$string['batch:none'] = 'Очередь импорта пока пуста.';
$string['batch:table:template'] = 'Шаблон';
$string['batch:table:created'] = 'Создан';
$string['batch:table:status'] = 'Статус';
$string['batch:table:queued'] = 'В очереди / Всего';
$string['batch:table:registered'] = 'Выпущено';
$string['batch:table:errors'] = 'Ошибок';
$string['batch:table:actions'] = 'Действия';
$string['batch:register'] = 'Запустить регистрацию';
$string['batch:register:confirm'] = 'Выдать сертификаты из этого пакета через tool_certificate?';
$string['batch:register:success'] = 'Выдано {$a->success} сертификатов. Ошибок: {$a->errors}.';
$string['batch:status:pending'] = 'В очереди';
$string['batch:status:processing'] = 'Обрабатывается';
$string['batch:status:completed'] = 'Завершён';
$string['batch:status:completed_errors'] = 'Завершён с ошибками';
$string['batch:status:failed'] = 'Ошибка';

$string['filter:template'] = 'Шаблон сертификата';
$string['filter:template:any'] = 'Все шаблоны';
$string['filter:status'] = 'Статус';
$string['filter:status:any'] = 'Все статусы';
$string['filter:user'] = 'Пользователь (имя или email)';
$string['filter:datefrom'] = 'Импортирован после';
$string['filter:dateto'] = 'Импортирован до';
$string['filter:perpage'] = 'Записей на странице';
$string['filter:apply'] = 'Применить';
$string['filter:reset'] = 'Сбросить';

$string['report:issues:title'] = 'Импортированные сертификаты';
$string['report:issues:description'] = 'Просматривайте все сертификаты, импортированные через плагин, фильтруйте по шаблону/статусу/дате, экспортируйте в CSV и переиздавайте отозванные записи.';
$string['report:menu'] = 'Отчёт по импортированным сертификатам';
$string['report:col:number'] = '№';
$string['report:col:certificate'] = 'Шаблон';
$string['report:col:user'] = 'Пользователь';
$string['report:col:imported'] = 'Импортирован';
$string['report:col:issued'] = 'Выдан';
$string['report:col:status'] = 'Статус';
$string['report:col:code'] = 'Код сертификата';
$string['report:reissue:selected'] = 'Переиздать выбранные сертификаты';
$string['report:reissue:none'] = 'Выберите хотя бы один отозванный сертификат для переиздания.';
$string['report:reissue:success'] = 'Переиздано {$a->success} сертификатов.';
$string['report:reissue:errors'] = 'Не удалось переиздать {$a->errors} сертификатов.';

$string['privacy:metadata'] = 'Плагин не хранит персональные данные вне стандартных таблиц tool_certificate.';
$string['certificateimport:import'] = 'Импортировать PDF сертификаты';
