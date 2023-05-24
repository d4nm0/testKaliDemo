
<?php
/**
 * Doc file  
 * 
 * PHP version 5
 * 
 * @category Doc
 * 
 * @package Backend
 * 
 * @author EISGE <kalifast@eisge.com>
 * 
 * @license kalifast.com Kalifast
 * 
 * @version SVN: $Id$
 * 
 * @link kalifast.com
 */ 
require_once 'class.Diff.php';
/**
 * Classe Doc
 * 
 * @category Functions
 * 
 * @package Backend
 * 
 * @author EISGE <kalifast@eisge.com>
 * 
 * @license kalifast.com Kalifast
 * 
 * @link kalifast.com
 */



class Doc extends BaseApi
{
    /**
     * Création d'une nouvelle doc
     * 
     * @return number
     */
    function createDoc()
    {
        $d = $this->checkParams(
            [
                'name' => 'html'
            ]
        );

        // Récupération de l'id max de la doc
        $s = $this->PDO->prepare(
            'SELECT max(ei_doc_id)+1 from ei_doc'
        );
        $s->execute();
        $max_doc_id = (int)($s->fetch()?:[1])[0];
        
        if ($max_doc_id == 0) {
            $max_doc_id = 1;
        }

        

        // Insertion de la nouvelle doc
        $s = $this->PDO->prepare(
            'INSERT into ei_doc(ei_doc_id, doc_name, ref_object_type_id) values(:ei_doc_id, :doc_name, "DOC")'
        );
        $res = $s->execute(
            [
                'ei_doc_id' => $max_doc_id,
                'doc_name' => $d->name
            ]
        );

        // Insertion de la nouvelle version du doc
        $s = $this->PDO->prepare(
            'INSERT into ei_doc_version(ei_doc_id, ei_doc_version_id, doc_name, doc_content, created_by, created_at, ei_subject_id, 
            ref_object_type_id) values(:ei_doc_id, 1, :doc_name, "Add documentation on your subject here : technical specification, procedures ... 
            It will be available on the documentation section of Kalifast", :user_id, now(), :ei_subject_id, "DOC")'
        );
        $s->execute(
            [
                'ei_doc_id' => $max_doc_id,
                'doc_name' => $d->name,
                'user_id' => $this->user['ei_user_id'],
                'ei_subject_id' => $this->user['current_subject_id']
            ]
        );

        $this->setdata(
            [
                "doc_id" => $max_doc_id
            ]
        );

        return $res;
    }

    /**
     * Récupération du contenu de la doc selon la version (si elle est à 0, on prend la version max)
     * 
     * @return array
     */
    function getDocVersion() 
    {
        $d = $this->checkParams(
            [
                'ei_doc_id' => 'int',
                'ei_doc_version_id' => 'int'
            ]
        );
        $d = $this->initOptionalParams('autosave', 'string', 'N');    

        $current_doc_version_id = 0;

        if ($d->ei_doc_version_id == 0) {
            if ($d->autosave == 'N') {
                // On récupère la version max
                $s = $this->PDO->prepare(
                    'SELECT dv.*, u.username, rss.is_final from ei_doc_version dv, ei_user u, ei_subject es, ref_subject_status rss where dv.ei_doc_id=:ei_doc_id and dv.ei_doc_version_id=(select max(ei_doc_version_id) 
                    from ei_doc_version where ei_doc_id=:ei_doc_id) and u.ei_user_id=dv.created_by and es.ei_subject_id=dv.ei_subject_id and u.ei_user_id=dv.created_by 
                    and es.ei_subject_version_id=(select max(ei_subject_version_id) from ei_subject where ei_subject_id=dv.ei_subject_id) 
                    and es.ref_subject_status_id=rss.ref_subject_status_id'
                );
                $s->execute(
                    [
                        'ei_doc_id' => $d->ei_doc_id
                    ]
                );
                $doc = $s->fetch(PDO::FETCH_ASSOC);

                // Récupération de la version max
                $s = $this->PDO->prepare(
                    'SELECT max(ei_doc_version_id) from ei_doc_version where ei_doc_id=:ei_doc_id'
                );
                $s->execute(
                    [
                        'ei_doc_id' => $d->ei_doc_id
                    ]
                );
                $current_doc_version_id = (int)($s->fetch()?:[0])[0];
            } else {
                // On récupère la version max
                $s = $this->PDO->prepare(
                    'SELECT dv.*, u.username, rss.is_final from ei_doc_version dv, ei_user u, ei_subject es, ref_subject_status rss where dv.ei_doc_id=:ei_doc_id  and dv.ei_doc_version_id=0  and u.ei_user_id=dv.created_by and es.ei_subject_id=dv.ei_subject_id and u.ei_user_id=dv.created_by 
                    and es.ei_subject_version_id=(select max(ei_subject_version_id) from ei_subject where ei_subject_id=dv.ei_subject_id) 
                    and es.ref_subject_status_id=rss.ref_subject_status_id'
                );
                $s->execute(
                    [
                        'ei_doc_id' => $d->ei_doc_id
                    ]
                );
                $doc = $s->fetch(PDO::FETCH_ASSOC);

                $current_doc_version_id = 0;
            }
        } else {
            $s = $this->PDO->prepare(
                'SELECT dv.*, u.username from ei_doc_version dv, ei_user u where dv.ei_doc_id=:ei_doc_id and dv.ei_doc_version_id=:ei_doc_version_id 
                and u.ei_user_id=dv.created_by'
            );
            $s->execute(
                [
                    'ei_doc_id' => $d->ei_doc_id,
                    'ei_doc_version_id' => $d->ei_doc_version_id
                ]
            );
            $doc = $s->fetch(PDO::FETCH_ASSOC);

            $current_doc_version_id = $d->ei_doc_version_id;
        }

        $s = $this->PDO->prepare(
            'SELECT 
                edp.path
            FROM
                ei_doc_path_vw edp
                inner join ei_doc_tree edt on edt.ei_doc_tree_node_id=edp.original_node_id
            WHERE
                edt.ei_doc_id =:ei_doc_id'
        );
        $s->execute(
            [
                'ei_doc_id' => $d->ei_doc_id
            ]
        );
        $doc_path = $s->fetch(PDO::FETCH_ASSOC);
        $doc['doc_path'] = $doc_path;
        
        // On vérifie maintenant s'il y a une version supérieure 
        // (pour afficher la flèche)
        $next_function_version_code_id = null;
        
        $s = $this->PDO->prepare(
            'SELECT ei_doc_version_id from ei_doc_version where ei_doc_id=:ei_doc_id and ei_doc_version_id=:ei_doc_version_id'
        );
        $s->execute(
            [
                'ei_doc_id' => $d->ei_doc_id,
                'ei_doc_version_id' => (int)$current_doc_version_id+1,
            ]
        );
        $next_doc_version_id = (int)($s->fetch()?:[0])[0];

        // On vérifie ensuite s'il y a une version précédente
        $previous_function_version_code_id = null;
        
        $s = $this->PDO->prepare(
            'SELECT ei_doc_version_id from ei_doc_version where ei_doc_id=:ei_doc_id and ei_doc_version_id=:ei_doc_version_id'
        );
        $s->execute(
            [
                'ei_doc_id' => $d->ei_doc_id,
                'ei_doc_version_id' => (int)$current_doc_version_id-1,
            ]
        );
        $previous_doc_version_id = (int)($s->fetch()?:[0])[0];

        $this->setData(
            [
                'doc' => $doc,
                'next_doc_version_id' => $next_doc_version_id,
                'previous_doc_version_id' => $previous_doc_version_id
            ]
        );

        return true;
    }

    /**
     * Création de la nouvelle version de la doc
     * 
     * @return true
     */
    function updateDocVersion()
    {
        $d = $this->checkParams(
            [
                'ei_doc_id' => 'int',
                'doc_name' => 'html',
                'doc_content' => 'html',
                'ei_subject_id' => 'int'
            ]
        );

        $d = $this->initOptionalParams('autosave', 'string', 'N');    

        if ($d->autosave === 'N') {
            // Récupération de la version max de la version
            $s = $this->PDO->prepare(
                'SELECT max(ei_doc_version_id)+1 from ei_doc_version where ei_doc_id=:ei_doc_id'
            );
            $s->execute(
                [
                    'ei_doc_id' => $d->ei_doc_id
                ]
            );
            $max_doc_version_id = (int)($s->fetch()?:[0])[0]; 
        } else if ($d->autosave === 'Y') {
            $max_doc_version_id = 0;
        }
       

        // Si on a choisi une intervention, on l'utilise pour la version sinon on utilise l'intervention courante
        $subject_id = 0;
        if ($d->ei_subject_id == 0) {
            $subject_id = $this->user['current_subject_id'];
        } else {
            $subject_id = $d->ei_subject_id;
        }

        if ($d->autosave === 'N') {
            $s = $this->PDO->prepare(
                "UPDATE `ei_doc` SET `doc_name`=:doc_name WHERE `ei_doc_id`=:ei_doc_id"
            );
            $s->execute(
                [
                    'ei_doc_id' => $d->ei_doc_id,
                    'doc_name' => $d->doc_name,
                ]
            );
            // Insertion de la nouvelle version du doc
            $s = $this->PDO->prepare(
                'INSERT into ei_doc_version(ei_doc_id, ei_doc_version_id, doc_name, doc_content, created_by, created_at, ei_subject_id, 
                ref_object_type_id) values(:ei_doc_id, :ei_doc_version_id, :doc_name, :doc_content, :user_id, now(), :ei_subject_id, "DOC")'
            );
            $s->execute(
                [
                    'ei_doc_id' => $d->ei_doc_id,
                    'ei_doc_version_id' => $max_doc_version_id,
                    'doc_name' => $d->doc_name,
                    'doc_content' => $d->doc_content,
                    'user_id' => $this->user['ei_user_id'],
                    'ei_subject_id' => $subject_id
                ]
            );
        } else if ($d->autosave === 'Y') {
            // Insertion de la nouvelle version du doc
            $s = $this->PDO->prepare(
                "INSERT INTO `ei_doc_version` (`ei_doc_id`, `ei_doc_version_id`, `doc_name`, `doc_content`, `created_by`, `created_at`, `ei_subject_id`, `ref_object_type_id`) 
                VALUES (:ei_doc_id, :ei_doc_version_id, :doc_name, :doc_content, :user_id, now(), :ei_subject_id, 'DOC') 
                ON DUPLICATE KEY UPDATE doc_content=:doc_content, created_by=:user_id, created_at=now();"
            );
            $s->execute(
                [
                    'ei_doc_id' => $d->ei_doc_id,
                    'ei_doc_version_id' => $max_doc_version_id,
                    'doc_name' => $d->doc_name,
                    'doc_content' => $d->doc_content,
                    'user_id' => $this->user['ei_user_id'],
                    'ei_subject_id' => $subject_id
                ]
            );
        }
    }

    /**
     * Verification de la doc autosave
     * 
     * @return true
     */
    function getAutoSaveDoc()
    {
        $d = $this->checkParams(
            [
                'ei_doc_id' => 'int'
            ]
        );
        // error_log($d->ei_doc_id);
        // Récupération de la version autosave 
        $s = $this->PDO->prepare(
            'SELECT edv.created_at-now()+20 as timecreated, edv.created_at, es.username, es.ei_user_id FROM ei_doc_version edv, ei_user es where edv.created_by=es.ei_user_id and ei_doc_id=:ei_doc_id and ei_doc_version_id=0;'
        );
        $s->execute(
            [
                'ei_doc_id' => $d->ei_doc_id
            ]
        );
        $CountOfDovVersion = $s->fetchAll(PDO::FETCH_ASSOC);

        // Récupération de la version max de la version
        $s = $this->PDO->prepare(
            'SELECT doc_content FROM ei_doc_version 
            where ei_doc_id=:ei_doc_id 
            and ei_doc_version_id=(select max(ei_doc_version_id) from ei_doc_version where ei_doc_id=:ei_doc_id);'
        );
        $s->execute(
            [
                'ei_doc_id' => $d->ei_doc_id
            ]
        );
        $MaxVersionText = $s->fetchAll(PDO::FETCH_ASSOC);
        

        // Récupération de la version autosave
        $s = $this->PDO->prepare(
            'SELECT doc_content FROM ei_doc_version where ei_doc_id=:ei_doc_id and ei_doc_version_id=0;'
        );
        $s->execute(
            [
                'ei_doc_id' => $d->ei_doc_id
            ]
        );
        $AutoSaveVersionText = $s->fetchAll(PDO::FETCH_ASSOC);
        

        // $html = $MaxVersionText[0]['doc_content'];

        // $dom = new DOMDocument();

        // $dom->preserveWhiteSpace = false;
        // $dom->loadHTML($html,LIBXML_HTML_NOIMPLIED);
        // $dom->formatOutput = true;
        // $old = $dom->saveXML($dom->documentElement); 
        // error_log($html);


        // $html = $AutoSaveVersionText[0]['doc_content'];

        // $dom = new DOMDocument();

        // $dom->preserveWhiteSpace = false;
        // $dom->loadHTML($html,LIBXML_HTML_NOIMPLIED);
        // $dom->formatOutput = true;
        // $new = $dom->saveXML($dom->documentElement); 

        // error_log($old);
        // error_log($new);
        // $diff = Diff::toHTML(Diff::compare($old, $new));
        // if (is_string($diff)) {
        //     error_log($diff);
        // }
        // error_log('////////');
        // error_log(Json_encode($MaxVersionText[0]['doc_content']));
        // error_log('////////');
        // if ($AutoSaveVersionText && $AutoSaveVersionText[0]['doc_content'] != null) {
        //     error_log(Json_encode($AutoSaveVersionText[0]['doc_content']));
        // }
        $data['MaxVersion']= $MaxVersionText[0]['doc_content'];
        if ($AutoSaveVersionText &&  $AutoSaveVersionText[0]['doc_content'] == null) {
            $data['AutoVersion']= '';
        } else if ($AutoSaveVersionText &&  $AutoSaveVersionText[0]['doc_content'] != null) {
            $data['AutoVersion']= $AutoSaveVersionText[0]['doc_content'];
        }
        
        $data['CountOfDovVersion'] = $CountOfDovVersion;
        // if ($diff) {
        //     $data['diff'] = htmlspecialchars_decode($diff);
        // }
        
        $this->setData($data);
        // $this->setData($CountOfDovVersion);

    }

    /**
     * Supprimer autosave version
     * 
     * @return true
     */
    function deleteAutoSaveDoc()
    {
        $d = $this->checkParams(
            [
                'ei_doc_id' => 'int'
            ]
        );
        // Récupération de la version max de la version
        $s = $this->PDO->prepare(
            'DELETE FROM ei_doc_version where  ei_doc_id=:ei_doc_id and ei_doc_version_id=0;

'
        );
        $s->execute(
            [
                'ei_doc_id' => $d->ei_doc_id
            ]
        );
        return true;
    }

    /**
     * Récupération du contenu d'un dossier sur l'arbre des docs
     * 
     * @return true
     */
    function getDocFolderContent()
    {
        $d = $this->checkParams(
            [
                'nodeid' => 'int'
            ]
        );
        // Récupération de la version max de la version
        $s = $this->PDO->prepare(
            "SELECT 
                *
            FROM
                ei_doc_tree edt
                    LEFT OUTER JOIN
                ei_doc ed ON edt.ei_doc_id = ed.ei_doc_id
                    LEFT OUTER JOIN
                ei_user eu ON ed.ei_user_id = eu.ei_user_id
            WHERE
                edt.ei_doc_tree_parent_node_id = :nodeid
                    AND edt.showed = 'Y'
                    AND CASE
                    WHEN
                        edt.foldername IS NULL
                    THEN
                        ed.ei_doc_version_id = (SELECT 
                                MAX(ei_doc_version_id)
                            FROM
                                ei_doc
                            WHERE
                                doc_name = ed.doc_name)
                    ELSE ed.ei_doc_version_id IS NULL
                END
            ORDER BY position"
        );
        $s->execute(
            [
                'nodeid' => $d->nodeid
            ]
        );
        $docContent = $s->fetchAll(PDO::FETCH_ASSOC);

        $path = $this->callClass(
            "Doc", 
            "getDocFolderPath", 
            [
                'nodeid' => $d->nodeid

            ]
        );
        $docContent[0]['path'] = $path->getdata();
        $this->setData($docContent);

    }

    /**
     * Récupération du contenu d'un dossier sur l'arbre des docs en fonction de la recherche
     * 
     * @return true
     */
    function getDocFolderContentWithResearch()
    {
        $d = $this->checkParams(
            [
                'nodeid' => 'int',
                'search' => 'string'
            ]
        );
        // Récupération de la version max de la version

        // ,SUBSTRING(REGEXP_REPLACE(edv2.doc_content,'<[^>]*>+', ''),LOCATE(UPPER(:searchstring),UPPER(REGEXP_REPLACE(edv2.doc_content,'<[^>]*>+', '')))-20,150) as Extract,
        //         CONVERT((length(SUBSTRING(REGEXP_REPLACE(edv2.doc_content,'<[^>]*>+', ''),LOCATE(UPPER(:searchstring),UPPER(REGEXP_REPLACE(edv2.doc_content,'<[^>]*>+', '')))-20,150)
        //         )-length(replace(SUBSTRING(REGEXP_REPLACE(edv2.doc_content,'<[^>]*>+', ''),LOCATE(UPPER(:searchstring),UPPER(REGEXP_REPLACE(edv2.doc_content,'<[^>]*>+', '')))-20,150),:searchstring,'')))/4,int) as COUNT
        $s = $this->PDO->prepare(
            "SELECT 
                    edpv.*,
                    edt.*,
                    ed.*,
                    edv2.ei_doc_id,
                    edv2.ei_doc_version_id,
                    edv2.doc_content,
                    edv2.created_by,
                    edv2.created_at,
                    edv2.ei_subject_id,
                    -- edv2.ref_object_type_id,
                    eu.*
                     ,SUBSTRING(REGEXP_REPLACE(edv2.doc_content,'<[^>]*>+', ''),LOCATE(UPPER(:searchstring),UPPER(REGEXP_REPLACE(edv2.doc_content,'<[^>]*>+', '')))-20,150) as Extract,
                CONVERT((length(SUBSTRING(REGEXP_REPLACE(edv2.doc_content,'<[^>]*>+', ''),LOCATE(UPPER(:searchstring),UPPER(REGEXP_REPLACE(edv2.doc_content,'<[^>]*>+', '')))-20,150)
                )-length(replace(SUBSTRING(REGEXP_REPLACE(edv2.doc_content,'<[^>]*>+', ''),LOCATE(UPPER(:searchstring),UPPER(REGEXP_REPLACE(edv2.doc_content,'<[^>]*>+', '')))-20,150),:searchstring,'')))/4,int) as COUNT
            FROM
                ei_doc_tree edt
                    LEFT OUTER JOIN
                ei_doc ed ON edt.ei_doc_id = ed.ei_doc_id
                    LEFT OUTER JOIN
                ei_doc_version edv2 ON edt.ei_doc_id = edv2.ei_doc_id
                    AND edv2.ei_doc_version_id = (SELECT 
                        MAX(edvc.ei_doc_version_id)
                    FROM
                        ei_doc_version edvc
                    WHERE
                        edvc.ei_doc_id = edt.ei_doc_id)
                    LEFT OUTER JOIN
                ei_user eu ON ed.ei_user_id = eu.ei_user_id
                    LEFT OUTER JOIN 
                ei_doc_path_vw edpv ON edpv.original_node_id = edt.ei_doc_tree_parent_node_id
            WHERE
                edt.ei_doc_tree_parent_node_id = :nodeid
                    AND edt.showed = 'Y'
                    AND CASE
                    WHEN
                        edt.foldername IS NULL
                    THEN
                        ed.ei_doc_version_id = (SELECT 
                                MAX(edc.ei_doc_version_id)
                            FROM
                                ei_doc edc
                            WHERE
                                edc.doc_name = ed.doc_name)
                    ELSE ed.ei_doc_version_id IS NULL
                END
                    AND edt.foldername LIKE :search
                    OR ed.doc_name LIKE :search
                    OR edv2.doc_content LIKE :search
            ORDER BY COUNT desc;"
        );
        $search = '%'.$d->search.'%';
        $s->execute(
            [
                'nodeid' => $d->nodeid,
                'search' => $search,
                'searchstring' => $d->search
            ]
        );
        $docContent = $s->fetchAll(PDO::FETCH_ASSOC);

        $path = $this->callClass(
            "Doc", 
            "getDocFolderPath", 
            [
                'nodeid' => $d->nodeid

            ]
        );
        $docContent[0]['path'] = $path->getdata();
        $this->setData($docContent);

    }

    /**
     * Vérifier si un fichier existe deja avec le nom donné
     * 
     * @return true
     */
    function verifyNameFileExist()
    {
        $d = $this->checkParams(
            [
                'name_file' => 'string'
            ]
        );
        // Récupération de la version max de la version
        $s = $this->PDO->prepare(
            "SELECT 
                COUNT(1) as nb_file
            FROM
                ei_doc
            WHERE
            (ref_object_type_id = 'FILE' or ref_object_type_id = 'DOC'  or  ref_object_type_id = 'LINK')
                AND doc_name = :name_file"
        );
        $s->execute(
            [
                'name_file' => $d->name_file
            ]
        );
        $countFile = $s->fetch(PDO::FETCH_ASSOC);
        $this->setData($countFile);

    }

    /**
     * Récupération du path d'un dossier sur l'arbre des docs
     * 
     * @return true
     */
    function getDocFolderPath()
    {
        $d = $this->checkParams(
            [
                'nodeid' => 'int'
            ]
        );
        // Récupération de la version max de la version
        $s = $this->PDO->prepare(
            "SELECT * FROM ei_doc_path_vw where original_node_id=:nodeid"
        );
        $s->execute(
            [
                'nodeid' => $d->nodeid
            ]
        );
        $docPath = $s->fetchAll(PDO::FETCH_ASSOC);
        $this->setData($docPath);

    }

    /**
     * Récupération historique des version d'un fichier
     * 
     * @return true
     */
    function getDocNodeHistory()
    {
        $d = $this->checkParams(
            [
                'node_name' => 'string'
            ]
        );
        // Récupération de la version max de la version
        $s = $this->PDO->prepare(
            "SELECT 
                ed.*, eu.picture_path, eu.username
            FROM
                ei_doc ed
                left outer join ei_user eu on eu.ei_user_id=ed.ei_user_id
            WHERE
                doc_name = :node_name
                    AND ref_object_type_id = 'FILE'"
        );
        $s->execute(
            [
                'node_name' => $d->node_name
            ]
        );
        $docHistory = $s->fetchAll(PDO::FETCH_ASSOC);
        $this->setData($docHistory);

    }

    /**
     * Récupération de l'image de l'utilisateur
     * 
     * @return true
     */
    function getUserImageProfil()
    {
        $d = $this->checkParams(
            [
                'ei_username' => 'int'
            ]
        );
        // Récupération de la version max de la version
        $s = $this->PDO->prepare(
            'SELECT username, picture_path FROM ei_user where username=:username;'
        );
        $s->execute(
            [
                'username' => $d->ei_username
            ]
        );
        $imageProfilUser = $s->fetchAll(PDO::FETCH_ASSOC);
        $this->setData($imageProfilUser);

    }



    /**
     * Recuperer toutes les version d'une doc
     * 
     * @return true
     */
    function getAllDocVersion()
    {
        $d = $this->checkParams(
            [
                'ei_doc_id' => 'int'
            ]
        );
        // Récupération de la version max de la version
        $s = $this->PDO->prepare(
            'SELECT edv.*, es.picture_path FROM ei_doc_version edv ,ei_user es   where edv.ei_doc_id=:ei_doc_id and es.ei_user_id=edv.created_by order by edv.created_at desc;'
        );
        $s->execute(
            [
                'ei_doc_id' => $d->ei_doc_id
            ]
        );
        $DovVersion = $s->fetchAll(PDO::FETCH_ASSOC);
        $this->setData($DovVersion);
        return true;
    }
}