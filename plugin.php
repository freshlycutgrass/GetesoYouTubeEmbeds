<?php
// YouTube/plugin.php
// Allows users to embed YouTube videos.

if (!defined("IN_ESO")) exit;

class GetesoYouTubeEmbeds extends Plugin {

var $id = "GetesoYouTubeEmbeds";
var $name = "YouTube Embeds";
var $version = "1.0";
var $description = "Allows users to embed YouTube videos.";
var $author = "freshlycutgrass";

var $youtube = array();

function init()
{
    parent::init();
	
    // Language definitions.
    $this->eso->addLanguage("YouTube", "YouTube");

    // Add the youtube formatter that will parse and unparse youtube embeds.
    $this->eso->formatter->addFormatter("youtube", "Formatter_YouTube");
    $this->eso->addCSS("plugins/GetesoYouTubeEmbeds/embed.css");
   }

}

class Formatter_YouTube {

var $formatter;
var $modes = array("youtube", "youtube_tag", "youtube_bbcode");
var $revert = array("<youtube>" => "&lt;youtube&gt;", "</youtube>" => "&lt;/youtube&gt;");

function Formatter_YouTube(&$formatter)
{
    $this->formatter =& $formatter;
}

function format()
{       
        // Map the different forms of youtube embeds to the same lexer mode, and map a function for this mode.
        $this->formatter->lexer->mapFunction("youtube", array($this, "youtube"));
        $this->formatter->lexer->mapHandler("youtube_tag", "youtube");
        $this->formatter->lexer->mapHandler("youtube_bbcode", "youtube");

        // Add these youtube modes to the lexer.  They are allowed in all modes.
        $allowedModes = $this->formatter->getModes($this->formatter->allowedModes["inline"], "youtube");
        foreach ($allowedModes as $mode) {
                $this->formatter->lexer->addEntryPattern('&lt;youtube&gt;https:\/\/youtu.be\/(?=.*&lt;\/youtube&gt;)', $mode, "youtube_tag");
                $this->formatter->lexer->addEntryPattern('&lt;youtube&gt;https:\/\/www.youtube.com\/watch\?v=(?=.*&lt;\/youtube&gt;)', $mode, "youtube_tag");
                $this->formatter->lexer->addEntryPattern('\[youtube\]https:\/\/youtu.be\/(?=.*\[\/youtube\])', $mode, "youtube_bbcode");
                $this->formatter->lexer->addEntryPattern('\[youtube\]https:\/\/www.youtube.com\/watch\?v=(?=.*\[\/youtube\])', $mode, "youtube_bbcode");
        }
        $this->formatter->lexer->addExitPattern('&lt;\/youtube&gt;', "youtube_tag");
        $this->formatter->lexer->addExitPattern('\[\/youtube]', "youtube_bbcode");
}

// Add HTML details tags to the output.
function youtube($match, $state)
{
        switch ($state) {
                case LEXER_ENTER: $this->formatter->output .= "<div id=\"youtube-div\"><iframe src=\"https://www.youtube.com/embed/"; break;
                case LEXER_EXIT: $this->formatter->output .= "\" id=\"youtube-iframe\" allowfullscreen scrolling=\"no\" allow=\"accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture;\"></iframe></div>"; break;
                case LEXER_UNMATCHED: $this->formatter->output .= $match;
        }
        return true;
}

// Revert details tags to their formatting code.
function revert($string)
{
        // Remove the button from the tag.
        if (preg_match("/<div id=\"youtube-div\"(.*?)>/", $string)) $string = str_replace("<div id=\"youtube-div\"><iframe src=\"https://www.youtube.com/embed/", "<youtube>https://youtu.be/", $string);
        // Clean up the end of the tag.
        $string = str_replace("\" id=\"youtube-iframe\" allowfullscreen scrolling=\"no\" allow=\"accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture;\"></iframe></div>", "</youtube>", $string);
        return $string;
}

}
