<?php
/* Prevent direct access */
defined( 'ABSPATH' ) or die( "You can't access this file directly." );

if ( !class_exists('ASP_Media_Parser') ) {
    /**
     * Class ASP_Media_Parser
     *
     * Class to parse attachment contents to strings
     *
     * @class        ASP_Media_Parser
     * @version      1.0
     * @package      AjaxSearchPro/Classes
     * @category     Class
     * @author       Ernest Marcinko
     */
    class ASP_Media_Parser {
        /**
         * Mime groups array
         *
         * @var array
         */
        private $mimes = array(
            'pdf'            => array(
                'application/pdf'
            ),
            'text'           => array(
                'text/plain',
                'text/csv',
                'text/tab-separated-values',
                'text/calendar',
                'text/css',
                'text/html'
            ),
            'richtext'       => array(
                'text/richtext',
                'application/rtf'
            ),
            'mso_word'       => array(
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-word.document.macroEnabled.12',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
                'application/vnd.ms-word.template.macroEnabled.12',
                'application/vnd.oasis.opendocument.text'
            ),
            'mso_excel'      => array(
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-excel.sheet.macroEnabled.12',
                'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
                'application/vnd.ms-excel.template.macroEnabled.12',
                'application/vnd.ms-excel.addin.macroEnabled.12',
                'application/vnd.oasis.opendocument.spreadsheet',
                'application/vnd.oasis.opendocument.chart',
                'application/vnd.oasis.opendocument.database',
                'application/vnd.oasis.opendocument.formula'
            ),
            'mso_powerpoint' => array(
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
                'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
                'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
                'application/vnd.openxmlformats-officedocument.presentationml.template',
                'application/vnd.ms-powerpoint.template.macroEnabled.12',
                'application/vnd.ms-powerpoint.addin.macroEnabled.12',
                'application/vnd.openxmlformats-officedocument.presentationml.slide',
                'application/vnd.ms-powerpoint.slide.macroEnabled.12',
                'application/vnd.oasis.opendocument.presentation',
                'application/vnd.oasis.opendocument.graphics'
            )
        );


        /**
         * ASP_Media_Parser constructor.
         * @param $args
         */
        function __construct($args) {
            $defaults = array(
                'pdf_parser' => 'auto' // auto, smalot, pdf2txt
            );

            $this->args = wp_parse_args($args, $defaults);
            $this->args = apply_filters('asp_media_parser_args', $this->args, $defaults);
        }

        /**
         * Checks if a mime type belongs to a certain mime group (text, richtext etc..)
         *
         * @param string $type
         * @param string $mime
         * @return bool
         */
        function isThis($type = 'text', $mime = '') {
            return (isset($this->mimes[$type]) && in_array($mime, $this->mimes[$type]));
        }

        /**
         * Gets contents from a text based file
         *
         * @param $filename
         * @param $mime
         * @return bool|string
         */
        function parseTXT($filename, $mime) {
            if ( !$this->isThis('text', $mime ) )
                return '';

            return wpd_get_file($filename);
        }

        /**
         * Gets contents from a richtext file
         *
         * @param $filename
         * @param $mime
         * @return string
         */
        function parseRTF($filename, $mime) {
            if ( !$this->isThis('richtext', $mime ) )
                return '';

            $rtf = wpd_get_file($filename);
            if ($rtf != '') {
                include_once(ASP_EXTERNALS_PATH . 'class.rtf-html-php.php');
                $reader = new ASP_RtfReader();
                $reader->Parse($rtf);
                $formatter = new ASP_RtfHtml();

                return html_entity_decode(strip_tags($formatter->Format($reader->root)));
            }

            return '';
        }

        /**
         * Gets contents from a PDF file
         *
         * @param $filename
         * @param $mime
         * @return string
         */
        function parsePDF($filename, $mime) {
            if ( !$this->isThis('pdf', $mime ) )
                return '';

            $args = $this->args;
            $contents = '';

            // PDF Parser for php 5.3 and above
            if ($args['pdf_parser'] == 'auto' || $args['pdf_parser'] == 'smalot') {
                if ( version_compare(PHP_VERSION, '5.3', '>=') ) {
                    include_once(ASP_EXTERNALS_PATH . 'class.pdfsmalot.php');
                    $parser = new ASP_PDFSmalot();
                    $parser = $parser->getObj();
                    try {
                        $pdf = $parser->parseFile($filename);
                        $contents = $pdf->getText();
                    } catch (Exception $e) {
                        echo 'Caught exception: ',  $e->getMessage(), "\n";
                    }
                }
            }

            // Different method maybe?
            if ($args['pdf_parser'] == 'auto' || $args['pdf_parser'] == 'pdf2txt') {
                if ( $contents == '' ) {
                    include_once(ASP_EXTERNALS_PATH . 'class.pdf2txt.php');

                    $pdfParser = new ASP_PDF2Text();
                    $pdfParser->setFilename($filename);
                    $pdfParser->decodePDF();
                    $contents = $pdfParser->output();
                }
            }

            return $contents;
        }

        /**
         * Gets contents from an Office Word file
         *
         * @param $filename
         * @param $mime
         * @return string
         */
        function parseMSOWord($filename, $mime) {
            if ( !$this->isThis('mso_word', $mime ) )
                return '';

            if ( false !== strpos( $mime, 'opendocument' ) ) {
                $o = $this->getFileFromArchive('content.xml', $filename);
            } else {
                $o = $this->getFileFromArchive('word/document.xml', $filename);
            }
            return $o;
        }

        /**
         * Gets contents from an Office Excel file
         *
         * @param $filename
         * @param $mime
         * @return string
         */
        function parseMSOExcel($filename, $mime) {
            if ( !$this->isThis('mso_excel', $mime ) )
                return '';

            if ( false !== strpos($mime, 'opendocument') ) {
                $o = $this->getFileFromArchive('content.xml', $filename);
            } else {
                $o = $this->getFileFromArchive('xl/sharedStrings.xml', $filename);
            }
            return $o;
        }

        /**
         * Gets contents from an Office Powerpoint file
         *
         * @param $filename
         * @param $mime
         * @return string
         */
        function parseMSOPpt($filename, $mime) {
            if ( !$this->isThis('mso_powerpoint', $mime ) )
                return '';

            $out = '';
            if ( class_exists( 'ZipArchive' ) ) {
                if (false !== strpos($mime, 'opendocument')) {
                    $out = $this->getFileFromArchive('content.xml', $filename);
                } else {
                    $zip = new ZipArchive();
                    if ( true === $zip->open($filename) ) {
                        $slide_num = 1;
                        while (false !== ($xml_index = $zip->locateName('ppt/slides/slide' . absint($slide_num) . '.xml'))) {
                            $xml = $zip->getFromIndex($xml_index);
                            $out .= ' ' . $this->getXMLContent($xml);
                            $slide_num++;
                        }
                        $zip->close();
                    }
                }
            }

            return $out;
        }

        /**
         * Gets the content from an XML string
         *
         * @param $xml
         * @return string
         */
        private function getXMLContent($xml) {
            if ( class_exists('DOMDocument') ) {
                $dom = new DOMDocument();
                $dom->loadXML($xml, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
                return $dom->saveXML();
            }
            return '';
        }

        /**
         * Gets a file from an archive, based on the xml file name
         *
         * @param $xml
         * @param $filename
         * @return string
         */
        private function getFileFromArchive($xml, $filename) {
            if ( class_exists('ZipArchive') && class_exists('DOMDocument') ) {
                $output_text = '';
                $zip = new ZipArchive();

                if (true === $zip->open($filename)) {
                    if (false !== ($xml_index = $zip->locateName($xml))) {
                        $xml_data = $zip->getFromIndex($xml_index);
                        $dom = new DOMDocument();
                        $dom->loadXML( $xml_data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING );
                        $output_text = $dom->saveXML();
                    }
                    $zip->close();
                } else {
                    // File open error
                    return '';
                }

                return $output_text;
            }

            // The ZipArchive class is missing
            return '';
        }

    }
}