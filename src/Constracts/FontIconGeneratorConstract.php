<?php
namespace Jankx\Megu\Constracts;

interface FontIconGeneratorConstract
{
    public function setFontName($fontName);
    public function setFontPath($path);
    public function setFontFamily($path);
    public function isMatched();
    public function getGlyphMaps();
}
