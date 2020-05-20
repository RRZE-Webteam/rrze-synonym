<?php

namespace RRZE\Synonym;

defined('ABSPATH') || exit;

define ('SYNONYM_ENDPOINT', 'wp-json/wp/v2/synonym' );

class API {

    private $aAllCats = array();

    protected function checkDomain( &$url ){
        // checks if SYNONYM_ENDPOINT exists at $url, if HTTP-status is 200
        // returns (redirected) URL or FALSE  
        $ret = FALSE;
        $request = wp_remote_get( $url . SYNONYM_ENDPOINT . '?per_page=1' );
        $status_code = wp_remote_retrieve_response_code( $request );
        if ( $status_code == '200' ){
            $content = json_decode( wp_remote_retrieve_body( $request ), TRUE );
            $ret = substr( $content[0]['guid']["rendered"], 0 , strpos( $content[0]['guid']["rendered"], '?' ) );
        }
        return $ret;
    }

    protected function isRegisteredDomain( &$url ){
        return in_array( $url, $this->getDomains() );
    }

    public function getDomains(){
        $domains = array();
        $options = get_option( 'rrze-synonym' );
        if ( isset( $options['registeredDomains'] ) ){
            foreach( $options['registeredDomains'] as $shortname => $url ){
                $domains[$shortname] = $url;
            }	
        }
        asort( $domains );
        return $domains;
    }
    
    public function setDomain( &$shortname, &$url ){
        $ret = FALSE;
        $url = trailingslashit( preg_replace( "/^((http|https):\/\/)?/i", "https://", $url ) );
        $domains = $this->getDomains();
        $shortname = strtolower( preg_replace('/[^A-Za-z0-9\-]/', '', str_replace( ' ', '-', $shortname ) ) );
        if ( ( in_array( $url, $domains ) === FALSE ) && ( array_key_exists( $shortname, $domains ) === FALSE ) ) {
            $url = $this->checkDomain( $url );
            if ( $url ){
                $domains[$shortname] = $url;
            }else{
                return FALSE;
            }
        }
        return $domains;
    }

    public function deleteDomain( &$shortname ){
        $this->deletesynonym( $shortname );
    }

    protected function getTaxonomies( $url, $field, &$filter ){
        $aRet = array();    
        $url .= SYNONYM_ENDPOINT . '_' . $field;    
        $slug = ( $filter ? '&slug=' . $filter : '' );
        $page = 1;

        do {
            $request = wp_remote_get( $url . '?page=' . $page . $slug );
            $status_code = wp_remote_retrieve_response_code( $request );
            if ( $status_code == 200 ){
                $entries = json_decode( wp_remote_retrieve_body( $request ), true );
                if ( !empty( $entries ) ){
                    foreach( $entries as $entry ){
                        if ( $entry['source'] == 'website' ){                            
                            if ( $entry['children'] ) {
                                foreach( $entry['children'] as $childname ){
                                    $aRet[$entry['name']][$childname] = array();        
                                }
                            }else{
                                $aRet[$entry['name']] = array();
                            }
                        }
                    }
                    foreach( $aRet as $name => $aChildren ){
                        foreach ( $aChildren as $childname => $val ){
                            if ( isset( $aRet[$childname] ) ){
                                $aRet[$name][$childname] = $aRet[$childname];
                            }
                        }
                    }
                }
            }
            $page++;   
        } while ( ( $status_code == 200 ) && ( !empty( $entries ) ) );
        return $aRet;
    }

    
    public function sortIt( &$arr ){
        uasort( $arr, function($a, $b) {
            return strtolower( $a ) <=> strtolower( $b );
        } );
    }
    
    public function deleteTaxonomies( $source, $field ){
        $args = array(
            'hide_empty' => FALSE,
            'meta_query' => array(
                array(
                   'key'       => 'source',
                   'value'     => $source,
                   'compare'   => '='
                )
            ),
            'taxonomy'  => 'synonym_' . $field,
            'fields' => 'ids'
            );
        $terms = get_terms( $args );
        foreach( $terms as $ID  ){
            wp_delete_term( $ID, 'synonym_' . $field );
        }
    }


    public function deleteCategories( $source ){
        $this->deleteTaxonomies( $source, 'category');
    }

    public function deleteTags( $source ){
        $this->deleteTaxonomies( $source, 'tag');
    }

    protected function setCategories( &$aCategories, &$shortname ){
        $aTmp = $aCategories;
        foreach ( $aTmp as $name => $aDetails ){
            $term = term_exists( $name, 'synonym_category' );
            if ( !$term ) {
                $term = wp_insert_term( $name, 'synonym_category' );
                update_term_meta( $term['term_id'], 'source', $shortname );    
            }
            foreach ( $aDetails as $childname => $tmp ) {
                $childterm = term_exists( $childname, 'synonym_category' );
                if ( !$childterm ) {
                    $childterm = wp_insert_term( $childname, 'synonym_category', array( 'parent' => $term['term_id'] ) );
                    update_term_meta( $childterm['term_id'], 'source', $shortname );    
                }
            }
            if ( $aDetails ){
                $aTmp = $aDetails;
            }
        }
    }

    
    public function sortAllCats( &$cats, &$into ) {
        foreach ($cats as $ID => $aDetails) {
            $into[$ID]['slug'] = $aDetails['slug'];
            $into[$ID]['name'] = $aDetails['name'];            
            if ( $aDetails['parentID'] ) {
                $parentID = $aDetails['parentID'];
                $into[$parentID][$ID]['slug'] = $aDetails['slug'];
                $into[$parentID][$ID]['name'] = $aDetails['name'];
            }
            unset( $cats[$parentID] );
        }    
        $this->sortAllCats( $cats, $into );
    }


    public function sortCats(Array &$cats, Array &$into, $parentID = 0, $prefix = '' ) {
        $prefix .= ( $parentID ? '-' : '' );
        foreach ($cats as $i => $cat) {
            if ( $cat->parent == $parentID ) {
                $into[$cat->term_id] = $cat;                
                unset( $cats[$i] );
            }
            $this->aAllCats[$cat->term_id]['parentID'] = $cat->parent;
            $this->aAllCats[$cat->term_id]['slug'] = $cat->slug;
            $this->aAllCats[$cat->term_id]['name'] = str_replace( '~', '&nbsp;', str_pad( ltrim( $prefix . ' ' . $cat->name ), 100, '~') );
        }    
        foreach ($into as $topCat) {
            $topCat->children = array();
            $this->sortCats($cats, $topCat->children, $topCat->term_id, $prefix );
        }
        if ( !$cats ){
            foreach ( $this->aAllCats as $ID => $aDetails ){
                if ( $aDetails['parentID'] ){
                    $this->aAllCats[$aDetails['parentID']]['children'][$ID] = $this->aAllCats[$ID];
                }
            }
        } 
    }

    public function cleanCats(){
        foreach ( $this->aAllCats as $ID => $aDetails ){
            if ( $aDetails['parentID'] ){
                unset( $this->aAllCats[$ID] );
            }
        }
    }

    public function getSlugNameCats(&$cats, &$into ){
        foreach ( $cats as $i => $cat ){
            $into[$cat['slug']] = $cat['name'];
            if ( isset( $cat['children'] ) ){
                $this->getSlugNameCats($cat['children'], $into );
            }
            unset( $cats[$i] );
        }
    }

    public function getCategories( $url, $shortname, $categories = '' ){
        $aRet = array();
        $aCategories = $this->getTaxonomies( $url, 'category', $categories );
        $this->setCategories( $aCategories, $shortname );
        $categories = get_terms( array(
            'taxonomy' => 'synonym_category',
            'meta_query' => array( array(
                'key' => 'source',
                'value' => $shortname
            ) ),
            'hide_empty' => FALSE
            ) );
        $categoryHierarchy = array();
        $this->sortCats($categories, $categoryHierarchy);
        $this->cleanCats();
        $this->getSlugNameCats( $this->aAllCats, $aRet );
        return $aRet;
    }


    public function deleteSynonyms( $source ){
        // deletes all synonyms by source
        $iDel = 0;
        $allSynonyms = get_posts( array( 'post_type' => 'synonym', 'meta_key' => 'source', 'meta_value' => $source, 'numberposts' => -1 ) );

        foreach ( $allSynonyms as $synonym ) {
            wp_delete_post( $synonym->ID, TRUE );
            $iDel++;
        } 
        return $iDel;
    }

    protected function cleanContent( $txt ){
        // returns content without info below '<!-- rrze-synonym -->'
        $txt = substr( $txt, 0, strpos( $txt, '<!-- rrze-synonym -->' ));
        return $txt;
    }

    protected function absoluteUrl( $txt, $baseUrl ){
        // converts relative URLs to absolute ones
        $needles = array('href="', 'src="', 'background="');
        $newTxt = '';
        if (substr( $baseUrl, -1 ) != '/' ){
            $baseUrl .= '/';
        } 
        $newBaseUrl = $baseUrl;
        $baseUrlParts = parse_url( $baseUrl );
        foreach ( $needles as $needle ){
            while( $pos = strpos( $txt, $needle ) ){
                $pos += strlen( $needle );
                if ( substr( $txt, $pos, 7 ) != 'http://' && substr( $txt, $pos, 8) != 'https://' && substr( $txt, $pos, 6) != 'ftp://' && substr( $txt, $pos, 9 ) != 'mailto://' ){
                    if ( substr( $txt, $pos, 1 ) == '/' ){
                        $newBaseUrl = $baseUrlParts['scheme'] . '://' . $baseUrlParts['host'];
                    }
                    $newTxt .= substr( $txt, 0, $pos ).$newBaseUrl;
                } else {
                    $newTxt .= substr( $txt, 0, $pos );
                }
                $txt = substr( $txt, $pos );
            }
            $txt = $newTxt . $txt;
            $newTxt = '';
        }
        // convert all elements of srcset, too
        $needle = 'srcset="';
        while( $pos = strpos( $txt, $needle, $pos ) ){
            $pos += strlen( $needle );
            $len = strpos( $txt, '"', $pos ) - $pos;
            $srcset = substr( $txt, $pos, $len );
            $aSrcset = explode( ',', $srcset );
            $aNewSrcset = array();
            foreach( $aSrcset as $src ){
                $src = trim( $src );
                if ( substr( $src, 0, 1 ) == '/' ){
                    $aNewSrcset[] = $newBaseUrl . $src;
                }                                
            }
            $newSrcset = implode( ', ', $aNewSrcset );
            $txt = str_replace( $srcset, $newSrcset, $txt );
        }
        return $txt;
      }

    protected function getSynonyms( &$url, &$categories ){
        $synonyms = array();
        $aCategoryRelation = array();
        $filter = '&filter[synonym_category]=' . $categories;
        $page = 1;

        do {
            $request = wp_remote_get( $url . SYNONYM_ENDPOINT . '?page=' . $page . $filter );
            $status_code = wp_remote_retrieve_response_code( $request );
            if ( $status_code == 200 ){
                $entries = json_decode( wp_remote_retrieve_body( $request ), true );
                if ( !empty( $entries ) ){
                    if ( !isset( $entries[0] ) ){
                        $entries = array( $entries );
                    }
                    foreach( $entries as $entry ){
                        if ( $entry['source'] == 'website' ){
                            // $content = substr( $entry['content']['rendered'], 0, strpos( $entry['content']['rendered'], '<!-- rrze-synonym -->' ));
                            $content = $this->cleanContent( $entry['content']['rendered'] );
                            $content = $this->absoluteUrl( $content, $url );

                            $synonyms[$entry['id']] = array(
                                'id' => $entry['id'],
                                'title' => $entry['title']['rendered'],
                                'content' => $content,
                                'lang' => $entry['lang'],
                                'synonym_category' => $entry['synonym_category'],
                                'remoteID' => $entry['remoteID'],
                                'remoteChanged' => $entry['remoteChanged']
                            );
                            $sTag = '';
                            foreach ( $entry['synonym_tag'] as $tag ){
                                $sTag .= $tag . ',';
                            }
                            $synonyms[$entry['id']]['synonym_tag'] = trim( $sTag, ',' );
                            $synonyms[$entry['id']]['URLhasSlider'] = ( ( strpos( $content, 'slider') !== false ) ? $entry['link'] : FALSE ); // we cannot handle sliders, see note in Shortcode.php shortcodeOutput()
                        }
                    }
                }
            }
            $page++;   
        } while ( ( $status_code == 200 ) && ( !empty( $entries ) ) );

        return $synonyms;
    }

    public function setTags( $terms, $shortname ){
        if ( $terms ){
            $aTerms = explode( ',', $terms );
            foreach( $aTerms as $name ){
                if ( $name ){
                    $term = term_exists( $name, 'synonym_tag' );
                    if ( !$term ) {
                        $term = wp_insert_term( $name, 'synonym_tag' );
                        update_term_meta( $term['term_id'], 'source', $shortname );    
                    }
                }
            }
        }
    }

    public function getSynonymsRemoteIDs( $source ){
        $aRet = array();
        $allSynonyms = get_posts( array( 'post_type' => 'synonym', 'meta_key' => 'source', 'meta_value' => $source, 'fields' => 'ids', 'numberposts' => -1 ) );
        foreach ( $allSynonyms as $postID ){
            $remoteID = get_post_meta( $postID, 'remoteID', TRUE );
            $remoteChanged = get_post_meta( $postID, 'remoteChanged', TRUE );
            $aRet[$remoteID] = array(
                'postID' => $postID,
                'remoteChanged' => $remoteChanged
                );
        }
        return $aRet;
    }

    public function setSynonyms( $url, $categories, $shortname ){
        $iNew = 0;
        $iUpdated = 0;
        $iDeleted = 0;
        $aURLhasSlider = array();

        // get all remoteIDs of stored synonyms to this source ( key = remoteID, value = postID )
        $aRemoteIDs = $this->getSynonymsRemoteIDs( $shortname );

        // $this->deleteTags( $shortname );
        // $this->deleteCategories( $shortname );
        // $this->getCategories( $url, $shortname );

        // get all synonyms
        $aFaq = $this->getSynonyms( $url, $categories );
        
        // set synonyms
        foreach ( $aFaq as $synonym ){
            $this->setTags( $synonym['synonym_tag'], $shortname );

            $aCategoryIDs = array();
            foreach ( $synonym['synonym_category'] as $name ){
                $term = get_term_by( 'name', $name, 'synonym_category' );
                $aCategoryIDs[] = $term->term_id;
            }

            if ( $synonym['URLhasSlider'] ) {
                $aURLhasSlider[] = $synonym['URLhasSlider'];
            } else {
                if ( isset( $aRemoteIDs[$synonym['remoteID']] ) ) {
                    if ( $aRemoteIDs[$synonym['remoteID']]['remoteChanged'] < $synonym['remoteChanged'] ){
                        // update synonyms
                        $post_id = wp_update_post( array(
                            'ID' => $aRemoteIDs[$synonym['remoteID']]['postID'],
                            'post_name' => sanitize_title( $synonym['title'] ),
                            'post_title' => $synonym['title'],
                            'post_content' => $synonym['content'],
                            'meta_input' => array(
                                'source' => $shortname,
                                'lang' => $synonym['lang'],
                                'remoteID' => $synonym['remoteID']
                                ),
                            'tax_input' => array(
                                'synonym_category' => $aCategoryIDs,
                                'synonym_tag' => $synonym['synonym_tag']
                                )
                            ) ); 
                        $iUpdated++;
                    }
                    unset( $aRemoteIDs[$synonym['remoteID']] );
                } else {
                    // insert synonyms
                    $post_id = wp_insert_post( array(
                        'post_type' => 'synonym',
                        'post_name' => sanitize_title( $synonym['title'] ),
                        'post_title' => $synonym['title'],
                        'post_content' => $synonym['content'],
                        'comment_status' => 'closed',
                        'ping_status' => 'closed',
                        'post_status' => 'publish',
                        'meta_input' => array(
                            'source' => $shortname,
                            'lang' => $synonym['lang'],
                            'remoteID' => $synonym['id'],
                            'remoteChanged' => $synonym['remoteChanged']
                            ),
                        'tax_input' => array(
                            'synonym_category' => $aCategoryIDs,
                            'synonym_tag' => $synonym['synonym_tag']
                            )
                        ) );
                    $iNew++;
                }
            }
        }

        // delete all other synonyms to this source
        foreach( $aRemoteIDs as $remoteID => $aDetails ){
            wp_delete_post( $aDetails['postID'], TRUE );
            $iDeleted++;
        }

        return array( 
            'iNew' => $iNew,
            'iUpdated' => $iUpdated,
            'iDeleted' => $iDeleted,
            'URLhasSlider' => $aURLhasSlider
        );
    }
}    


