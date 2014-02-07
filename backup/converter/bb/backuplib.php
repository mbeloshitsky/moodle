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
 * Provides {@link bb_export_converter} class
 *
 * @package    core
 * @subpackage backup-convert
 * @copyright  2011 Darko Miletic <dmiletic@moodlerooms.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/backup/converter/convertlib.php');

class bb_export_converter extends base_converter {
    static public function get_deps() {
        global $CFG;
        require_once($CFG->dirroot . '/backup/util/settings/setting_dependency.class.php');
        return array(
            'users'   => setting_dependency::DISABLED_VALUE,
            'filters' => setting_dependency::DISABLED_VALUE,
            'blocks'  => setting_dependency::DISABLED_VALUE
        );

    }
    protected function execute() {

    }
    public static function description() {

        return array(
            'from'  => backup::FORMAT_MOODLE,
            'to'    => backup::FORMAT_BB,
            'cost'  => 10
        );
    }

}


class bb_store_backup_file extends backup_execution_step {

    protected function define_execution() {

        // Get basepath
        $basepath = $this->get_basepath();

        // Calculate the zip fullpath (in OS temp area it's always backup.imscc)
        $zipfile = $basepath . '/backup.bb';

        // Perform storage and return it (TODO: shouldn't be array but proper result object)
        // Let's send the file to file storage, everything already defined
        // First of all, get some information from the backup_controller to help us decide
        list($dinfo, $cinfo, $sinfo) = backup_controller_dbops::get_moodle_backup_information($this->get_backupid());

        // Extract useful information to decide
        $file      = $sinfo['filename']->value;
        $filename  = basename($file,'.'.pathinfo($file, PATHINFO_EXTENSION)).'.zip';        // Backup filename
        $userid    = $dinfo[0]->userid;                // User->id executing the backup
        $id        = $dinfo[0]->id;                    // Id of activity/section/course (depends of type)
        $courseid  = $dinfo[0]->courseid;              // Id of the course

        $ctxid     = context_user::instance($userid)->id;
        $component = 'user';
        $filearea  = 'backup';
        $itemid    = 0;
        $fs = get_file_storage();
        $fr = array(
            'contextid'   => $ctxid,
            'component'   => $component,
            'filearea'    => $filearea,
            'itemid'      => $itemid,
            'filepath'    => '/',
            'filename'    => $filename,
            'userid'      => $userid,
            'timecreated' => time(),
            'timemodified'=> time());
        // If file already exists, delete if before
        // creating it again. This is BC behaviour - copy()
        // overwrites by default
        if ($fs->file_exists($fr['contextid'], $fr['component'], $fr['filearea'], $fr['itemid'], $fr['filepath'], $fr['filename'])) {
            $pathnamehash = $fs->get_pathname_hash($fr['contextid'], $fr['component'], $fr['filearea'], $fr['itemid'], $fr['filepath'], $fr['filename']);
            $sf = $fs->get_file_by_hash($pathnamehash);
            $sf->delete();
        }

        return array('backup_destination' => $fs->create_file_from_pathname($fr, $zipfile));
    }
}

class bb_zip_contents extends backup_execution_step {

    protected function define_execution() {

        // Get basepath
        $basepath = $this->get_basepath();

        // Get the list of files in directory
        $filestemp = get_directory_list($basepath, '', false, true, true);
        $files = array();
        foreach ($filestemp as $file) {
            // Add zip paths and fs paths to all them
            $files[$file] = $basepath . '/' . $file;
        }

        // Calculate the zip fullpath (in OS temp area it's always backup.mbz)
        $zipfile = $basepath . '/backup.bb';

        // Get the zip packer
        $zippacker = get_file_packer('application/zip');

        // Zip files
        $zippacker->archive_to_pathname($files, $zipfile);
    }
}

class bb_backup_convert extends backup_execution_step {

    protected function define_execution() {
        global $CFG;
        // Get basepath
        $basepath = $this->get_basepath();

        $tempdir = $CFG->dataroot . '/temp/backup/' . uniqid('', true);

        if (mkdir($tempdir, 0777, true)) {

            bb_convert_moodle2($basepath, $tempdir);
            //Switch the directories
            if (empty($CFG->keeptempdirectoriesonbackup)) {
                fulldelete($basepath);
            } else {
                if (!rename($basepath, $basepath  . '_moodle2_source')) {
                    throw new backup_task_exception('failed_rename_source_tempdir');
                }
            }

            if (!rename($tempdir, $basepath)) {
                throw new backup_task_exception('failed_move_converted_into_place');
            }

        }
    }
}

function bb_convert_moodle2 ($sourcepath, $destpath) {

    function sdir( $path='.', $mask='*', $nocache=0 ){
        static $dir = array(); // cache result in memory
        if ( !isset($dir[$path]) || $nocache) {
            $dir[$path] = scandir($path);
        }
        foreach ($dir[$path] as $i=>$entry) {
            if ($entry!='.' && $entry!='..' && fnmatch($mask, $entry) ) {
                $sdir[] = $entry;
            }
        }
        return ($sdir);
    }

    $xmlfiles = sdir($sourcepath, '*.xml');

    // ********************************************************************************
    // Алгоритм конвертера, точнее его часть, написанная на PHP
    // прост. Мы читаем xml-файлы из бекапа мудла, а затем применяем
    // к ним XSLT-трансформацию.
    // ********************************************************************************
    $xmlFileName = $sourcepath.DIRECTORY_SEPARATOR."moodle_backup.xml";
    $xml = new DOMDocument();
    $xml->load($xmlFileName);

    $filesNode = $xml->createElement('files');

    foreach ($xmlfiles as $xmlfn) {
        if ($xmlfn == 'moodle_backup.xml')
            continue;
        $xmlf = new DOMDocument;
        $xmlf->load($sourcepath.DIRECTORY_SEPARATOR.$xmlfn);
        $fileNode = $xml->createElement(basename($xmlfn, '.xml'));
        $nodeToAppend = $xml->importNode($xmlf->documentElement, true);
        $fileNode->appendChild($nodeToAppend);
        $filesNode->appendChild($fileNode);
    }
    $xml->appendChild($filesNode);
    //$xml->save('debug_input.xml');

    $xslFileName = dirname(__FILE__)."/moodle2bb91_lite.xsl";
    $xsl = new DOMDocument;
    $xsl->load($xslFileName);
    $proc = new XSLTProcessor();
    $proc->importStyleSheet($xsl); // attach the xsl rules
    // Выполним трансформацию
    $result_string = $proc->transformToXML($xml);
    //file_put_contents('debug.xml', $result_string);
    $result = new DOMDocument;
    $result->loadXML($result_string);


    // Встроим картинки куда нужно

    function embedImages($doc, $selector, $sourcepath) {
        //$xpath = new DOMXpath($doc);
        $elements = $doc->getElementsByTagName($selector);

        if (!is_null($elements)) {
            foreach ($elements as $element) {
                $files = array();
                $textElement = null;
                foreach($element->childNodes as $child) {
                    if ($child->nodeName == 'file') {
                        $files[$child->getAttribute('name')]=$child;
                    }
                    if ($child->nodeName == 'TEXT') {
                        $textElement = $child;
                    }
                }
                $textElement->nodeValue = preg_replace_callback('/src="@@PLUGINFILE@@\/(\S+)\"/', function ($match) use ($files, $selector, $sourcepath) {
                    $fileElement = $files[urldecode($match[1])];
                    if (is_null($fileElement)) {
                        echo "WARNING: No file ".urldecode($match[1])." (".$selector.")\n";
                        return $match[0];
                    }
                    $hash = $fileElement->getAttribute('hash');
                    $filename = $sourcepath.DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR.substr($hash,0,2).DIRECTORY_SEPARATOR.$hash;
                    $fp = fopen($filename,"rb", 0);
                    $base64 = chunk_split(base64_encode(fread($fp, filesize($filename))));
                    fclose($fp);
                    return 'src="data:'.$fileElement->getAttribute('mime').';base64,'.$base64.'"';
                }, $textElement->textContent);
            }
        } else {
            echo "XPATH selector fail while embedding images";
        }
    }

    embedImages($result, 'ANSWER', $sourcepath);
    embedImages($result, 'CHOICE', $sourcepath);
    embedImages($result, 'BODY',   $sourcepath);

    // Создадим необходимые файлы

    foreach ($result->getElementsByTagName('filestocreate')->item(0)->childNodes as $fileNode) {
        if ($fileNode->nodeName == '#text')
            continue;
        $outxml = new DOMDocument("1.0", "utf-8");
        foreach ($fileNode->childNodes as $child) {
            $childToAppend = $outxml->importNode($child, true);
            $outxml->appendChild($childToAppend);
        }
        $outxml->save($destpath.DIRECTORY_SEPARATOR.$fileNode->getAttribute('name'));
    }

    // Запишем манифест
    $filesToCreate = $result->getElementsByTagName('filestocreate')->item(0);
    $filesToCreate->parentNode->removeChild($filesToCreate);
    $filesToCopy = $result->getElementsByTagName('filestocopy')->item(0);
    $filesToCopy->parentNode->removeChild($filesToCopy);
    $manifest = $result->getElementsByTagName('manifest')->item(0);
    $manifestXml = new DOMDocument("1.0", "utf-8");
    $manifestToAppend = $manifestXml->importNode($manifest, true);
    $manifestXml->appendChild($manifestToAppend);
    $manifestXml->save($destpath.DIRECTORY_SEPARATOR."imsmanifest.xml");
}

