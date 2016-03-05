<?php
      /**
      * Simple PHP script which processes IPTV files like "#EXTM3U
      * #EXTINF:-1,ES : TVE 1*
      * http://62.210.162.88:80/live/ES120/194804032016/1722.ts
      * #EXTINF:-1,ES : LA 2*
      * http://62.210.162.88:80/live/ES120/194804032016/1723.ts"
      * and generates a *.cfg for Iris 9700 HD 02 Satellite recievers
      *
      * @version 0.1
      * @since   0.1
      * @author  Pau Peris <pau@pauperis.com>
      */

      $help       = sprintf( 'Usage: php %1$s --input path%2$sto%2$sinput%2$sfiles%2$s --output path%2$sto%2$soutput%2$sfiles%2$s%3$s',
                      $argv[ 0 ],
                      DIRECTORY_SEPARATOR,
                      PHP_EOL );
      $longopts   = array(
          'input:',
          'output:'
      );

      if( PHP_SAPI != 'cli' || !$opts = checkPArams( $longopts ) ) {
          echo $help;
          return;
      }

      $params     = getParams( $opts );
      $newContent = readFiles( $argv, $params );
      $handle     = fopen( $params[ 'out' ], 'w') or die( 'Cannot open file:  '.$params[ 'out' ] );
      
      return fwrite( $handle, $newContent );

      function checkParams( $longopts ) {
          $opts = getopt( '', $longopts );
          if( count( $opts ) != count( $longopts ) || !file_exists( $opts[ 'input' ] ) ) {
              $result = false;
          } else { $result = $opts; }

          return $result;
      }

      function getParams( $opts ) {
        $in     = __DIR__ . DIRECTORY_SEPARATOR . $opts[ 'input' ];
        $files	= is_dir( $in )? scandir( $in ) : array( $opts[ 'input' ] );
        $now    = new \Datetime( 'now' );
        $fmt    = new \IntlDateFormatter(
                          \Locale::getDefault(),
                          \IntlDateFormatter::SHORT,
                          \IntlDateFormatter::MEDIUM,
                          date_default_timezone_get(),
                          \IntlDateFormatter::GREGORIAN
                      );
        $out	= is_dir( $opts[ 'output' ] )? $opts[ 'output' ]. DIRECTORY_SEPARATOR . 'iptv_lines['.preg_replace('/[^A-Za-z0-9_\-]/', '_',$fmt->format( $now )).'].cfg' : $opts[ 'output' ];

        return array(
          'files' => $files,
          'in'    => $in,
          'out'   => $out
        );
      }

      function readFiles( $argv, $params ) {
        extract( $params );
        $newContent = '';
      	foreach( $files as $file ) {

            if( in_array( $file, array( '.', '..', $argv[ 0 ] ) ) ) { continue; }

            $filePath = is_dir( $in )? $in . DIRECTORY_SEPARATOR . $file : $file;
            if( file_exists( $filePath ) ) {

                //Skip directories
                if( is_dir( $filePath ) ) { continue; }

                $content = file( $filePath );
                foreach( $content as $k => $line ) {

                    //remove new lines
                    $line = trim( preg_replace( '/\s+/', ' ', $line ) );

                    if( empty( trim( $line ) ) ) { continue; }
                        $newContent = getIPTVFileContnt( $content, $k, $newContent, $line );
                }
            } else { return sprintf( 'File %s does not exists.', $filePath ); }
      	}

        return $newContent;
      }

      function getIPTVFileContnt( $content, $k, $newContent, $line ) {
        //Check if this is the HTTP(S):// line
        if( strtolower( substr( $line, 0, 4 ) ) != 'http') { return $newContent; }

        $strs = explode( ' ', $line );
        $url = array_shift( $strs );

        //Get the last line to generate teh channel name
        $prvL = trim( preg_replace( '/\s+/', ' ', $content[ $k-1 ] ) );
        $strs = explode( ':', $prvL );
        $str  = array_pop( $strs );
        $str  = str_replace( '*', '', $str );

        if( empty( $newContent ) ) { $newContent .= '<NETDBS_TXT_VER_1>'; }

        $newContent .= "\n".sprintf( 'IPTV: {%s} {%s}', trim( $str ), preg_replace( '/\s+/', ' ', $url ) );

        return $newContent;
      }
?>
