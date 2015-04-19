<?php

/**
 *
 *
 * @author  Joel Hart
 */
class Mediotype_Core_Helper_File extends Mediotype_Core_Helper_Abstract
{

    /**
     * Get size of remote file
     *
     * @param $file
     * @return mixed
     */
    public function getSize($file)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $file);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        return curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
    }

    /**
     * Extracts single gzipped file. If archive will contain more then one file you will got a mess.
     *
     * @param $archive
     * @param $destination
     * @return int
     */
    public function unGZip($archive, $destination)
    {
        $buffer_size = 4096; // read 4kb at a time
        $archive     = gzopen($archive, 'rb');
        $dat         = fopen($destination, 'wb');
        while (!gzeof($archive)) {
            fwrite($dat, gzread($archive, $buffer_size));
        }
        fclose($dat);
        gzclose($archive);
        return filesize($destination);
    }

    /**
     * This code is unfinished and contains syntax errors
     */
    public function downloadFile($URI, $destinationDir = null, $filename = null)
    {
        $results = array();
        ini_set('memory_limit', '1028M');
        if (is_null($destinationDir)) {
            $destinationDir = Mage::getBaseDir('var') . DS . 'download';
            if(!file_exists($destinationDir)){
                mkdir($destinationDir);
            }
        } else {
            $destinationDir = rtrim($destinationDir, DS);
        }

        if (strtolower(substr($URI, 0, 3)) == "fil") {
            /*
             * Get file from file pointer
             */
            $request = curl_init($URI); // initiate curl object

            if ($request === FALSE) {

                Mage::log(array(
                    'CURL DID NOT FIND FILE AT FILEPATH LOCATION',
                    'File pointer does not point to a real file  :: ' . $URI),
                    null, 'file-pointer-problem.log');

                return 404;

            }

            curl_setopt($request, CURLOPT_HEADER, 1); // set to 0 to eliminate header info from response
            curl_setopt($request, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
            curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment this line if you get no gateway response.
            $post_response = curl_exec($request); // execute curl post and store results in $post_response
            $header_size = curl_getinfo($request, CURLINFO_HEADER_SIZE);
            curl_close($request); // close curl object

            if ($post_response === FALSE) {

                Mage::log(array(
                    'CURL RESPONSE ON FILE POINTER IS FALSE',
                    'File pointer does not point to a real file  :: ' . $URI),
                    null, 'file-pointer-problem.log');

                return 404;

            } else {

                try {
                    $body = substr( $post_response, $header_size );

                    $exploadedURI = explode( '/', $URI );
                    $lastValue    = array_pop( $exploadedURI );
                    $fileParts    = explode( '.', $lastValue );
                    $filename     = urlencode( trim( $fileParts[0] ) );
                    $fileExt      = urlencode( trim( $fileParts[1] ) );
                    $fullFilePath = $destinationDir . DS . $filename . "." . $fileExt;

                    $IOHandler = fopen( trim( $fullFilePath ), 'w+' );
                    fwrite( $IOHandler, $body );
                    fclose( $IOHandler );

                    $results['dir']       = $destinationDir;
                    $results['filename']  = $filename;
                    $results['extension'] = $fileExt;
                    $results['path']      = trim( $fullFilePath );

                    return $results;
                } catch(Exception $e) {

                    Mage::log( array(
                        '... EXCEPTION::     '.$e->getMessage(),
                        ' File pointer does not have a valid image file extension  :: ' . $URI),
                        null, 'filedl.log' );

                    return 404;
                }

            }

        }

        $URI = str_replace(" ", "%20", $URI);

        $request = curl_init(trim($URI)); // initiate curl object

        curl_setopt($request, CURLOPT_HEADER, 1); // set to 0 to eliminate header info from response
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false); // uncomment this line if you get no gateway response.
        curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);

        $post_response = curl_exec($request); // execute curl post and store results in $post_response
        $header_size   = curl_getinfo($request, CURLINFO_HEADER_SIZE);
        $mime_type     = curl_getinfo($request, CURLINFO_CONTENT_TYPE);

        $httpCode = curl_getinfo($request, CURLINFO_HTTP_CODE);
        if ($httpCode == 404) {
            return 404;
            /* Handle 404 here. */
        }

        curl_close($request); // close curl object
        if ($post_response != '') {

            // PROCESS HEADERS
            $header  = substr($post_response, 0, $header_size);
            $headers = $this->parseHttpHeaders($header);


            // PROCESS BODY
            $body = substr($post_response, $header_size);

            $fileExt = $this->getFileExtension($mime_type);

            if (is_null($filename)) {
                $exploadedURI = explode('/', $URI);
                $lastValue    = array_pop($exploadedURI);
                $fileParts    = explode('.', $lastValue);
                $filename     = urlencode(trim($fileParts[0]));
                $fullFilePath = $destinationDir . DS . $filename . "." . $fileExt;

            } else {
                $fullFilePath = $destinationDir . DS . $filename;
            }

            $results['dir']       = $destinationDir;
            $results['filename']  = $filename;
            $results['extension'] = $fileExt;
            $results['path']      = $fullFilePath;

            $IOHandler = fopen(trim($fullFilePath), 'w+');
            fwrite($IOHandler, $body);
            fclose($IOHandler);
            return $results;
        }


        Mediotype_Core_Helper_Debugger::log("FAILED TO DOWNLOAD FILE: '$URI'");

        return false;
    }


    /**
     * Based on http://php.net/manual/en/function.mime-content-type.php#107798
     *
     * @return bool|string
     */
    public function generateUpToDateMimeArray()
    {
        $url          = 'http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types';
        $results      = array();
        $fileContents = @explode("\n", @file_get_contents($url));
        foreach ($fileContents as $line) {
            if (isset($line[0]) && $line[0] !== '#' && preg_match_all(
                    '#([^\s]+)#',
                    $line,
                    $out
                ) && isset($out[1]) && ($c = count($out[1])) > 1
            ) {
                $results[$out[0][0]] = $out[0][1];
            }
        }
        return $results;
    }

    public function getFileExtension($mimeType)
    {
        $mimeArray = $this->generateUpToDateMimeArray();
        return $mimeArray[$mimeType];
    }

    /**
     * Taken from http://php.net/manual/en/function.http-parse-headers.php#112986
     *
     * @param $raw_headers
     * @return array
     */
    public function parseHttpHeaders($raw_headers)
    {
        $headers = array();
        $key     = ''; // [+]

        foreach (explode("\n", $raw_headers) as $i => $h) {
            $h = explode(':', $h, 2);

            if (isset($h[1])) {
                if (!isset($headers[$h[0]])) {
                    $headers[$h[0]] = trim($h[1]);
                } elseif (is_array($headers[$h[0]])) {
                    // $tmp = array_merge($headers[$h[0]], array(trim($h[1]))); // [-]
                    // $headers[$h[0]] = $tmp; // [-]
                    $headers[$h[0]] = array_merge($headers[$h[0]], array(trim($h[1]))); // [+]
                } else {
                    // $tmp = array_merge(array($headers[$h[0]]), array(trim($h[1]))); // [-]
                    // $headers[$h[0]] = $tmp; // [-]
                    $headers[$h[0]] = array_merge(array($headers[$h[0]]), array(trim($h[1]))); // [+]
                }

                $key = $h[0]; // [+]
            } else // [+]
            { // [+]
                if (substr($h[0], 0, 1) == "\t") // [+]
                {
                    $headers[$key] .= "\r\n\t" . trim($h[0]);
                } // [+]
                elseif (!$key) // [+]
                {
                    $headers[0] = trim($h[0]);
                }
                trim($h[0]); // [+]
            } // [+]
        }

        return $headers;
    }

}