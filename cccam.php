<?php
        /**
        * Simple PHP script which processes cccam's cline files like "C: s3.cccam-free.com 11000 nngins cccam-free.com"
        * and generates a *.cfg for Iris 9700 HD 02 Satellite recievers.
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
        
	$ds	= DIRECTORY_SEPARATOR;
	$in     = __DIR__ . $ds . $opts[ 'input' ];
	$files	= is_dir( $in )? scandir( $in ) : array( $opts[ 'input' ] );
	$now    = new \Datetime( 'now' );
	$fmt    = new \IntlDateFormatter(
                    \Locale::getDefault(),
                    \IntlDateFormatter::SHORT,
                    \IntlDateFormatter::MEDIUM,
                    date_default_timezone_get(),
                    \IntlDateFormatter::GREGORIAN
                );
        $f = preg_replace( '/[^A-Za-z0-9_\-]/', '_', $fmt->format( $now ) );
	$out	= is_dir( $opts[ 'output' ] )? $opts[ 'output' ]. $ds . 'cccam_lines['.$f.'].cfg' : $opts[ 'output' ];

	$newContent = '';
	foreach( $files as $file ) {
		if( in_array( $file, array( '.', '..', $argv[ 0 ] ) ) ) { continue; }
		$filePath = is_dir( $in )? $in . $ds . $file : $file;
		if( file_exists( $filePath ) ) {
                        //Skip directories
                        if( is_dir( $filePath ) ) { continue; }

			$content = file( $filePath );
//			$newContent = '';
			foreach( $content as $line ) {
				//remove new lines
				$line = trim(preg_replace('/\s+/', ' ', $line));

				if( empty( trim( $line ) ) ) { continue; }
				$words = explode( ' ', $line );

				if( !isset( $handle ) ) {
					$handle = fopen( $out, 'w') or die( 'Cannot open file:  '.$out );
					$header = '<NETDBS_TXT_VER_1>';
					$prefix = 'CCCAM:';
					$suffix  = '{2}';
				}
				$data = "\n".$prefix;
				foreach( $words as $k => $word ) {
					if( $k == 0 ) { continue; }
//					elseif( $k == 2 ) { $data .= '{16000}'; }
					$data .= sprintf( '{%s}', $word );
				}
				$data .= $suffix;
				if( empty( $newContent ) ) { $newContent .= $header; }
				$newContent .= $data;
			}
		} else { throw new \Exception( sprintf( 'File %s does not exists.', $filePath ) ); }
	}

        fwrite( $handle, $newContent );

        function checkParams( $longopts ) {
            $opts = getopt( '', $longopts );
            if( count( $opts ) != count( $longopts ) || !file_exists( $opts[ 'input' ] ) ) {
                $result = false;
            } else { $result = $opts; }

            return $result;
        }
?>
