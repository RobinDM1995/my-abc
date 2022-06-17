<?php

/**
 * @project:   PDFDesigner (concrete5 add-on)
 *
 * @author     Fabian Bitter
 * @copyright  (C) 2016 Fabian Bitter (www.bitter.de)
 * @version    1.2.1
 */

namespace Concrete\Package\PdfDesigner\Src\MakeFont;

defined('C5_EXECUTE') or die("Access Denied.");

use Concrete\Package\PdfDesigner\Src\MakeFont\TTFParser;

class MakeFont
{
    private function getMapDir()
    {
        return DIR_PACKAGES . "/pdf_designer/vendor/setasign/fpdf/makefont/";
    }

    private function LoadMap($enc)
    {
        $file = $this->getMapDir() . '/' . strtolower($enc) . '.map';
        $a = file($file);

        if (empty($a)) {
            return;
        }

        $map = array_fill(0, 256, array('uv' => -1, 'name' => '.notdef'));

        foreach ($a as $line) {
            $e = explode(' ', rtrim($line));
            $c = hexdec(substr($e[0], 1));
            $uv = hexdec(substr($e[1], 2));
            $name = $e[2];
            $map[$c] = array('uv' => $uv, 'name' => $name);
        }

        return $map;
    }

    /**
     * Return information from a TrueType font
     *
     * @param string $file
     * @param boolean $embed
     * @param string $subset
     * @param string $map
     *
     * @return string
     */
    private function GetInfoFromTrueType($file, $embed, $subset, $map)
    {
        $ttf = new TTFParser($file);
        $ttf->Parse();

        if ($embed) {
            if (!$ttf->embeddable) {
                return;
            }

            if ($subset) {
                $chars = array();

                foreach ($map as $v) {
                    if ($v['name'] != '.notdef') {
                        $chars[] = $v['uv'];
                    }
                }

                $ttf->Subset($chars);
                $info['Data'] = $ttf->Build();
            } else {
                $info['Data'] = file_get_contents($file);
            }

            $info['OriginalSize'] = strlen($info['Data']);
        }

        $k = 1000 / $ttf->unitsPerEm;

        $info['FontName'] = $ttf->postScriptName;
        $info['Bold'] = $ttf->bold;
        $info['ItalicAngle'] = $ttf->italicAngle;
        $info['IsFixedPitch'] = $ttf->isFixedPitch;
        $info['Ascender'] = round($k * $ttf->typoAscender);
        $info['Descender'] = round($k * $ttf->typoDescender);
        $info['UnderlineThickness'] = round($k * $ttf->underlineThickness);
        $info['UnderlinePosition'] = round($k * $ttf->underlinePosition);
        $info['FontBBox'] = array(round($k * $ttf->xMin), round($k * $ttf->yMin), round($k * $ttf->xMax), round($k * $ttf->yMax));
        $info['CapHeight'] = round($k * $ttf->capHeight);
        $info['MissingWidth'] = round($k * $ttf->glyphs[0]['w']);

        $widths = array_fill(0, 256, $info['MissingWidth']);

        foreach ($map as $c => $v) {
            if ($v['name'] != '.notdef') {
                if (isset($ttf->chars[$v['uv']])) {
                    $id = $ttf->chars[$v['uv']];
                    $w = $ttf->glyphs[$id]['w'];
                    $widths[$c] = round($k * $w);
                }
            }
        }

        $info['Widths'] = $widths;

        return $info;
    }

    /**
     * Return information from a Type1 font
     *
     * @param string $file
     * @param boolean $embed
     * @param string $map
     *
     * @return mixed
     */
    private function GetInfoFromType1($file, $embed, $map)
    {
        if ($embed) {
            $f = fopen($file, 'rb');

            if (!$f) {
                return false;
            }

            // Read first segment
            $a = unpack('Cmarker/Ctype/Vsize', fread($f, 6));

            if ($a['marker'] != 128) {
                return false;
            }
            
            $size1 = $a['size'];

            $data = fread($f, $size1);

            // Read second segment
            $a = unpack('Cmarker/Ctype/Vsize', fread($f, 6));

            if ($a['marker'] != 128) {
                return false;
            }
            
            $size2 = $a['size'];

            $data .= fread($f, $size2);

            fclose($f);

            $info['Data'] = $data;
            $info['Size1'] = $size1;
            $info['Size2'] = $size2;
        }

        $afm = substr($file, 0, -3) . 'afm';

        if (!file_exists($afm)) {
            return false;
        }
        
        $a = file($afm);

        if (empty($a)) {
            return false;
        }
        
        foreach ($a as $line) {
            $e = explode(' ', rtrim($line));

            if (count($e) < 2) {
                continue;
            }

            $entry = $e[0];

            if ($entry == 'C') {
                $w = $e[4];
                $name = $e[7];
                $cw[$name] = $w;
            } elseif ($entry == 'FontName') {
                $info['FontName'] = $e[1];
            } elseif ($entry == 'Weight') {
                $info['Weight'] = $e[1];
            } elseif ($entry == 'ItalicAngle') {
                $info['ItalicAngle'] = (int) $e[1];
            } elseif ($entry == 'Ascender') {
                $info['Ascender'] = (int) $e[1];
            } elseif ($entry == 'Descender') {
                $info['Descender'] = (int) $e[1];
            } elseif ($entry == 'UnderlineThickness') {
                $info['UnderlineThickness'] = (int) $e[1];
            } elseif ($entry == 'UnderlinePosition') {
                $info['UnderlinePosition'] = (int) $e[1];
            } elseif ($entry == 'IsFixedPitch') {
                $info['IsFixedPitch'] = ($e[1] == 'true');
            } elseif ($entry == 'FontBBox') {
                $info['FontBBox'] = array((int) $e[1], (int) $e[2], (int) $e[3], (int) $e[4]);
            } elseif ($entry == 'CapHeight') {
                $info['CapHeight'] = (int) $e[1];
            } elseif ($entry == 'StdVW') {
                $info['StdVW'] = (int) $e[1];
            }
        }

        if (!isset($info['FontName'])) {
            return false;
        }
        
        if (!isset($info['Ascender'])) {
            $info['Ascender'] = $info['FontBBox'][3];
        }

        if (!isset($info['Descender'])) {
            $info['Descender'] = $info['FontBBox'][1];
        }

        $info['Bold'] = isset($info['Weight']) && preg_match('/bold|black/i', $info['Weight']);

        if (isset($cw['.notdef'])) {
            $info['MissingWidth'] = $cw['.notdef'];
        } else {
            $info['MissingWidth'] = 0;
        }
        $widths = array_fill(0, 256, $info['MissingWidth']);

        foreach ($map as $c => $v) {
            if ($v['name'] != '.notdef') {
                if (isset($cw[$v['name']])) {
                    $widths[$c] = $cw[$v['name']];
                }
            }
        }

        $info['Widths'] = $widths;

        return $info;
    }

    /**
     * @param array $info
     * @return string
     */
    private function MakeFontDescriptor($info)
    {
        // Ascent
        $fd = "array('Ascent'=>" . $info['Ascender'];

        // Descent
        $fd .= ",'Descent'=>" . $info['Descender'];

        // CapHeight
        if (!empty($info['CapHeight'])) {
            $fd .= ",'CapHeight'=>" . $info['CapHeight'];
        } else {
            $fd .= ",'CapHeight'=>" . $info['Ascender'];
        }

        // Flags
        $flags = 0;
        if ($info['IsFixedPitch']) {
            $flags += 1 << 0;
        }

        $flags += 1 << 5;

        if ($info['ItalicAngle'] != 0) {
            $flags += 1 << 6;
        }

        $fd .= ",'Flags'=>" . $flags;

        // FontBBox
        $fbb = $info['FontBBox'];
        $fd .= ",'FontBBox'=>'[" . $fbb[0] . ' ' . $fbb[1] . ' ' . $fbb[2] . ' ' . $fbb[3] . "]'";

        // ItalicAngle
        $fd .= ",'ItalicAngle'=>" . $info['ItalicAngle'];

        // StemV
        if (isset($info['StdVW'])) {
            $stemv = $info['StdVW'];
        } elseif ($info['Bold']) {
            $stemv = 120;
        } else {
            $stemv = 70;
        }

        $fd .= ",'StemV'=>" . $stemv;

        $fd .= ",'MissingWidth'=>" . $info['MissingWidth'] . ')';

        return $fd;
    }

    /**
     * @param array $widths
     * @return string
     */
    private function MakeWidthArray($widths)
    {
        $s = "array(\n\t";

        for ($c = 0; $c <= 255; $c++) {
            if (chr($c) == "'") {
                $s .= "'\\''";
            } elseif (chr($c) == "\\") {
                $s .= "'\\\\'";
            } elseif ($c >= 32 && $c <= 126) {
                $s .= "'" . chr($c) . "'";
            } else {
                $s .= "chr($c)";
            }

            $s .= '=>' . $widths[$c];

            if ($c < 255) {
                $s .= ',';
            }

            if (($c + 1) % 22 == 0) {
                $s .= "\n\t";
            }
        }

        $s .= ')';

        return $s;
    }

    /**
     * @param array $map
     *
     * @return string
     */
    private function MakeFontEncoding($map)
    {
        $ref = $this->LoadMap('cp1252');
        $s = '';
        $last = 0;

        for ($c = 32; $c <= 255; $c++) {
            if ($map[$c]['name'] != $ref[$c]['name']) {
                if ($c != $last + 1) {
                    $s .= $c . ' ';
                }
                $last = $c;
                $s .= '/' . $map[$c]['name'] . ' ';
            }
        }

        return rtrim($s);
    }

    /**
     *
     * @param array $map
     * @return string
     */
    private function MakeUnicodeArray($map)
    {
        $ranges = array();

        foreach ($map as $c => $v) {
            $uv = $v['uv'];
            if ($uv != -1) {
                if (isset($range)) {
                    if ($c == $range[1] + 1 && $uv == $range[3] + 1) {
                        $range[1] ++;
                        $range[3] ++;
                    } else {
                        $ranges[] = $range;
                        $range = array($c, $c, $uv, $uv);
                    }
                } else {
                    $range = array($c, $c, $uv, $uv);
                }
            }
        }

        $ranges[] = $range;

        foreach ($ranges as $range) {
            if (isset($s)) {
                $s .= ',';
            } else {
                $s = 'array(';
            }
            $s .= $range[0] . '=>';
            $nb = $range[1] - $range[0] + 1;
            if ($nb > 1) {
                $s .= 'array(' . $range[2] . ',' . $nb . ')';
            } else {
                $s .= $range[2];
            }
        }

        $s .= ')';

        return $s;
    }

    /**
     * @param string $file
     * @param string $s
     * @param string $mode
     */
    private function SaveToFile($file, $s, $mode)
    {
        $f = fopen($file, 'w' . $mode);
        fwrite($f, $s);
        fclose($f);
    }

    /**
     *
     * @param string $file
     * @param string $type
     * @param string $enc
     * @param boolean $embed
     * @param boolean $subset
     * @param array $map
     * @param array $info
     */
    private function MakeDefinitionFile($file, $type, $enc, $embed, $subset, $map, $info)
    {
        $s = "<?php\n";
        $s .= '$type = \'' . $type . "';\n";
        $s .= '$name = \'' . $info['FontName'] . "';\n";
        $s .= '$desc = ' . $this->MakeFontDescriptor($info) . ";\n";
        $s .= '$up = ' . $info['UnderlinePosition'] . ";\n";
        $s .= '$ut = ' . $info['UnderlineThickness'] . ";\n";
        $s .= '$cw = ' . $this->MakeWidthArray($info['Widths']) . ";\n";
        $s .= '$enc = \'' . $enc . "';\n";

        $diff = $this->MakeFontEncoding($map);

        if ($diff) {
            $s .= '$diff = \'' . $diff . "';\n";
        }

        $s .= '$uv = ' . $this->MakeUnicodeArray($map) . ";\n";

        if ($embed) {
            $s .= '$file = \'' . $info['File'] . "';\n";
            if ($type == 'Type1') {
                $s .= '$size1 = ' . $info['Size1'] . ";\n";
                $s .= '$size2 = ' . $info['Size2'] . ";\n";
            } else {
                $s .= '$originalsize = ' . $info['OriginalSize'] . ";\n";
                if ($subset) {
                    $s .= "\$subsetted = true;\n";
                }
            }
        }

        $s .= "?>\n";

        $this->SaveToFile($file, $s, 't');
    }

    /**
     *
     * @param string $fontFile
     * @param string $enc
     * @param boolean $embed
     * @param boolean $subset
     *
     * @return boolean
     */
    public static function MakeFont($fontFile, $fontPath, &$zFile, $enc = 'cp1252', $embed = true, $subset = true)
    {
        $makeFont = new self;

        $map = $makeFont->LoadMap($enc);

        if (get_magic_quotes_runtime()) {
            @set_magic_quotes_runtime(false);
        }

        $baseName = $fontPath . substr(basename($fontFile), 0, -4);
        
        $defFile = $baseName . ".php";
        
        $zFile = $baseName . ".z";

        ini_set('auto_detect_line_endings', '1');

        $ext = strtolower(substr($fontFile, -3));

        $info = false;
        
        if (file_exists($fontFile) === false) {
            return false;
        }
        if ($ext == 'ttf' || $ext == 'otf') {
            $type = 'TrueType';
            $info = $makeFont->GetInfoFromTrueType($fontFile, $embed, $subset, $map);
        } elseif ($ext == 'pfb') {
            $type = 'Type1';
            $info = $makeFont->GetInfoFromType1($fontFile, $embed, $map);
        }

        if ($info === false) {
            return false;
        }
        
        if ($embed) {
            $info['File'] = $zFile;

            if (function_exists('gzcompress')) {
                $makeFont->SaveToFile($info['File'], gzcompress($info['Data']), 'b');
            } else {
                $subset = false;
            }
            
            $info['File'] = basename($info['File']);
        }

        $makeFont->MakeDefinitionFile($defFile, $type, $enc, $embed, $subset, $map, $info);

        return $defFile;
    }
}
