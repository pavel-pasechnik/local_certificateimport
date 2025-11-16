<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Converts uploaded certificate PDFs into JPEG backgrounds.
 *
 * @package   local_certificateimport
 * @copyright 2025
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_certificateimport\local;

use moodle_exception;

/**
 * Lightweight PDF → JPEG converter.
 *
 * Uses Imagick when available and falls back to the CLI convert utility if the extension
 * is not enabled. Throws a moodle_exception when no converter is available.
 */
class converter {
    /** @var int */
    protected $dpi;

    /**
     * Converter constructor.
     *
     * @param int $dpi
     */
    public function __construct(int $dpi = 150) {
        $this->dpi = $dpi;
    }

    /**
     * Converts the first page of a PDF file into a JPEG and returns a temporary path.
     *
     * @param string $pdfpath
     * @return string
     * @throws moodle_exception
     */
    public function convert(string $pdfpath): string {
        if (!is_readable($pdfpath)) {
            throw new moodle_exception('error:csvread', 'local_certificateimport');
        }

        if (extension_loaded('imagick')) {
            return $this->convert_with_imagick($pdfpath);
        }

        if ($this->has_cli_convert()) {
            return $this->convert_with_cli($pdfpath);
        }

        if ($this->has_pdftoppm()) {
            return $this->convert_with_pdftoppm($pdfpath);
        }

        if ($this->has_ghostscript()) {
            return $this->convert_with_ghostscript($pdfpath);
        }

        throw new moodle_exception('error:convertermissing', 'local_certificateimport');
    }

    /**
     * Uses the Imagick PHP extension for conversion.
     *
     * @param string $pdfpath
     * @return string
     * @throws moodle_exception
     */
    protected function convert_with_imagick(string $pdfpath): string {
        try {
            $imagick = new \Imagick();
            $imagick->setResolution($this->dpi, $this->dpi);
            $imagick->readImage($pdfpath . '[0]');
            $imagick->setImageFormat('jpeg');
            $imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
            $imagick->setImageCompressionQuality(90);
            $imagick->setBackgroundColor('white');
            $imagick->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);

            $target = $this->generate_temp_jpeg();
            $imagick->writeImage($target);
            $imagick->clear();
            $imagick->destroy();

            return $target;
        } catch (\ImagickException $exception) {
            throw new moodle_exception('error:converterimagick', 'local_certificateimport', '', $exception->getMessage());
        }
    }

    /**
     * Uses the ImageMagick CLI utility when the PHP extension is missing.
     *
     * @param string $pdfpath
     * @return string
     * @throws moodle_exception
     */
    protected function convert_with_cli(string $pdfpath): string {
        $target = $this->generate_temp_jpeg();
        $escapedsource = escapeshellarg($pdfpath . '[0]');
        $escapedtarget = escapeshellarg($target);
        $command = implode(' ', [
            escapeshellcmd($this->get_convert_binary()),
            '-density ' . (int)$this->dpi,
            $escapedsource,
            '-quality 90',
            $escapedtarget,
        ]);

        exec($command, $output, $code);
        if ($code !== 0 || !file_exists($target)) {
            @unlink($target);
            throw new moodle_exception('error:convertercli', 'local_certificateimport', '', $code);
        }

        return $target;
    }

    /**
     * Uses the pdftoppm CLI utility when available.
     *
     * @param string $pdfpath
     * @return string
     * @throws moodle_exception
     */
    protected function convert_with_pdftoppm(string $pdfpath): string {
        $target = $this->generate_temp_jpeg();
        $prefix = preg_replace('/\.jpg$/', '', $target);
        $command = implode(' ', [
            escapeshellcmd($this->get_pdftoppm_binary()),
            '-singlefile',
            '-jpeg',
            '-r ' . (int)$this->dpi,
            '-f 1',
            '-l 1',
            escapeshellarg($pdfpath),
            escapeshellarg($prefix),
        ]);

        exec($command, $output, $code);
        if ($code !== 0 || !file_exists($target)) {
            @unlink($target);
            throw new moodle_exception('error:converterpdftoppm', 'local_certificateimport', '', $code);
        }

        return $target;
    }

    /**
     * Uses the Ghostscript CLI utility when available.
     *
     * @param string $pdfpath
     * @return string
     * @throws moodle_exception
     */
    protected function convert_with_ghostscript(string $pdfpath): string {
        $target = $this->generate_temp_jpeg();
        $command = implode(' ', [
            escapeshellcmd($this->get_ghostscript_binary()),
            '-dSAFER',
            '-dBATCH',
            '-dNOPAUSE',
            '-dFirstPage=1',
            '-dLastPage=1',
            '-sDEVICE=jpeg',
            '-dJPEGQ=90',
            '-r' . (int)$this->dpi,
            '-sOutputFile=' . escapeshellarg($target),
            escapeshellarg($pdfpath),
        ]);

        exec($command, $output, $code);
        if ($code !== 0 || !file_exists($target)) {
            @unlink($target);
            throw new moodle_exception('error:convertergs', 'local_certificateimport', '', $code);
        }

        return $target;
    }

    /**
     * Generates a writable temp file path for JPEG output.
     *
     * @return string
     */
    protected function generate_temp_jpeg(): string {
        $tempdir = make_temp_directory('local_certificateimport');
        return $tempdir . '/' . time() . '_' . random_string(8) . '.jpg';
    }

    /**
     * Checks whether the CLI convert utility is present.
     *
     * @return bool
     */
    protected function has_cli_convert(): bool {
        return !empty($this->get_convert_binary());
    }

    /**
     * Checks whether pdftoppm is available.
     *
     * @return bool
     */
    protected function has_pdftoppm(): bool {
        return !empty($this->get_pdftoppm_binary());
    }

    /**
     * Checks whether Ghostscript is available.
     *
     * @return bool
     */
    protected function has_ghostscript(): bool {
        return !empty($this->get_ghostscript_binary());
    }

    /**
     * Returns the ImageMagick convert binary path when available.
     *
     * @return string
     */
    protected function get_convert_binary(): string {
        $paths = ['/usr/bin/convert', '/usr/local/bin/convert'];
        foreach ($paths as $path) {
            if (is_executable($path)) {
                return $path;
            }
        }

        $output = [];
        $code = 0;
        @exec('command -v convert', $output, $code);
        if ($code === 0 && !empty($output[0])) {
            return trim($output[0]);
        }

        return '';
    }

    /**
     * Returns the pdftoppm binary path when available.
     *
     * @return string
     */
    protected function get_pdftoppm_binary(): string {
        return local_certificateimport_find_pdftoppm_path();
    }

    /**
     * Returns the Ghostscript binary path when available.
     *
     * @return string
     */
    protected function get_ghostscript_binary(): string {
        return local_certificateimport_find_ghostscript_path();
    }
}
