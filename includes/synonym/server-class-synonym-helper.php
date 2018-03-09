<?php

namespace RRZE\Synonym\Server;

Class WplisttableHelper {

    public static function getSynonymsForWPListTable() {
        
        $args = array(
            'sslverify'   => false,
        );
        
        $flag = 0;
        $z = get_option('registerServer');
        
        if($z) {
        
            foreach($z as $k => $v) {
                $content = wp_remote_get("https://{$v}/wp-json/wp/v2/synonym?per_page=100", $args );
                
                $status_code = wp_remote_retrieve_response_code( $content );
                
                if ( 200 === $status_code ) {
               
                    $response[] = $content['body'];
                    $flag = 1;
                }
            }
            
            if($flag == 1) {
                $clean = array_filter($response);


                foreach($clean as $c => $v) {
                    $list[$c] = json_decode($clean[$c], true);
                }

                $i = 1;
                foreach($list as $k => $v) {
                    foreach($v as $b => $c) {
                        $item[$i]['id']         = $c['id'];
                        $item[$i]['title']      = $c['title']['rendered'];
                        $item[$i]['slug']       = $c['slug'];
                        $item[$i]['synonym']    = $c['synonym']['synonym'][0];
                        $url = parse_url($c['guid']['rendered']);
                        $item[$i]['domain']     = $url['host'];
                        $i++;
                    }
                }
                return $item;
            }
        } else {
            return;
        }
    }
}