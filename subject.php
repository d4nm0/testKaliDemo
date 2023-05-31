<?php
/**
 * Subject file  
 * 
 * PHP version 5
 * 
 * @category Subject
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

/**
 * Subject class 
 * 
 * @category Subject
 * 
 * @package Backend
 * 
 * @author EISGE <kalifast@eisge.com>
 * 
 * @license kalifast.com Kalifast
 * 
 * @link kalifast.com
 */
class Subject extends BaseApi
{
    /**
     * Ajouter une doc dans les attachment du subject
     * 
     * @return array
     */
    function addDocInAttachment()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int',
                'ei_doc_id' => 'int'
            ]
        );
        // Récupération du nom de la doc
        // error_log($d->ei_subject_id);
        $s = $this->PDO->prepare(
            'SELECT ei_doc_version_id, doc_name from ei_doc_version where ei_doc_id=:ei_doc_id and ei_doc_version_id=(select max(ei_doc_version_id)
            from ei_doc_version where ei_doc_id=:ei_doc_id)'
        );
        $s->execute(
            [
                'ei_doc_id' => $d->ei_doc_id
            ]
        );
        $content_doc = $s->fetch(PDO::FETCH_ASSOC);

        // verfier qu'il y ai qu'une version
        $s = $this->PDO->prepare(
            'SELECT count(*) from ei_attachment WHERE ei_attachment_id=:ei_doc_id'
        );
        $s->execute(
            [
                'ei_doc_id' => $d->ei_doc_id
            ]
        );

        $count_doc = (int)($s->fetch()?:[0])[0];
        if ($count_doc == 0) {
            $s = $this->PDO->prepare(
                'INSERT into ei_attachment(ei_attachment_id, ei_attachment_name, ei_attachment_type, creator_id, created_at, ei_attachment_show) values(:ei_doc_id,:doc_name,"klf/doc",:ei_user_id,NOW(),"Y")'
            );
            $s->execute(
                [
                    'ei_doc_id' => $d->ei_doc_id,
                    'doc_name' => $content_doc['doc_name'],
                    'ei_user_id' => $this->user['ei_user_id']
                ]
            );

            $s = $this->PDO->prepare(
                'INSERT into ei_subject_attachment(ei_attachment_id, ei_subject_id) values (:ei_doc_id,:ei_subject_id)'
            );
            $s->execute(
                [
                    'ei_doc_id' => $d->ei_doc_id,
                    'ei_subject_id' => $d->ei_subject_id
                ]
            );
        } else {
            $s = $this->PDO->prepare(
                'UPDATE  ei_attachment set ei_attachment_show="Y" where ei_attachment_id=:ei_doc_id'
            );
            $s->execute(
                [
                    'ei_doc_id' => $d->ei_doc_id,
                ]
            );
            $s = $this->PDO->prepare(
                'UPDATE ei_subject_attachment set ei_subject_id=:ei_subject_id where ei_attachment_id=:ei_doc_id'
            );
            $s->execute(
                [
                    'ei_doc_id' => $d->ei_doc_id,
                    'ei_subject_id' => $d->ei_subject_id
                ]
            );

            
        }

        $this->callClass(
            "Subject", 
            "addSubjectAudit", 
            [
                'ei_subject_id' => $d->ei_subject_id,
                'ei_description' => "added a document in attachment",
                'element_type' => "DOC",
                'element_id' => $d->ei_doc_id,
                'label' => $content_doc['doc_name'],
                'action' => "ADD"

            ]
        );

        return true;
    }

    /**
     * Ajouter un lien dans les attachment du subject
     * 
     * @return array
     */
    function addLinkInAttachment()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int',
                'ei_doc_id' => 'int'
            ]
        );
        // Récupération du nom de la doc
        // error_log($d->ei_subject_id);
        $s = $this->PDO->prepare(
            'SELECT ei_doc_id, doc_name, link from ei_doc where ei_doc_id=:ei_doc_id'
        );
        $s->execute(
            [
                'ei_doc_id' => $d->ei_doc_id
            ]
        );
        $content_doc = $s->fetch(PDO::FETCH_ASSOC);

        // verfier qu'il y ai qu'une version
        $s = $this->PDO->prepare(
            'SELECT count(*) from ei_attachment WHERE ei_attachment_id=:ei_doc_id'
        );
        $s->execute(
            [
                'ei_doc_id' => $d->ei_doc_id
            ]
        );

        $count_doc = (int)($s->fetch()?:[0])[0];
        if ($count_doc == 0) {
            $s = $this->PDO->prepare(
                'INSERT into ei_attachment(ei_attachment_id, ei_attachment_name, ei_url_attachment, ei_attachment_type, creator_id, created_at, ei_attachment_show) values(:ei_doc_id,:doc_name,:link,"klf/link",:ei_user_id,NOW(),"Y")'
            );
            $s->execute(
                [
                    'ei_doc_id' => $d->ei_doc_id,
                    'doc_name' => $content_doc['doc_name'],
                    'link' => $content_doc['link'],
                    'ei_user_id' => $this->user['ei_user_id']
                ]
            );

            $s = $this->PDO->prepare(
                'INSERT into ei_subject_attachment(ei_attachment_id, ei_subject_id) values (:ei_doc_id,:ei_subject_id)'
            );
            $s->execute(
                [
                    'ei_doc_id' => $d->ei_doc_id,
                    'ei_subject_id' => $d->ei_subject_id
                ]
            );
        } else {
            $s = $this->PDO->prepare(
                'UPDATE  ei_attachment set ei_attachment_show="Y" where ei_attachment_id=:ei_doc_id'
            );
            $s->execute(
                [
                    'ei_doc_id' => $d->ei_doc_id,
                ]
            );
            $s = $this->PDO->prepare(
                'UPDATE ei_subject_attachment set ei_subject_id=:ei_subject_id where ei_attachment_id=:ei_doc_id'
            );
            $s->execute(
                [
                    'ei_doc_id' => $d->ei_doc_id,
                    'ei_subject_id' => $d->ei_subject_id
                ]
            );

            
        }

        $this->callClass(
            "Subject", 
            "addSubjectAudit", 
            [
                'ei_subject_id' => $d->ei_subject_id,
                'ei_description' => "added a link in attachment",
                'element_type' => "LINK",
                'element_id' => $d->ei_doc_id,
                'label' => $content_doc['doc_name'],
                'action' => "ADD"

            ]
        );

        return true;
    }

    /**
     * Ajouter une attachment sur un subject
     * 
     * @return array
     */
    function addSubjectsAttachment()
    {
        $d = $this->checkParams(
            [
                'ei_attachment_id' => 'int',
                'ei_attachment_name' => 'string',
                'ei_url_attachment' => 'string',
                'ei_attachment_type' => 'string',
                'ei_subject_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'INSERT into ei_attachment(ei_attachment_id, ei_attachment_name, ei_url_attachment, ei_attachment_type, creator_id, created_at, ei_attachment_show) 
            values(:ei_attachment_id, :ei_attachment_name, :ei_url_attachment, :ei_attachment_type, :ei_user_id, NOW(), "Y")'
        );
        $s->execute(
            [
                'ei_attachment_id' => $d->ei_attachment_id,
                'ei_attachment_name' => $d->ei_attachment_name,
                'ei_url_attachment' => $d->ei_url_attachment,
                'ei_attachment_type' => $d->ei_attachment_type,
                'ei_user_id' => $this->user['ei_user_id']
            ]
        );

        $s = $this->PDO->prepare(
            'INSERT into ei_subject_attachment(ei_attachment_id, ei_subject_id) values(:ei_attachment_id,:ei_subject_id)'
        );
        $s->execute(
            [
                'ei_attachment_id' => $d->ei_attachment_id,
                'ei_subject_id' => $d->ei_subject_id
            ]
        );

        $this->callClass(
            "Subject", 
            "addSubjectAudit", 
            [
                'ei_subject_id' => $d->ei_subject_id,
                'ei_description' => "added an attchment",
                'element_type' => "ATTACHMENT",
                'element_id' => $d->ei_attachment_id,
                'label' => $d->ei_attachment_name,
                'action' => "ADD"

            ]
        );

        return true;
    }

    /**
     * Création d'un nouveau deploymentstep
     * 
     * @return array
     */
    function addSubjectDeploymentstep()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int',
                'type' => 'int',
                'deployment_phase' => 'string',
                'description' => 'html',
                'status' => 'string',
                'deployment_order' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'INSERT into ei_subject_deploymentstep(ei_subject_id,ref_deploymentstep_type_id,deployment_action,deployment_status,deployment_phase,deployment_order) values (:ei_subject_id,:type,:description,:status,:deployment_phase,:deployment_order)'
        );
        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id,
                'type' => $d->type,
                'deployment_phase' => $d->deployment_phase,
                'description' => $d->description,
                'status' => $d->status,
                'deployment_order' => $d->deployment_order
            ]
        );

        $this->callClass(
            "Subject", 
            "addSubjectAudit", 
            [
                'ei_subject_id' => $d->ei_subject_id,
                'ei_description' => "added a deployment step",
                'element_type' => "DEPLOYMENT",
                'element_id' => $d->ei_subject_id,
                'label' => '',
                'action' => "ADD"

            ]
        );

        return true;
    }

    /**
     * Ajouter un attachment sur un subject deploymentstep
     * 
     * @return array
     */
    function addSubjectDeploymentStepsAttachment()
    {
        $d = $this->checkParams(
            [
                'ei_attachment_id' => 'int',
                'ei_attachment_name' => 'string',
                'ei_attachment_type' => 'string',
                'ei_subject_id' => 'int',
                'ei_subject_deploymentstep_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'INSERT into ei_attachment(ei_attachment_id, ei_attachment_name, ei_attachment_type, creator_id, created_at, ei_attachment_show) values (:ei_attachment_id, :ei_attachment_name, :ei_attachment_type, :ei_user_id, NOW(), "Y")'
        );
        $s->execute(
            [
                'ei_attachment_id' => $d->ei_attachment_id,
                'ei_attachment_name' => $d->ei_attachment_name,
                'ei_attachment_type' => $d->ei_attachment_type,
                'ei_user_id' => $this->user['ei_user_id']
            ]
        );

        $s = $this->PDO->prepare(
            'INSERT into ei_subject_deploymentstep_attachment(ei_attachment_id,ei_subject_deploymentstep_id, ei_subject_id) values (:ei_attachment_id,:ei_subject_deploymentstep_id,:ei_subject_id)'
        );
        $s->execute(
            [
                'ei_attachment_id' => $d->ei_attachment_id,
                'ei_subject_deploymentstep_id' => $d->ei_subject_deploymentstep_id,
                'ei_subject_id' => $d->ei_subject_id
            ]
        );


        $this->callClass(
            "Subject", 
            "addSubjectAudit", 
            [
                'ei_subject_id' => $d->ei_subject_id,
                'ei_description' => "added an attachment in deplyoment step",
                'element_type' => "ATTACHMENT",
                'element_id' => $d->ei_attachment_id,
                'label' => $d->ei_attachment_name,
                'action' => "ADD"

            ]
        );

        return true;
    }

    /**
     * Ajouter une fonction en risk sur un subject
     * 
     * @return array
     */
    function addSubjectsRisk()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int',
                'ei_function_id' => 'int'
            ]
        );

        // On vérifie que le risque n'existe pas déjà dans la table ei_subject_risk
        $s = $this->PDO->prepare(
            'SELECT count(*) from ei_subject_risk where ei_subject_id=:ei_subject_id and ei_function_id=:ei_function_id'
        );
        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id,
                'ei_function_id' => $d->ei_function_id
            ]
        );
        $count_risk = (int)($s->fetch()?:[0])[0];

        if ($count_risk == 0) {
            $s = $this->PDO->prepare(
                'INSERT into ei_subject_risk(ei_subject_id, ei_function_id, risk_type, created_by, created_at) 
                values(:ei_subject_id, :ei_function_id, "manual", :ei_user_id, NOW())'
            );
            $s->execute(
                [
                    'ei_subject_id' => $d->ei_subject_id,
                    'ei_function_id' => $d->ei_function_id,
                    'ei_user_id' => $this->user['ei_user_id'],
                ]
            );

            $s = $this->PDO->prepare(
                'SELECT default_techno_id FROM ei_function where ei_function_id=:ei_function_id;'
            );
            $s->execute(
                [
                    'ei_function_id' => $d->ei_function_id
                ]
            );

            $default_techno = $s->fetch();
            // error_log(json_encode($this->user['current_environment_id']));

            $s = $this->PDO->prepare(
                'INSERT INTO `ei_function_code_techno_environment` (`ei_environment_id`, `ei_function_id`, `ref_techno_id`, `effective_date`, `ei_subject_id`, `creator_id`, `created_at`) 
                VALUES (:env_id, :ei_function_id, :default_techno, now(), :ei_subject_id,:ei_user_id, now());
                '
            );
            $s->execute(
                [
                    'ei_subject_id' => $d->ei_subject_id,
                    'ei_function_id' => $d->ei_function_id,
                    'env_id' => $this->user['current_environment_id'],
                    'default_techno' => $default_techno['default_techno_id'],
                    'ei_user_id' => $this->user['ei_user_id']
                ]
            );

            $s = $this->PDO->prepare(
                'INSERT INTO `ei_function_code` (`ei_function_id`, `ei_subject_id`, `ref_techno_id`) 
                VALUES (:ei_function_id, :ei_subject_id, :default_techno);'
            );
            $s->execute(
                [
                    'ei_subject_id' => $d->ei_subject_id,
                    'ei_function_id' => $d->ei_function_id,
                    'default_techno' => $default_techno['default_techno_id'],
                ]
            );
        }

        $this->callClass(
            "Subject", 
            "addSubjectAudit", 
            [
                'ei_subject_id' => $d->ei_subject_id,
                'ei_description' => "added an impact",
                'element_type' => "IMPACT",
                'element_id' => $d->ei_function_id,
                'label' => '',
                'action' => "ADD"

            ]
        );


        return true;
    }


    /**
     * Add favorite subject filter
     * 
     * @return array
     */
    function addFavoriteSubjectFilter()
    {
        $d = $this->checkParams(
            [
                'ei_subject_filter_name' => 'string',
                'ei_subject_filter_url' => 'string',
            ]
        );

        $s = $this->PDO->prepare(
            "SELECT count(1) from ei_subject_filter where ei_subject_filter_name=:ei_subject_filter_name and ei_user_id=:ei_user_id"
        );
        $s->execute(
            [
                'ei_subject_filter_name' => $d->ei_subject_filter_name,
                'ei_user_id' => $this->user['ei_user_id']
            ]
        );

        $FavoriteNameAlreadyExist = $s->fetch()[0];
        if (!$FavoriteNameAlreadyExist) {
            $s = $this->PDO->prepare(
                "INSERT INTO `ei_subject_filter` 
                (`ei_user_id`, 
                `ei_subject_filter_name`, 
                `ei_subject_filter_url`) 
                VALUES ( 
                :ei_user_id, 
                :ei_subject_filter_name, 
                :ei_subject_filter_url);"
            );
            $s->execute(
                [
                    'ei_subject_filter_name' => $d->ei_subject_filter_name,
                    'ei_subject_filter_url' => $d->ei_subject_filter_url,
                    'ei_user_id' => $this->user['ei_user_id']
                ]
            );
        } else {
            $s = $this->PDO->prepare(
                "SELECT * from `ei_subject_filter` WHERE `ei_subject_filter_name`=:ei_subject_filter_name and ei_user_id=:ei_user_id;"
            );
            $s->execute(
                [
                    'ei_subject_filter_name' => $d->ei_subject_filter_name,
                    'ei_user_id' => $this->user['ei_user_id']
                ]
            );
            $rowvalue = $s->fetchAll(PDO::FETCH_ASSOC);

            if ($rowvalue) {
                $s = $this->PDO->prepare(
                    "UPDATE `ei_subject_filter` SET `ei_subject_filter_url`=:ei_subject_filter_url WHERE  ei_subject_filter_id=:ei_subject_filter_id"
                );
                $s->execute(
                    [
                        'ei_subject_filter_url' => $d->ei_subject_filter_url,
                        'ei_subject_filter_id' => $rowvalue[0]['ei_subject_filter_id']
                    ]
                ); 
            }
            
        }


        return true;
    }

    /**
     * Supprimer attachment sur les subjects
     * 
     * @return array
     */
    function deleteSubjectAttachment()
    {
        $d = $this->checkParams(
            [
                'ei_attachment_id' => 'int',
                'ei_subject_id' => 'int'
            ]
        );


        $s = $this->PDO->prepare(
            'DELETE FROM ei_attachment  where ei_attachment_id=:ei_attachment_id ;'
        );
        $s->execute(
            [
                'ei_attachment_id' => $d->ei_attachment_id
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT * from ei_attachment  where ei_attachment_id=:ei_attachment_id ;'
        );
        $s->execute(
            [
                'ei_attachment_id' => $d->ei_attachment_id
            ]
        );

        $attachment_deployment = $s->fetchAll(PDO::FETCH_ASSOC);
        $this->setData($attachment_deployment);

        return true;
    }


    // /**
    //  * Supprimer attachment sur les subjects
    //  * 
    //  * @return array
    //  */
    // function deleteSubjectCampaign()
    // {
    //     $d = $this->checkParams(
    //         [
    //             'ei_subject_id' => 'int',
    //             'ei_subject_campaign_id' => 'int',
    //             'ei_subject_campaigntype_id' => 'int'
    //         ]
    //     );

    //     $s = $this->PDO->prepare(
    //         'DELETE FROM `ei_subject_campaign` WHERE `ei_subject_id`=:ei_subject_id 
    //         and`ei_subject_campaign_id`=:ei_subject_campaign_id 
    //         and`ei_subject_campaigntype_id`=:ei_subject_campaigntype_id'
    //     );

    //     $s->execute(
    //         [
    //             'ei_subject_id' => $d->ei_subject_id,
    //             'ei_subject_campaign_id' => $d->ei_subject_campaign_id,
    //             'ei_subject_campaigntype_id' => $d->ei_subject_campaigntype_id,
    //         ]
    //     );

    //     return true;
    // }

    /**
     * Recuperer les attachment sur un subject deploymentstep
     * 
     * @return array
     */
    function getDeploymenstepAttachment()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int',
                'ei_subject_deploymentstep_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT d.ei_subject_id, d.ei_attachment_id, a.ei_attachment_id, a.ei_attachment_name, a.ei_attachment_type, a.creator_id, a.ei_attachment_show, d.ei_subject_deploymentstep_id
            FROM   ei_attachment a, ei_subject_deploymentstep_attachment d  where d.ei_subject_id =:ei_subject_id
            and d.ei_attachment_id = a.ei_attachment_id  and d.ei_subject_deploymentstep_id=:ei_subject_deploymentstep_id'
        );
        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id,
                'ei_subject_deploymentstep_id' => $d->ei_subject_deploymentstep_id
            ]
        );

        $attachment_deployment = $s->fetchAll(PDO::FETCH_ASSOC);
        $this->setData($attachment_deployment);

        return true;
    }

    /**
     * Recuperer les attachment sur un subject
     * 
     * @return array
     */
    function getSubjectsAttachment()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT s.ei_subject_id, s.ei_attachment_id, a.ei_attachment_id, a.ei_attachment_name, a.ei_url_attachment, a.ei_attachment_type, a.creator_id, a.ei_attachment_show
            FROM  ei_subject_attachment s,  ei_attachment a  where s.ei_subject_id =:ei_subject_id and s.ei_attachment_id = a.ei_attachment_id and a.ei_attachment_show="Y";'
        );
        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id
            ]
        );

        $attachment_list = $s->fetchAll(PDO::FETCH_ASSOC);
        $this->setData($attachment_list);

        return true;
    }

    /**
     * Récupération des file_brick relier à un subject
     * 
     * @return true
     */
    function getSubjectConnectedFileBrick()
    {
        $d = $this->checkParams(
                [
                'ei_subject_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT 
                egrsb.*,
                ef.ei_function_id,
                egfbf.*,
                egfb.*,
                egrscgb.*,
                egrsc.commit_user_id,
                eu.picture_path
            FROM
                ei_function ef
                    INNER JOIN
                ei_git_file_brick_function egfbf ON egfbf.ei_function_id = ef.ei_function_id
                    INNER JOIN
                ei_git_file_brick egfb ON egfb.ei_git_brick_id = egfbf.ei_git_brick_id
                    INNER JOIN
                ei_git_repo_subject_commit_git_brick egrscgb ON egrscgb.ei_git_repo_id = egfb.ei_git_repo_id
                    AND egrscgb.ei_git_brick_id = egfb.ei_git_brick_id
                    INNER JOIN
                ei_git_repo_subject_commit egrsc ON egrscgb.ei_commit_id = egrsc.ei_commit_id
                    INNER JOIN
                ei_git_repo_subject_branch egrsb ON egrsb.ei_git_repo_subject_branch_id = egrsc.ei_git_repo_subject_branch_id
                    INNER JOIN
                ei_user eu ON eu.ei_user_id = egrsc.commit_user_id
            WHERE
                egrsb.ei_subject_id = :ei_subject_id
            GROUP BY egfb.ei_git_brick_id
            '
        );
        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id
            ]
        );
        $subjectConnectedFileBrick = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($subjectConnectedFileBrick);
        return true;
    }

    /**
     * Récupération des Campagne d'un subject
     * 
     * @return array
     */
    function getSubjectCampaign()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int'
            ]
        );
        // On récupère la liste des campagnes
        $s = $this->PDO->prepare(
            'SELECT 
                esc.*, ecv.*
            FROM
                ei_subject_campaign esc
                    LEFT OUTER JOIN
                ei_campaign_version ecv ON ecv.ei_campaign_id = esc.ei_subject_campaign_id
                    AND (SELECT 
                        MAX(ei_campaign_version_id)
                    FROM
                        ei_campaign_version
                    WHERE
                        ei_campaign_id = esc.ei_subject_campaign_id) = ecv.ei_campaign_version_id
            WHERE
                esc.ei_subject_id = :ei_subject_id'
        );
        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id
            ]
        );
        $subject_campaign = $s->fetchAll(PDO::FETCH_ASSOC);

        foreach ($subject_campaign as $index =>  $campaign) {
            $s = $this->PDO->prepare(
                'SELECT ecs.* from ei_campaign_step ecs where ei_campaign_id=:ei_campaign_id and (SELECT 
                    MAX(ecv.ei_campaign_version_id)
                FROM
                    ei_campaign_version ecv
                WHERE
                    ecv.ei_campaign_id = ecs.ei_campaign_id) = ecs.ei_campaign_version_id order by ei_campaign_step_order'
            );
            $s->execute(
                [
                    'ei_campaign_id' => $campaign['ei_subject_campaign_id']
                ]
            );
            $subject_campaign_step = $s->fetchAll(PDO::FETCH_ASSOC);
            $campaign['campaign_step'] = $subject_campaign_step;
            $subject_campaign[$index] = $campaign;
            
        }
        $this->setData($subject_campaign);

        return true;
    }

    /**
     * Transformation de la version max en version 0
     * 
     * @return array
     */
    function getCampaignStep()
    {
        $d = $this->checkParams(
            [
                'ei_campaign_id'=> 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT ecs.* from ei_campaign_step ecs where ei_campaign_id=:ei_campaign_id and (SELECT 
                MAX(ecv.ei_campaign_version_id)
            FROM
                ei_campaign_version ecv
            WHERE
                ecv.ei_campaign_id = ecs.ei_campaign_id) = ecs.ei_campaign_version_id order by ei_campaign_step_order'
        );
        $s->execute(
            [
                'ei_campaign_id' => $d->ei_campaign_id
            ]
        );
        $subject_campaign_step = $s->fetchAll(PDO::FETCH_ASSOC);
        $this->setData($subject_campaign_step);

        return true;
    }



    /**
     * Transformation de la version max en version 0
     * 
     * @return array
     */
    function transformMaxVersionToVersionZero()
    {
        $d = $this->checkParams(
            [
                'campaign_id'=> 'int'
            ]
        );

            $s = $this->PDO->prepare(
                "INSERT INTO ei_campaign_step (ei_campaign_id,ei_campaign_version_id, ei_campaign_step_id, ei_campaign_step_order,ei_campaign_step_type,ei_campaign_step_scenario_id,ei_campaign_step_dataset_id,ei_campaign_step_text)
                SELECT ei_campaign_id,0, ei_campaign_step_id,ei_campaign_step_order,ei_campaign_step_type,ei_campaign_step_scenario_id,ei_campaign_step_dataset_id,ei_campaign_step_text
                FROM ei_campaign_step
                WHERE ei_campaign_id=:ei_campaign_id and (SELECT max(ei_campaign_version_id) from ei_campaign_version where `ei_campaign_id`=:ei_campaign_id) = ei_campaign_version_id;"
            );
            $s->execute(
                [
                    'ei_campaign_id' => $d->campaign_id
                ]
            );
    }

    /**
     * Delete de la  version 0
     * 
     * @return array
     */
    function unSaveCampaignVersionZero()
    {
        $d = $this->checkParams(
            [
                'campaign_id'=> 'int'
            ]
        );

            $s = $this->PDO->prepare(
                "DELETE FROM `ei_campaign_step` WHERE `ei_campaign_id`=:ei_campaign_id and`ei_campaign_version_id`='0' ;"
            );
            $s->execute(
                [
                    'ei_campaign_id' => $d->campaign_id
                ]
            );
    }

    /**
     * Delete de la  campagne
     * 
     * @return array
     */
    function deleteCampaign()
    {
        $d = $this->checkParams(
            [
                'campaign_id'=> 'int'
            ]
        );

        $s = $this->PDO->prepare(
            "DELETE FROM `ei_campaign_step` WHERE `ei_campaign_id`=:ei_campaign_id;"
        );
        $s->execute(
            [
                'ei_campaign_id' => $d->campaign_id
            ]
        );

        $s = $this->PDO->prepare(
            "DELETE FROM `ei_campaign` WHERE `ei_campaign_id`=:ei_campaign_id;"
        );
        $s->execute(
            [
                'ei_campaign_id' => $d->campaign_id
            ]
        );

        $s = $this->PDO->prepare(
            "DELETE FROM `ei_subject_campaign` WHERE `ei_subject_campaign_id`=:ei_campaign_id;"
        );
        $s->execute(
            [
                'ei_campaign_id' => $d->campaign_id
            ]
        );

        $s = $this->PDO->prepare(
            "DELETE FROM `ei_campaign_plane` WHERE `ei_campaign_id`=:ei_campaign_id;"
        );
        $s->execute(
            [
                'ei_campaign_id' => $d->campaign_id
            ]
        );

        $s = $this->PDO->prepare(
            "DELETE FROM `ei_campaign_step_plane` WHERE `ei_campaign_id`=:ei_campaign_id;"
        );
        $s->execute(
            [
                'ei_campaign_id' => $d->campaign_id
            ]
        );

        $s = $this->PDO->prepare(
            "DELETE FROM `ei_campaign_version` WHERE `ei_campaign_id`=:ei_campaign_id;"
        );
        $s->execute(
            [
                'ei_campaign_id' => $d->campaign_id
            ]
        );
    }

    /**
     * Delete d'un step campagne
     * 
     * @return array
     */
    function deleteCampaignStep()
    {
        $d = $this->checkParams(
            [
                'campaign_id'=> 'int'
                ,'ei_campaign_step_id'=> 'int'
            ]
        );

        $s = $this->PDO->prepare(
            "DELETE FROM `ei_campaign_step` WHERE `ei_campaign_id`=:ei_campaign_id and`ei_campaign_step_id`=:ei_campaign_step_id;"
        );
        $s->execute(
            [
                'ei_campaign_id' => $d->campaign_id,
                'ei_campaign_step_id' => $d->ei_campaign_step_id
            ]
        );
    }

    /**
     * Duppliquer un filter 
     * 
     * @return array
     */
    function duplicateFilter()
    {
        $d = $this->checkParams(
            [
                'ei_subject_filter_id'=> 'int'
            ]
        );

        $s = $this->PDO->prepare(
            "INSERT INTO ei_subject_filter ( ei_user_id, ei_subject_filter_name, ei_subject_filter_url)
            select ei_user_id, concat(ei_subject_filter_name,'(2)'),ei_subject_filter_url FROM ei_subject_filter  where ei_subject_filter_id=:ei_subject_filter_id;"
        );
        $s->execute(
            [
                'ei_subject_filter_id' => $d->ei_subject_filter_id
            ]
        );
    }

    /**
     * Update favorite subject Filter
     * 
     * @return array
     */
    function updateFavoriteSubjectFiltervalue()
    {
        $d = $this->checkParams(
            [
                'ei_subject_filter_url'=> 'string'
                ,'ei_subject_filter_id'=> 'int'
            ]
        );

        $s = $this->PDO->prepare(
            "UPDATE `ei_subject_filter` SET `ei_subject_filter_url`=:ei_subject_filter_url WHERE `ei_subject_filter_id`=:ei_subject_filter_id;"
        );
        $s->execute(
            [
                'ei_subject_filter_url' => $d->ei_subject_filter_url,
                'ei_subject_filter_id' => $d->ei_subject_filter_id
            ]
        );
    }

    /**
     * Récupération des Campagne d'un subject avec la version 0 
     * 
     * @return array
     */
    function getSubjectCampaignVersionzero()
    {
        $d = $this->checkParams(
            [
                'campaign_id'=> 'int'
            ]
        );

            $s = $this->PDO->prepare(
                "SELECT 
                    ecs.*,
                    (SELECT 
                            COUNT(1)
                        FROM
                            ei_scenario_tree
                        WHERE
                            ei_scenario_id = ecs.ei_campaign_step_scenario_id) AS exist_in_tree
                FROM
                    ei_campaign_step ecs
                WHERE
                    ei_campaign_id = :ei_campaign_id
                        AND ecs.ei_campaign_version_id = '0'
                ORDER BY ei_campaign_step_order;"
            );
            $s->execute(
                [
                    'ei_campaign_id' => $d->campaign_id
                ]
            );
            $subject_campaign_step = $s->fetchAll(PDO::FETCH_ASSOC);
            
        $this->setData($subject_campaign_step);

        return true;
    }

    /**
     * Save la version 0 des campaign sur les subject
     * 
     * @return array
     */
    function saveVersionZeroForCampaign()
    {
        $d = $this->checkParams(
            [
                'campaign_id'=> 'int'
            ]
        );

        $s = $this->PDO->prepare(
            "SELECT max(ei_campaign_version_id) from ei_campaign_step where `ei_campaign_id`=:campaign_id "
        );
        $s->execute(
            [
            'campaign_id' => $d->campaign_id
            ]
        );
        $max_campaign_version_id = (int)($s->fetch()?:[0])[0]+1;

        $s = $this->PDO->prepare(
            "SELECT ei_campaign_version_label from ei_campaign_version where ei_campaign_id=:ei_campaign_id and (SELECT max(ei_campaign_version_id) from ei_campaign_version where `ei_campaign_id`=:ei_campaign_id) = ei_campaign_version_id;"
        );
        $s->execute(
            [
                'ei_campaign_id' => $d->campaign_id,
            ]
        );
        $campaign_title = $s->fetch(PDO::FETCH_ASSOC);
        $s = $this->PDO->prepare(
            "INSERT INTO `ei_campaign_version` (`ei_campaign_id`, `ei_campaign_version_id`, `ei_campaign_version_label`, `ei_campaign_version_created_by`) 
            VALUES (:ei_campaign_id, :new_version_id, :campaign_title, :ei_user_id);"
        );
        $s->execute(
            [
                'ei_campaign_id' => $d->campaign_id,
                'new_version_id' => $max_campaign_version_id,
                'campaign_title' => $campaign_title['ei_campaign_version_label'],
                'ei_user_id' => $this->user['ei_user_id']
            ]
        );
        
        $s = $this->PDO->prepare(
            "UPDATE `ei_campaign_step` SET `ei_campaign_version_id`=:new_version_id WHERE `ei_campaign_id`=:ei_campaign_id and`ei_campaign_version_id`='0';"
        );
        $s->execute(
            [
                'ei_campaign_id' => $d->campaign_id,
                'new_version_id' => (int)$max_campaign_version_id
            ]
        );

        return true;
    }

    /**
     * Récupération des deploymentSteps par subject
     * 
     * @return array
     */
    function getSubjectDeploymentstep()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int'
            ]
        );
        // On récupère la liste des status d'intervention
        $s = $this->PDO->prepare(
            'SELECT d.ei_subject_id, d.ei_subject_deploymentstep_id, d.ref_deploymentstep_type_id, dt.deploymentstep_type_name, 
            d.deployment_action, d.deployment_status, d.deployment_phase, d.deployment_order, s.ei_subject_id, s.ei_delivery_id , s.title, edp.deploymentstep_phase_name
            FROM ei_subject_deploymentstep d
            left outer join ei_subject s
            on d.ei_subject_id=s.ei_subject_id
            left outer join ei_deploymentstep_phase edp
            on d.deployment_phase=edp.deploymentstep_phase_id
            left outer join ref_deploymentstep_type dt
            on d.ref_deploymentstep_type_id=dt.ref_deploymentstep_type_id
            where d.ei_subject_id=:ei_subject_id and s.ei_subject_id=:ei_subject_id and s.ei_subject_version_id=(select max(s2.ei_subject_version_id) from ei_subject s2
            where s2.ei_subject_id=s.ei_subject_id) order by edp.deploymentstep_phase_id, d.deployment_order asc'
        );
        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id
            ]
        );
        $subject_deploymentstep = $s->fetchAll(PDO::FETCH_ASSOC);
        $this->setData($subject_deploymentstep);

        return true;
    }

    /**
     * Récupération des risks
     * 
     * @return true
     */
    function getSubjectRisk()
    { 
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT esr.*, ef.function_name  FROM ei_subject_risk esr left outer join ei_function ef 
            on ef.ei_function_id = esr.ei_function_id where esr.ei_subject_id=:ei_subject_id order by created_at desc'
        );

        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id
            ]
        );


        $function_risk = $s->fetchAll(PDO::FETCH_ASSOC);

        foreach ($function_risk as $key => $value) {
            
            $obj = $this->callClass(
                "Functions", 
                "getPath", 
                [
                    'ei_function_id' =>$value['ei_function_id']
                ]
            );

            $path =$obj->getData();
            $value['path'] = array_reverse($path);
            // error_log(json_encode($value));
            $function_risk[$key] = $value;
        }
        
        // error_log(json_encode($function_risk));
        $this->setData($function_risk);

        return true;
    }


    /**
     * Récupération des status des patch note
     * 
     * @return true
     */
    function getSubjectPatchNoteStatus()
    { 
        $d = $this->checkParams(
            [
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT * FROM ref_patch_status;'
        );

        $s->execute(
            [
            ]
        );

        $patch_note_status = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($patch_note_status);

        return true;
    }

    /**
     * Récupération des types des patch note
     * 
     * @return true
     */
    function getSubjectPatchNoteTypes()
    { 
        $d = $this->checkParams(
            [
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT * FROM ref_patch_type;'
        );

        $s->execute(
            [
            ]
        );

        $patch_note_types = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($patch_note_types);

        return true;
    }

    /**
     * Verifier si une taches est en cour ou en new sur un subject
     * 
     * @return true
     */
    function getSubjectTaskStatus()
    { 
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            "SELECT count(*) as number
            FROM ei_task_link etl, ei_task et, ref_task_status rts where etl.ei_task_id=et.ei_task_id and rts.ref_task_status_id=et.ref_task_status_id 
            and etl.object_id=:ei_subject_id and (rts.is_new='Y' or rts.is_inprogress='Y') "
        );

        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id
            ]
        );

        $task_status_in_subject = (int)($s->fetch()?:[0])[0];

        $this->setData($task_status_in_subject);

        return true;
    }

    /**
     * Récupération de la liste des interventions liées à la delivery
     * 
     * @return array
     */
    function getSubjectListByDeliveryId()
    {
        $d = $this->checkParams(
            [
                'ei_delivery_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT s.ei_subject_id, s.title, p.ei_pool_id, p.pool_name, d.ei_delivery_id,
            d.delivery_name, st.ref_subject_type_id, st.type_name , st.type_icon,
            ss.ref_subject_status_id, ss.status_name, ss.color as status_color, ss.status_icon,
            sp.ref_subject_priority_id, sp.priority_name, sp.color as priority_color,sp.priority_picto,
            u.username, u.picture_path, u2.username as in_charge_username, u2.picture_path as in_charge_picture_path, s.created_at, DATEDIFF(Now(), s.created_at) as diff_days from ei_subject s 
            left outer join ei_pool p
            on s.ei_pool_id=p.ei_pool_id 
            left outer join ei_delivery d
            on s.ei_delivery_id=d.ei_delivery_id
            left outer join ref_subject_type st
            on s.ref_subject_type_id=st.ref_subject_type_id
            left outer join ref_subject_status ss
            on s.ref_subject_status_id=ss.ref_subject_status_id
            left outer join ref_subject_priority sp
            on s.ref_subject_priority_id=sp.ref_subject_priority_id
            left outer join ei_user u
            on s.creator_id=u.ei_user_id 
            left outer join ei_user u2
            on s.ei_subject_user_in_charge=u2.ei_user_id 
            where s.ei_subject_version_id=(select max(s2.ei_subject_version_id) 
            from ei_subject s2 where s2.ei_subject_id=s.ei_subject_id) and d.ei_delivery_id=:ei_delivery_id
            order by s.ei_subject_id desc'
        );
        $s->execute(
            [
                'ei_delivery_id' => $d->ei_delivery_id
            ]
        );
        $subject_list = $s->fetchAll(PDO::FETCH_ASSOC);
        $this->setData($subject_list);

        return true;
    }

    /**
     * Récupération de la liste des taches liées à l'intervention
     * 
     * @return array
     */
    function getSubjectTaskListByIdDisplayLine()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'json',
            ]
        );

        // error_log($d->ei_subject_id);
        // error_log('//////////////////');
        foreach ($d->ei_subject_id as $type => $value) {
            // error_log($type);
            // error_log('//////////');
            // error_log($value);
            foreach ($value as $key2 => $id_value) {
                // error_log($key2);
                // error_log('//////////');
                // error_log($id_value);
            }
        }
        if ($type === 'subject') {
            $s = $this->PDO->prepare(
                'SELECT distinct type_prefix,ref_task_type_id,task_type_name,task_type_color,task_type_order,ei_task_id,task_title,overwrite_expected_end,overwrite_expected_start,
                estimation,final_cost,ei_user_id,username,user_picture_path,ref_task_status_id,task_status_name,task_status_color,task_status_icon_class,
                task_status_order,task_status_is_new,task_status_is_inprogress,task_status_is_final,ref_subject_type_id,type_name,ei_subject_id,title,ei_pool_id,
                pool_name,ei_delivery_id,delivery_name,ref_subject_status_id,subject_status_name,subject_status_color,ref_subject_priority_id,
                subject_priority_name,subject_priority_color,created_at from ei_task_detail_vw where ei_subject_id=:subject_id AND connected_user_id = :connected_user_id'
            );
            $s->execute(
                [
                    'subject_id' => $id_value,
                    'connected_user_id' => $this->user['ei_user_id']
                ]
            );
            $subject_task_list = $s->fetchAll(PDO::FETCH_ASSOC);

            $this->setData($subject_task_list);
        } else if ($type === 'delivery') {
            $s = $this->PDO->prepare(
                'SELECT type_prefix,ref_task_type_id,task_type_name,task_type_color,task_type_order,ei_task_id,task_title,overwrite_expected_end,overwrite_expected_start,
                estimation,final_cost,ei_user_id,username,user_picture_path,ref_task_status_id,task_status_name,task_status_color,task_status_icon_class,
                task_status_order,task_status_is_new,task_status_is_inprogress,task_status_is_final,ref_subject_type_id,type_name,ei_subject_id,title,ei_pool_id,
                pool_name,ei_delivery_id,delivery_name,ref_subject_status_id,subject_status_name,subject_status_color,ref_subject_priority_id,
                subject_priority_name,subject_priority_color,created_at from ei_task_detail_vw where ei_delivery_id=:delivery_id AND  connected_user_id = :connected_user_id'
            );
            $s->execute(
                [
                    'delivery_id' => $id_value,
                    'connected_user_id' => $this->user['ei_user_id']
                ]
            );
            $subject_task_list = $s->fetchAll(PDO::FETCH_ASSOC);

            
        } else if ($type === 'iteration') {
            $s = $this->PDO->prepare(
                "SELECT distinct tv.* FROM ei_task_detail_vw tv , ei_execution_stack_step_link sl
                where sl.object_id = tv.ei_task_id
                and sl.object_type ='TASK'
                and sl.ei_iteration_id =:ei_iteration_id 
                AND tv.connected_user_id = :connected_user_id"
            );
            $s->execute(
                [
                    'ei_iteration_id' => $id_value,
                    'connected_user_id' => $this->user['ei_user_id']
                ]
            );
            $subject_task_list = $s->fetchAll(PDO::FETCH_ASSOC);

        } else if ($type === 'MyFilter') {
            $s = $this->PDO->prepare(
                'SELECT type_prefix,ref_task_type_id,task_type_name,task_type_color,task_type_order,ei_task_id,task_title,overwrite_expected_end,overwrite_expected_start, nb_calendardays_before_alert,alert_LATE ,alert_lastday,
                estimation,final_cost,ei_user_id, DATE_FORMAT(overwrite_expected_start, "%d %b") as expected_start_short, DATE_FORMAT(overwrite_expected_end, "%d %b") as expected_end_short, username,user_picture_path,ref_task_status_id,task_status_name,task_status_color,task_status_icon_class,
                task_status_order,task_status_is_new,task_status_is_inprogress,task_status_is_final,task_description,task_creator_username,ref_subject_type_id,type_name,ei_subject_id,title,ei_pool_id,
                pool_name,ei_delivery_id,delivery_name,ref_subject_status_id,subject_status_name,subject_status_color,ref_subject_priority_id,
                subject_priority_name,subject_priority_color,created_at, isread, read_dttm from ei_task_detail_vw where ei_user_id=:ei_user_id AND connected_user_id = :connected_user_id' 
            );
            // error_log($this->user['ei_user_id']);
            $s->execute(
                [
                    'ei_user_id' => $this->user['ei_user_id'],
                    'connected_user_id' => $this->user['ei_user_id']
                ]
            );
            $subject_task_list = $s->fetchAll(PDO::FETCH_ASSOC);

        }

        $this->setData($subject_task_list);

        return true;
    }

    /**
     * Récupération de la liste des taches liées à la delivery
     * 
     * @return array
     */
    function getSubjectTaskListByIdDisplayLineDeliveryDetails()
    {
        $d = $this->checkParams(
            [
                'ei_delivery_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT tdw.ei_task_id, tdw.ref_task_type_id, tdw.ei_subject_id, tdw.task_type_name, tdw.username,
            tdw.task_type_color , tdw.ref_task_status_id, tdw.user_picture_path,
            tdw.task_status_name, tdw.task_type_color, tdw.task_title ,
            tdw.task_description, tdw.ei_user_id, tdw.username, tdw.task_creator_picture_path, tdw.estimation, tdw.final_cost, tdw.overwrite_expected_start, tdw.overwrite_expected_end, tdw.task_status_icon_class
            from ei_task_detail_vw tdw where tdw.ei_delivery_id=:ei_delivery_id AND tdw.connected_user_id = :connected_user_id ;'
        );
        $s->execute(
            [
                'ei_delivery_id' => $d->ei_delivery_id,
                'connected_user_id' => $this->user['ei_user_id']
            ]
        );
        $subject_task_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($subject_task_list);

        return true;
    }

    /**
     * Récupération des type deploymentSteps 
     * 
     * @return array
     */
    function getSubjectTypeDeploymentstep()
    {
        $s = $this->PDO->prepare(
            'SELECT ref_deploymentstep_type_id, deploymentstep_type_name, deploymentstep_default_description FROM ref_deploymentstep_type'
        );
        $s->execute();
        
        $subject_deploymentstep_type = $s->fetchAll(PDO::FETCH_ASSOC);
        $this->setData($subject_deploymentstep_type);

        return true;
    }

    /**
     * Modification d'un deploymentstep
     * 
     * @return array
     */
    function updateSubjectDeploymentStep()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int',
                'deploymentId' => 'int',
                'type' => 'int',
                'deployment_phase' => 'string',
                'deployment_order' => 'int',
                'description' => 'html',
                'status' => 'string'
            ]
        );

        $s = $this->PDO->prepare(
            'UPDATE ei_subject_deploymentstep set ref_deploymentstep_type_id=:type, deployment_action=:description, 
            deployment_status=:status, deployment_phase=:phase, deployment_order=:order where ei_subject_id=:ei_subject_id and ei_subject_deploymentstep_id=:deploymentId'
        );
        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id,
                'deploymentId' => $d->deploymentId,
                'type' => $d->type,
                'description' => $d->description,
                'status' => $d->status,
                'phase' => $d->deployment_phase,
                'order' => $d->deployment_order
            ]
        );

        $this->callClass(
            "Subject", 
            "addSubjectAudit", 
            [
                'ei_subject_id' => $d->ei_subject_id,
                'ei_description' => "updated a deployment step",
                'element_type' => "DEPLOYMENT",
                'element_id' => $d->deploymentId,
                'label' => '',
                'action' => "UPDATE"

            ]
        );

        return true;
    }

    /**
     * Recuperer les Stats des scenario executer
     * 
     * @return array
     */
    function getScenarioStat()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            "SELECT  sl.ei_scenario_id, f.scenario_name, f.path, count(1) as nb_scenario FROM ei_execution_stack_step_log sl 
            inner join ei_scenario_detail_vw f on sl.ei_scenario_id=f.ei_scenario_id 

            where related_subject_id=:ei_subject_id 
            and sl.step_type ='SCENARIO'
            group by ei_scenario_id;"
        );
        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id
            ]
        );

        $scenariostat = $s->fetchAll(PDO::FETCH_ASSOC);
        $this->setData($scenariostat);

        return true;
    }

    /**
     * Recuperer les requirement rattacher a un subject
     * 
     * @return array
     */
    function getSubjectRequirement()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            "SELECT err.*,er.* from ei_requirement_risk  err
            inner join ei_requirement er on err.ei_requirement_id=er.ei_requirement_id
            where ei_subject_id=:ei_subject_id"
        );
        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id
            ]
        );

        $subjectrequirement = $s->fetchAll(PDO::FETCH_ASSOC);
        $this->setData($subjectrequirement);

        return true;
    }

    /**
     * Ajouter les requirement rattacher a un subject 
     * 
     * @return array
     */
    function addSubjectRequirement()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int',
                'ei_requirement_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            "INSERT IGNORE INTO `ei_requirement_risk` (`ei_requirement_id`, `ei_subject_id`, `risk_type`, `created_by`, `created_at`) VALUES (:ei_requirement_id, :ei_subject_id, 'manual', :ei_user_id, NOW());"
        );
        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id,
                'ei_requirement_id' => $d->ei_requirement_id,
                'ei_user_id' => $this->user['ei_user_id']
            ]
        );

        return true;
    }

    /**
     * Supprimer les requirement rattacher a un subject 
     * 
     * @return array
     */
    function deleteSubjectRequirement()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int',
                'ei_requirement_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            "DELETE FROM `ei_requirement_risk` WHERE `ei_requirement_id`=:ei_requirement_id and`ei_subject_id`=:ei_subject_id;"
        );
        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id,
                'ei_requirement_id' => $d->ei_requirement_id
            ]
        );

        return true;
    }

    /**
     * Recuperer les Stats des functions executer
     * 
     * @return array
     */
    function getFunctionStat()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT  sl.ei_function_id, f.function_name, f.path, count(1) as nb_function 
            FROM ei_execution_stack_step_log sl 
            inner join ei_function_detail_vw f 
                on sl.ei_function_id=f.ei_function_id 

            where related_subject_id=:ei_subject_id 
            group by ei_function_id;'
        );
        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id
            ]
        );

        $functionstat = $s->fetchAll(PDO::FETCH_ASSOC);
        $this->setData($functionstat);

        return true;
    }

    /**
     * Recuperer tout les scenarios ouvert sur un subject
     * 
     * @return array
     */
    function getSubjectScenarioOpen()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT count(*) as count FROM ei_scenario_version where ei_subject_id=:ei_subject_id and ei_api_application_module_mode_id="EDIT";'
        );

        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id
            ]
        );
        $SubjectScenarioOpen = $s->fetch(PDO::FETCH_ASSOC);

        if ($SubjectScenarioOpen['count'] >=1) {
            $s = $this->PDO->prepare(
                'SELECT * FROM ei_scenario_version where ei_subject_id=:ei_subject_id and ei_api_application_module_mode_id="EDIT";'
            );

            $s->execute(
                [
                    'ei_subject_id' => $d->ei_subject_id
                ]
            );
            $SubjectScenarioOpenInfos = $s->fetchAll(PDO::FETCH_ASSOC);  
        }
        
        $value['SubjectScenarioOpen'] = $SubjectScenarioOpen;

        if ($SubjectScenarioOpen['count'] >=1) {
            $value['SubjectScenarioOpenInfos'] = $SubjectScenarioOpenInfos;
        }
        $this->setData($value);

        return true;
    }

    /**
     * Recuperer tout les id des subjects
     * 
     * @return array
     */
    function getSubjectListId()
    {

        $s = $this->PDO->prepare(
            'SELECT s.ei_subject_id, s.ref_subject_status_id from ei_subject s where 
            s.ei_subject_version_id = (select max(s2.ei_subject_version_id) 
            from ei_subject s2 where s.ei_subject_id=s2.ei_subject_id) and 
            s.ref_subject_status_id = (select rss.ref_subject_status_id from ref_subject_status rss where s.ref_subject_status_id=rss.ref_subject_status_id 
            and rss.is_final="N");'
        );
        $s->execute();
        $SubjectListId = $s->fetchAll(PDO::FETCH_ASSOC);
        $this->setData($SubjectListId);

        return true;
    }

    /**
     * Recuperer tout les commentaires d'un subject
     * 
     * @return array
     */
    function getSubjectComment()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT 
                esc.*,NOW()-esc.ei_subject_comment_datetime as since_sec, eu.username, eu.picture_path
            FROM
                ei_subject_comment esc
                    LEFT OUTER JOIN
                ei_user eu ON esc.author_id = eu.ei_user_id
            WHERE
                ei_subject_id =:ei_subject_id
                AND esc.ei_subject_comment_id_reply IS NULL
                OR esc.ei_subject_comment_id_reply = 0;'
        );

        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id
            ]
        );

        $SubjectComment = $s->fetchAll(PDO::FETCH_ASSOC);
        $this->setData($SubjectComment);

        return true;
    }

    /**
     * Recuperer tout les commentaires d'un subject
     * 
     * @return array
     */
    function getSubjectCommentLine()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int',
                'ei_subject_comment_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT 
                esc.*,NOW()-esc.ei_subject_comment_datetime as since_sec, eu.username, eu.picture_path
            FROM
                ei_subject_comment esc
                    LEFT OUTER JOIN
                ei_user eu ON esc.author_id = eu.ei_user_id
            WHERE
                ei_subject_id = :ei_subject_id
                    AND esc.ei_subject_comment_id_reply=:ei_subject_comment_id'
        );

        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id,
                'ei_subject_comment_id' => $d->ei_subject_comment_id
            ]
        );

        $SubjectComment = $s->fetchAll(PDO::FETCH_ASSOC);
        $this->setData($SubjectComment);

        return true;
    }

    /**
     * Delete un commentaire 
     * 
     * @return array
     */
    function deleteSubjectCommentLine()
    {
        $d = $this->checkParams(
            [
                'ei_subject_comment_id' => 'int'
            ]
        );
        
        $s = $this->PDO->prepare(
            "DELETE FROM `ei_subject_comment` WHERE `ei_subject_comment_id`=:ei_subject_comment_id"
        );

        $s->execute(
            [
                'ei_subject_comment_id' => $d->ei_subject_comment_id
            ]
        );


        return true;
    }

    /**
     * Update un commentaire 
     * 
     * @return array
     */
    function updateSubjectCommentLine()
    {
        $d = $this->checkParams(
            [
                'ei_subject_comment_id' => 'int',
                'message' => 'string'
            ]
        );

        $s = $this->PDO->prepare(
            "UPDATE `ei_subject_comment` 
            SET 
                `message` = :message
            WHERE
                `ei_subject_comment_id` = :ei_subject_comment_id"
        );

        $s->execute(
            [
                'ei_subject_comment_id' => $d->ei_subject_comment_id,
                'message' => $d->message
            ]
        );


        return true;
    }

    /**
     * Ajouter un commentaire sur les subject
     * 
     * @return array
     */
    function addSubjectComment()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int',
                'ei_user_id_tagged' => 'int',
                'ei_username_tagged' => 'string',
                'comment_data' => 'html',
                'ei_subject_comment_id_reply' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT max(ei_subject_comment_id)+1 FROM ei_subject_comment'
        );
        $s->execute();
        $subject_comment_id = (int)($s->fetch()?:[0])[0];

        if ($subject_comment_id == 0) {
            $subject_comment_id = 1;
        }

        $s = $this->PDO->prepare(
            'INSERT INTO `ei_subject_comment` (`ei_subject_comment_id`,`ei_subject_id`, `message`, `author_id`,`ei_subject_comment_ei_user_id_tagged`,`ei_subject_comment_ei_username_tagged`,`ei_subject_comment_id_reply`) VALUES (:ei_subject_comment_id,:ei_subject_id, :comment_data, :ei_user_id,:ei_user_id_tagged,:ei_username_tagged,:ei_subject_comment_id_reply);'
        );

        $s->execute(
            [
                'ei_subject_comment_id' => $subject_comment_id,
                'ei_subject_id' => $d->ei_subject_id,
                'comment_data' => $d->comment_data,
                'ei_user_id_tagged' => $d->ei_user_id_tagged,
                'ei_username_tagged' => $d->ei_username_tagged,
                'ei_subject_comment_id_reply' => $d->ei_subject_comment_id_reply,
                'ei_user_id' => $this->user['ei_user_id']
            ]
        );

        return true;
    }

    /**
     * Recuperer tout les thread d'un subject
     * 
     * @return array
     */
    function getSubjectThread()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT * FROM ei_subject_thread where ei_subject_id=:ei_subject_id'
        );

        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id
            ]
        );

        $SubjectThread = $s->fetchAll(PDO::FETCH_ASSOC);

        foreach ($SubjectThread as $key => $value) {
            // error_log($value['ei_subject_thread_id']);
            if ($value && $value['ei_subject_thread_id']) {
                $thread_user = $this->callClass(
                    "Subject", 
                    "getSubjectThreadUserAssign", 
                    [
                        'ei_subject_thread_id' => $value['ei_subject_thread_id'],
                    ]
                );
                $SubjectThread[$key]['thread_user'] = $thread_user->getdata();;
            }
            
        }
        

        $this->setData($SubjectThread);

        return true;
    }

    /**
     * Recuperer tout les user assigner a un thread
     * 
     * @return array
     */
    function getSubjectThreadUserAssign()
    {
        $d = $this->checkParams(
            [
                'ei_subject_thread_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT * FROM ei_subject_thread_user estu inner join ei_user eu on eu.ei_user_id=estu.ei_subject_thread_user_id where estu.ei_subject_thread_id=:ei_subject_thread_id'
        );

        $s->execute(
            [
                'ei_subject_thread_id' => $d->ei_subject_thread_id
            ]
        );

        $SubjectThreadUserAssign = $s->fetchAll(PDO::FETCH_ASSOC);

        

        $this->setData($SubjectThreadUserAssign);

        return true;
    }

    /**
     * Recuperer tout les commentaires d'un thread
     * 
     * @return array
     */
    function getSubjectThreadComment()
    {
        $d = $this->checkParams(
            [
                'ei_subject_thread_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT *
            FROM (
                    (SELECT estu2.*,
                            eu.*,
                            NOW() - estu2.ei_subject_thread_comment_datetime AS since_sec
                    FROM ei_subject_thread_comment estu2
                    INNER JOIN ei_user eu ON eu.ei_user_id = estu2.ei_subject_thread_author_id
                    LEFT OUTER JOIN ei_subject_thread_comment_read_by estcrb2 ON estcrb2.ei_subject_thread_comment_id = estu2.ei_subject_thread_comment_id
                    AND estcrb2.ei_subject_thread_user_id = :ei_subject_thread_user_id
                    WHERE ei_subject_thread_id = :ei_subject_thread_id
                    AND estcrb2.ei_subject_thread_user_id IS NOT NULL
                    ORDER BY since_sec ASC
                    LIMIT 1)
                UNION ALL
                    (SELECT estu.*,
                            eu.*,
                            NOW() - estu.ei_subject_thread_comment_datetime AS since_sec
                    FROM ei_subject_thread_comment estu
                    INNER JOIN ei_user eu ON eu.ei_user_id = estu.ei_subject_thread_author_id
                    LEFT OUTER JOIN ei_subject_thread_comment_read_by estcrb ON estcrb.ei_subject_thread_comment_id = estu.ei_subject_thread_comment_id
                    AND estcrb.ei_subject_thread_user_id = :ei_subject_thread_user_id
                    WHERE ei_subject_thread_id = :ei_subject_thread_id
                    AND estcrb.ei_subject_thread_user_id IS NULL)) AS donne '
        );

        $s->execute(
            [
                'ei_subject_thread_id' => $d->ei_subject_thread_id,
                'ei_subject_thread_user_id' => $this->user['ei_user_id'],
            ]
        );

        $getSubjectThreadComment = $s->fetchAll(PDO::FETCH_ASSOC);

        foreach ($getSubjectThreadComment as $key => $value) {
            if ($value && $value['ei_subject_thread_comment_id_reply']) {
                $commentDate= '';
                $s = $this->PDO->prepare(
                    'SELECT estc.*, eu.username , NOW()-estc.ei_subject_thread_comment_datetime as since_sec FROM ei_subject_thread_comment  estc inner join ei_user eu on estc.ei_subject_thread_author_id=eu.ei_user_id where ei_subject_thread_comment_id=:ei_subject_thread_comment_id_reply'
                );

                $s->execute(
                    [
                        'ei_subject_thread_comment_id_reply' => $value['ei_subject_thread_comment_id_reply']
                    ]
                );

                $getSubjectThreadCommentReply = $s->fetchAll(PDO::FETCH_ASSOC);

                $seconds = floor($value['since_sec'] % 60);
                $minutes = floor(($value['since_sec'] % 3600) / 60);
                $hours = floor(($value['since_sec'] % (3600 * 24)) / 3600);
                $days = floor($value['since_sec'] / (3600 * 24));
                if ($days == 0) {
                    if ($hours == 0 && $minutes == 0) {
                        $commentDate = $seconds . ' seconds ago';
                    } else if ($hours == 0 && $minutes != 0) {
                        $commentDate = $minutes . ' minutes ago';
                    } else {
                        $commentDate = $hours . ' hours ago';
                    }
                } else {
                    $commentDate =  date("m d Y H:m", strtotime($value['ei_subject_thread_comment_datetime']));
                }
                $getSubjectThreadComment[$key]['time_after'] = $commentDate;

                $getSubjectThreadComment[$key]['comment_reply'] = $getSubjectThreadCommentReply;
            } else {

                $s = $this->PDO->prepare(
                    'SELECT * from ei_subject_thread_comment_read_by estcrb inner join ei_user eu on eu.ei_user_id=estcrb.ei_subject_thread_user_id  where ei_subject_thread_comment_id =:ei_subject_thread_comment_id'
                );

                $s->execute(
                    [
                        'ei_subject_thread_comment_id' => $value['ei_subject_thread_comment_id']
                    ]
                );

                $getSubjectThreadCommentReadBy = $s->fetchAll(PDO::FETCH_ASSOC);
                // error_log(json_encode($getSubjectThreadCommentReadBy));
                $getSubjectThreadComment[$key]['User_read'] = $getSubjectThreadCommentReadBy;
                $seconds = floor($value['since_sec'] % 60);
                $minutes = floor(($value['since_sec'] % 3600) / 60);
                $hours = floor(($value['since_sec'] % (3600 * 24)) / 3600);
                $days = floor($value['since_sec'] / (3600 * 24));
                if ($days == 0) {
                    if ($hours == 0 && $minutes == 0) {
                        $commentDate = $seconds . ' seconds ago';
                    } else if ($hours == 0 && $minutes != 0) {
                        $commentDate = $minutes . ' minutes ago';
                    } else {
                        $commentDate = $hours . ' hours ago';
                    }
                } else {
                    $commentDate =  date("m d Y H:m", strtotime($value['ei_subject_thread_comment_datetime']));
                }
                $getSubjectThreadComment[$key]['time_after'] = $commentDate;
            }
        }
        $this->setData($getSubjectThreadComment);

        return true;
    }

    /**
     * Recuperer tout les commentaires d'un thread dans la modal
     * 
     * @return array
     */
    function getSubjectThreadCommentAll()
    {
        $d = $this->checkParams(
            [
                'ei_subject_thread_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT *, NOW()-estu.ei_subject_thread_comment_datetime as since_sec FROM ei_subject_thread_comment estu inner join ei_user eu on eu.ei_user_id=estu.ei_subject_thread_author_id where ei_subject_thread_id=:ei_subject_thread_id'
        );

        $s->execute(
            [
                'ei_subject_thread_id' => $d->ei_subject_thread_id
            ]
        );

        $getSubjectThreadComment = $s->fetchAll(PDO::FETCH_ASSOC);

        foreach ($getSubjectThreadComment as $key => $value) {
            if ($value && $value['ei_subject_thread_comment_id_reply']) {
                $commentDate= '';
                $s = $this->PDO->prepare(
                    'SELECT estc.*, eu.username , NOW()-estc.ei_subject_thread_comment_datetime as since_sec FROM ei_subject_thread_comment  estc inner join ei_user eu on estc.ei_subject_thread_author_id=eu.ei_user_id where ei_subject_thread_comment_id=:ei_subject_thread_comment_id_reply'
                );

                $s->execute(
                    [
                        'ei_subject_thread_comment_id_reply' => $value['ei_subject_thread_comment_id_reply']
                    ]
                );

                $getSubjectThreadCommentReply = $s->fetchAll(PDO::FETCH_ASSOC);

                $seconds = floor($value['since_sec'] % 60);
                $minutes = floor(($value['since_sec'] % 3600) / 60);
                $hours = floor(($value['since_sec'] % (3600 * 24)) / 3600);
                $days = floor($value['since_sec'] / (3600 * 24));
                if ($days == 0) {
                    if ($hours == 0 && $minutes == 0) {
                        $commentDate = $seconds . ' seconds ago';
                    } else if ($hours == 0 && $minutes != 0) {
                        $commentDate = $minutes . ' minutes ago';
                    } else {
                        $commentDate = $hours . ' hours ago';
                    }
                } else {
                    $commentDate =  date("m d Y H:m", strtotime($value['ei_subject_thread_comment_datetime']));
                }
                $getSubjectThreadComment[$key]['time_after'] = $commentDate;

                $getSubjectThreadComment[$key]['comment_reply'] = $getSubjectThreadCommentReply;
            } else {

                $s = $this->PDO->prepare(
                    'SELECT * from ei_subject_thread_comment_read_by estcrb inner join ei_user eu on eu.ei_user_id=estcrb.ei_subject_thread_user_id  where ei_subject_thread_comment_id =:ei_subject_thread_comment_id'
                );

                $s->execute(
                    [
                        'ei_subject_thread_comment_id' => $value['ei_subject_thread_comment_id']
                    ]
                );

                $getSubjectThreadCommentReadBy = $s->fetchAll(PDO::FETCH_ASSOC);
                // error_log(json_encode($getSubjectThreadCommentReadBy));
                $getSubjectThreadComment[$key]['User_read'] = $getSubjectThreadCommentReadBy;
                $seconds = floor($value['since_sec'] % 60);
                $minutes = floor(($value['since_sec'] % 3600) / 60);
                $hours = floor(($value['since_sec'] % (3600 * 24)) / 3600);
                $days = floor($value['since_sec'] / (3600 * 24));
                if ($days == 0) {
                    if ($hours == 0 && $minutes == 0) {
                        $commentDate = $seconds . ' seconds ago';
                    } else if ($hours == 0 && $minutes != 0) {
                        $commentDate = $minutes . ' minutes ago';
                    } else {
                        $commentDate = $hours . ' hours ago';
                    }
                } else {
                    $commentDate =  date("m d Y H:m", strtotime($value['ei_subject_thread_comment_datetime']));
                }
                $getSubjectThreadComment[$key]['time_after'] = $commentDate;
            }
        }
        $this->setData($getSubjectThreadComment);

        return true;
    }

    /**
     * Crée un thread
     * 
     * @return array
     */
    function addSubjectThread()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int',
                'ei_subject_thread_name' => 'string',
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT max(ei_subject_thread_id)+1 FROM ei_subject_thread'
        );
        $s->execute();
        $ei_subject_thread_id = (int)($s->fetch()?:[0])[0];

        if ($ei_subject_thread_id == 0) {
            $ei_subject_thread_id = 1;
        }

        $s = $this->PDO->prepare(
            'INSERT INTO `ei_subject_thread` (`ei_subject_thread_id`, `ei_subject_id`, `ei_subject_thread_name`) VALUES (:ei_subject_thread_id, :ei_subject_id, :ei_subject_thread_name)'
        );

        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id,
                'ei_subject_thread_name' => $d->ei_subject_thread_name,
                'ei_subject_thread_id' => $ei_subject_thread_id,
            ]
        );

        return true;
    }

    /**
     * Delete un thread
     * 
     * @return array
     */
    function deleteSubjectThread()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int',
                'ei_subject_thread_id' => 'int',
            ]
        );

        $s = $this->PDO->prepare(
            'DELETE FROM `ei_subject_thread` WHERE `ei_subject_thread_id`=:ei_subject_thread_id and`ei_subject_id`=:ei_subject_id'
        );

        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id,
                'ei_subject_thread_id' => $d->ei_subject_thread_id,
            ]
        );

        $s = $this->PDO->prepare(
            'DELETE FROM `ei_subject_thread_user` WHERE `ei_subject_thread_id`=:ei_subject_thread_id'
        );

        $s->execute(
            [
                'ei_subject_thread_id' => $d->ei_subject_thread_id,
            ]
        );

        $s = $this->PDO->prepare(
            'DELETE estcrb FROM ei_subject_thread_comment_read_by estcrb
                INNER JOIN
            ei_subject_thread_comment estc ON estc.ei_subject_thread_comment_id = estcrb.ei_subject_thread_comment_id
                AND estc.ei_subject_thread_id = :ei_subject_thread_id'
        );

        $s->execute(
            [
                'ei_subject_thread_id' => $d->ei_subject_thread_id,
            ]
        );

        $s = $this->PDO->prepare(
            'DELETE FROM `ei_subject_thread_comment` WHERE `ei_subject_thread_id`=:ei_subject_thread_id'
        );

        $s->execute(
            [
                'ei_subject_thread_id' => $d->ei_subject_thread_id,
            ]
        );

        return true;
    }

    /**
     * Ajouter un user
     * 
     * @return array
     */
    function addUserToThread()
    {
        $d = $this->checkParams(
            [
                'ei_subject_thread_id' => 'int',
                'ei_subject_thread_username' => 'string',
                'ei_subject_thread_user_id' => 'int',
            ]
        );

        if (strlen($d->ei_subject_thread_username) <= 0 ) {
            $s = $this->PDO->prepare(
                'SELECT username FROM ei_user where ei_user_id=:ei_subject_thread_user_id'
            );
            $s->execute(
                [
                    'ei_subject_thread_user_id' => $d->ei_subject_thread_user_id
                ]
            );
            $ei_subject_thread_username = $s->fetch(PDO::FETCH_ASSOC)['username'];
             
            
        } else {
            $ei_subject_thread_username = $d->ei_subject_thread_username;
        }


        $s = $this->PDO->prepare(
            'SELECT count(1) FROM ei_subject_thread_user where ei_subject_thread_id=:ei_subject_thread_id and ei_subject_thread_username=:ei_subject_thread_username and ei_subject_thread_user_id=:ei_subject_thread_user_id'
        );
        $s->execute(
            [
                'ei_subject_thread_id' => $d->ei_subject_thread_id,
                'ei_subject_thread_username' => $ei_subject_thread_username,
                'ei_subject_thread_user_id' => $d->ei_subject_thread_user_id
            ]
        );
        $countThreadUserExist = (int)($s->fetch()?:[0])[0];

        if ($countThreadUserExist == 0) {
            $s = $this->PDO->prepare(
                'INSERT INTO `ei_subject_thread_user` (`ei_subject_thread_id`, `ei_subject_thread_username`, `ei_subject_thread_user_id`) VALUES (:ei_subject_thread_id, :ei_subject_thread_username, :ei_subject_thread_user_id)'
            );

            $s->execute(
                [
                    'ei_subject_thread_id' => $d->ei_subject_thread_id,
                    'ei_subject_thread_username' => $ei_subject_thread_username,
                    'ei_subject_thread_user_id' => $d->ei_subject_thread_user_id
                ]
            );
            return true;
        } else {
            $this->setData('User already exist');
            return false;
        }
        

        
    }

    /**
     * Supprimer un user du thread
     * 
     * @return array
     */
    function deleteSubjectThreadUser()
    {
        $d = $this->checkParams(
            [
                'ei_subject_thread_id' => 'int',
                'ei_subject_thread_user_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'DELETE FROM `ei_subject_thread_user` WHERE `ei_subject_thread_id`=:ei_subject_thread_id and `ei_subject_thread_user_id`=:ei_subject_thread_user_id'
        );

        $s->execute(
            [
                'ei_subject_thread_id' => $d->ei_subject_thread_id,
                'ei_subject_thread_user_id' => $d->ei_subject_thread_user_id
            ]
        );

        return true;
    }

    /**
     * Supprimer un comment du thread
     * 
     * @return array
     */
    function deleteSubjectThreadComment()
    {
        $d = $this->checkParams(
            [
                'ei_subject_thread_comment_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'DELETE  FROM ei_subject_thread_comment_read_by where ei_subject_thread_comment_id = :ei_subject_thread_comment_id'
        );

        $s->execute(
            [
                'ei_subject_thread_comment_id' => $d->ei_subject_thread_comment_id,
            ]
        );

        $s = $this->PDO->prepare(
            'DELETE FROM `ei_subject_thread_comment` WHERE `ei_subject_thread_comment_id`=:ei_subject_thread_comment_id'
        );

        $s->execute(
            [
                'ei_subject_thread_comment_id' => $d->ei_subject_thread_comment_id,
            ]
        );

        return true;
    }

    /**
     * Ajouter un commentaire a un thread
     * 
     * @return array
     */
    function addSubjectThreadComment()
    {
        $d = $this->checkParams(
            [
                'ei_subject_thread_id' => 'int',
                'ei_subject_thread_comment_message' => 'html',
                'ei_subject_thread_comment_id_reply' => 'int',
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT max(ei_subject_thread_comment_id)+1 FROM ei_subject_thread_comment'
        );
        $s->execute();
        $ei_subject_thread_comment_id = (int)($s->fetch()?:[0])[0];

        if ($ei_subject_thread_comment_id == 0) {
            $ei_subject_thread_comment_id = 1;
        }

        $s = $this->PDO->prepare(
            'INSERT INTO `ei_subject_thread_comment` (
            `ei_subject_thread_comment_id`, 
            `ei_subject_thread_id`, 
            `ei_subject_thread_author_id`, 
            `ei_subject_thread_comment_message`, 
            `ei_subject_thread_comment_datetime`, 
            `ei_subject_thread_comment_id_reply`) 
            VALUES (:ei_subject_thread_comment_id, :ei_subject_thread_id, :ei_subject_thread_author_id, :ei_subject_thread_comment_message, now(), :ei_subject_thread_comment_id_reply)'
        );

        $s->execute(
            [
                'ei_subject_thread_comment_id' => $ei_subject_thread_comment_id,
                'ei_subject_thread_id' => $d->ei_subject_thread_id,
                'ei_subject_thread_author_id' => $this->user['ei_user_id'],
                'ei_subject_thread_comment_message'=> $d->ei_subject_thread_comment_message,
                'ei_subject_thread_comment_id_reply'=> $d->ei_subject_thread_comment_id_reply
            ]
        );

        return true;
    }

    /**
     * Update un commentaire sur un thread 
     * 
     * @return array
     */
    function updateSubjectThreadComment()
    {
        $d = $this->checkParams(
            [
                'ei_subject_thread_comment_id' => 'int',
                'ei_subject_thread_comment_message' => 'string'
            ]
        );

        $s = $this->PDO->prepare(
            "UPDATE `ei_subject_thread_comment` SET `ei_subject_thread_comment_message`=:ei_subject_thread_comment_message WHERE `ei_subject_thread_comment_id`=:ei_subject_thread_comment_id"
        );

        $s->execute(
            [
                'ei_subject_thread_comment_id' => $d->ei_subject_thread_comment_id,
                'ei_subject_thread_comment_message' => $d->ei_subject_thread_comment_message
            ]
        );


        return true;
    }

    /**
     * Insertion de la lecture du commentaire par un des user assigner au thread 
     * 
     * @return array
     */
    function addSubjectThreadCommentUserRead()
    {
        $d = $this->checkParams(
            [
                'ei_subject_thread_user_id' => 'int',
                'ei_subject_thread_comment_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT max(ei_subject_thread_comment_read_id)+1 FROM ei_subject_thread_comment_read_by'
        );
        $s->execute();
        $ei_subject_thread_comment_user_read_id = (int)($s->fetch()?:[0])[0];

        if ($ei_subject_thread_comment_user_read_id == 0) {
            $ei_subject_thread_comment_user_read_id = 1;
        }

        $s = $this->PDO->prepare(
            'SELECT * from ei_subject_thread_comment_read_by where ei_subject_thread_comment_id=:ei_subject_thread_comment_id and ei_subject_thread_user_id=:ei_subject_thread_user_id;'
        );
        $s->execute(
            [
                'ei_subject_thread_user_id' => $d->ei_subject_thread_user_id,
                'ei_subject_thread_comment_id' => $d->ei_subject_thread_comment_id
            ]
        );
        $ei_subject_thread_comment_user_read_exist = (int)($s->fetch()?:[0])[0];

        if ($ei_subject_thread_comment_user_read_exist == 0) {
            $s = $this->PDO->prepare(
                "INSERT INTO `ei_subject_thread_comment_read_by` (`ei_subject_thread_comment_read_id`, `ei_subject_thread_comment_id`, `ei_subject_thread_user_id`)
                VALUES (:ei_subject_thread_comment_user_read_id, :ei_subject_thread_comment_id, :ei_subject_thread_user_id);"
            );

            $s->execute(
                [
                    'ei_subject_thread_user_id' => $d->ei_subject_thread_user_id,
                    'ei_subject_thread_comment_id' => $d->ei_subject_thread_comment_id,
                    'ei_subject_thread_comment_user_read_id'=>$ei_subject_thread_comment_user_read_id
                ]
            );
 
        } 
        

        return true;
    }

    /**
     * Recuperer tout les subject filter par userId
     * 
     * @return array
     */
    function getSubjectFilter()
    {
        $s = $this->PDO->prepare(
            "select * from ei_subject_filter where ei_user_id=:ei_user_id;"
        );

        $s->execute(
            [
                'ei_user_id' => $this->user['ei_user_id']
            ]
        );
        $SubjectFilter = $s->fetchAll(PDO::FETCH_ASSOC);
        
        $createurFilter = '';
        $count = 0;
        foreach ($SubjectFilter as $key => $value) {
            if ($value['ei_subject_filter_url']) {
                $createurlfilter = '[';
                $test = explode('/', $value['ei_subject_filter_url']);
                if ($test) {
                    unset($test[0]);
                    unset($test[1]);
                    foreach ($test as $key => $value) {
                        if ($value) {
                            if (ctype_digit(substr($value, 0, 1)) != 1) {
                                $createurlfilter .= '{"' . $value . '":';
                            } else {
                                $createurlfilter .= '[' . $value . ']}';
                                if ($key -1 !== sizeof($test)) {
                                    $createurlfilter .= ',';
                                }
                            }
                        }
                        
                    }
                }
                $createurlfilter .= ']';

                $id = $this->callClass(
                    "Filter", 
                    "applyFiltercount", 
                    [
                        'filterjson' => $createurlfilter,
                        'table_name' => 'ei_subject',
                    ]
                );
                if ($id) {
                    $nb_subject = $id->getdata();
                    
                    $SubjectFilter[$count++]['nb_subject'] = $nb_subject[0]['nb_subject'];
                }
            }
        }
        
        $this->setData($SubjectFilter);

        return true;
    }

    /**
     * Ajout des logs
     * 
     * @return array
     */
    function addSubjectAudit()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int',
                'ei_description' => 'html',
                'element_type' => 'string',
                'element_id' => 'int',
                'label' => 'string',
                'action' => 'string'
            ]
        );

        $s = $this->PDO->prepare(
            'INSERT into ei_subject_audit(ei_subject_id, ei_user_id, datetime, description, element_type, element_id, label, action) 
            values (:ei_subject_id,:ei_user_id,NOW(),:ei_description, :element_type, :element_id, :label, :action)'
        );

            // error_log($d->ei_subject_id);
            // error_log($d->ei_description);
            // error_log($d->element_type);
            // error_log($d->element_id);
            // error_log($d->label);
            // error_log($d->action);
            // error_log($this->user['ei_user_id']);

        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id,
                'ei_description' => $d->ei_description,
                'element_type' => $d->element_type,
                'element_id' => $d->element_id,
                'label' => $d->label,
                'action' => $d->action,
                'ei_user_id' => $this->user['ei_user_id']
            ]
        );

        return true;
    }

    /**
     * Récupération de la liste des phase des deploymentstep
     * 
     * @return array
     */
    function getDeploymentStepPhaseList()
    {
        $s = $this->PDO->prepare(
            'SELECT * from ei_deploymentstep_phase order by `deploymentstep_phase_order` asc'
        );
        $s->execute();
        $deployment_phase_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($deployment_phase_list);

        return true;
    }

    /**
     * Récupération de la liste des type des deploymentstep
     * 
     * @return array
     */
    function getDeploymentStepTypeList()
    {
        $s = $this->PDO->prepare(
            'SELECT * from ref_deploymentstep_type order by `deploymentstep_type_order` asc'
        );
        $s->execute();
        $deployment_type_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($deployment_type_list);

        return true;
    }

    /**
     * Recuperer tout les logs
     * 
     * @return array
     */
    function getSubjectAudit()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT sa.ei_subject_audit_id, sa.ei_subject_id, sa.ei_user_id, u.username, 
            u.picture_path, sa.datetime, sa.element_type, sa.element_id, sa.description, sa.label, sa.action
            from ei_subject_audit sa left outer join ei_user u
            on sa.ei_user_id=u.ei_user_id where sa.ei_subject_id=:ei_subject_id order by ei_subject_audit_id desc'
        );
        
        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id
            ]
        );

        $SubjectAudit = $s->fetchAll(PDO::FETCH_ASSOC);
        $this->setData($SubjectAudit);

        return true;
    }

    /**
     * Modification d'une pahse des deploymentstep
     * 
     * @return true
     */
    function updateDeploymentStepPhase()
    {
        $d = $this->checkParams(
            [
                'deploymentstep_phase_id' => 'int',
                'deploymentstep_phase_name' => 'string',
                'deploymentstep_phase_order' => 'int',
            ]
        );
        $s = $this->PDO->prepare(
            'SELECT * from ei_deploymentstep_phase where deploymentstep_phase_name=:deploymentstep_phase_name and deploymentstep_phase_id !=:deploymentstep_phase_id'
        );
        $s->execute(
            [
                'deploymentstep_phase_name' => $d->deploymentstep_phase_name,
                'deploymentstep_phase_id' => $d->deploymentstep_phase_id
            ]
        );
        $deployment_step_phase_exist = $s->fetch();

        if ($deployment_step_phase_exist != false) {
            $this->logError(
                'deployment step phase already exists', 0
            );
        } else {
            $s = $this->PDO->prepare(
                'UPDATE ei_deploymentstep_phase set deploymentstep_phase_name=:deploymentstep_phase_name,deploymentstep_phase_order=:deploymentstep_phase_order
                where deploymentstep_phase_id=:deploymentstep_phase_id'
            );
            $s->execute(
                [
                    'deploymentstep_phase_name' => $d->deploymentstep_phase_name,
                    'deploymentstep_phase_order' => $d->deploymentstep_phase_order,
                    'deploymentstep_phase_id' => $d->deploymentstep_phase_id,
                ]
            );
        }
        
        $s = $this->PDO->prepare(
            'SELECT * from ei_deploymentstep_phase where deploymentstep_phase_id=:deploymentstep_phase_id'
        );
        $s->execute(
            [
                'deploymentstep_phase_id' => $d->deploymentstep_phase_id
            ]
        );
        $status = $s->fetch(PDO::FETCH_ASSOC);

        $this->setData($status);

        return true;
    }

    /**
     * Modification d'une type des deploymentstep
     * 
     * @return true
     */
    function updateDeploymentStepType()
    {
        $d = $this->checkParams(
            [
                'deploymentstep_type_id' => 'int',
                'deploymentstep_type_name' => 'string',
                'deploymentstep_type_order' => 'int',
            ]
        );
        $s = $this->PDO->prepare(
            'SELECT * from ref_deploymentstep_type where deploymentstep_type_name=:deploymentstep_type_name and ref_deploymentstep_type_id !=:deploymentstep_type_id'
        );
        $s->execute(
            [
                'deploymentstep_type_name' => $d->deploymentstep_type_name,
                'deploymentstep_type_id' => $d->deploymentstep_type_id
            ]
        );
        $deployment_step_type_exist = $s->fetch();

        if ($deployment_step_type_exist != false) {
            $this->logError(
                'deployment step type already exists', 0
            );
        } else {
            $s = $this->PDO->prepare(
                'UPDATE ref_deploymentstep_type set deploymentstep_type_name=:deploymentstep_type_name,deploymentstep_type_order=:deploymentstep_type_order
                where ref_deploymentstep_type_id=:deploymentstep_type_id'
            );
            $s->execute(
                [
                    'deploymentstep_type_name' => $d->deploymentstep_type_name,
                    'deploymentstep_type_order' => $d->deploymentstep_type_order,
                    'deploymentstep_type_id' => $d->deploymentstep_type_id,
                ]
            );
        }
        
        $s = $this->PDO->prepare(
            'SELECT * from ref_deploymentstep_type where ref_deploymentstep_type_id=:deploymentstep_type_id'
        );
        $s->execute(
            [
                'deploymentstep_type_id' => $d->deploymentstep_type_id
            ]
        );
        $status = $s->fetch(PDO::FETCH_ASSOC);

        $this->setData($status);

        return true;
    }

    /**
     * Création d'un nouveau statut de delivery
     * 
     * @return true
     */
    function addDeploymentStepPhase()
    {
        $d = $this->checkParams(
            [
                'deploymentstep_phase_name' => 'string',
                'deploymentstep_phase_order' => 'int',
            ]
        );
        // error_log($d->deploymentstep_phase_name);
        // error_log($d->deploymentstep_phase_order);
        $s = $this->PDO->prepare(
            'SELECT * from ei_deploymentstep_phase where deploymentstep_phase_name=:deploymentstep_phase_name'
        );
        $s->execute(
            [
                'deploymentstep_phase_name' => $d->deploymentstep_phase_name
            ]
        );
        $deploymentstep_name_exists = $s->fetch();

        if ($deploymentstep_name_exists != false) {
            // Le status existe déjà
            $this->logError(
                'Deployment Step Phase already exists', 0
            );
        } else {
            // On récupère l'id max pour le statut de la tache
            $s = $this->PDO->prepare(
                'SELECT max(deploymentstep_phase_id) from ei_deploymentstep_phase'
            );
            $s->execute();
            $deployment_Step_Phase_id = (int)($s->fetch()?:[0])[0]+1;


            $deployment_Step_order = 0;

            if ($d->deploymentstep_phase_order == 0) {
                // On récupère l'order max de task_status
                $s = $this->PDO->prepare(
                    'SELECT max(deploymentstep_phase_order) from ei_deploymentstep_phase'
                );
                $s->execute();
                $deployment_Step_order = (int)($s->fetch()?:[0])[0]+1;

            } else {
                $deployment_Step_order = $d->deploymentstep_phase_order;
            }

            // Insertion du nouveau statut de tache
            $s = $this->PDO->prepare(
                'INSERT into ei_deploymentstep_phase(deploymentstep_phase_id, deploymentstep_phase_name, deploymentstep_phase_order) values(:deploymentstep_phase_id, 
                :deploymentstep_phase_name, :deploymentstep_phase_order)'
            );
            $s->execute(
                [
                    'deploymentstep_phase_id' => $deployment_Step_Phase_id,
                    'deploymentstep_phase_name' => $d->deploymentstep_phase_name,
                    'deploymentstep_phase_order' => $deployment_Step_order,
                ]
            );
        }

        return true;
    }

    /**
     * Création d'un nouveau type de delivery
     * 
     * @return true
     */
    function addDeploymentStepType()
    {
        $d = $this->checkParams(
            [
                'deploymentstep_type_name' => 'string',
                'deploymentstep_type_order' => 'int',
            ]
        );
        // error_log($d->deploymentstep_type_name);
        // error_log($d->deploymentstep_type_order);
        $s = $this->PDO->prepare(
            'SELECT * from ref_deploymentstep_type where deploymentstep_type_name=:deploymentstep_type_name'
        );
        $s->execute(
            [
                'deploymentstep_type_name' => $d->deploymentstep_type_name
            ]
        );
        $deploymentstep_name_exists = $s->fetch();

        if ($deploymentstep_name_exists != false) {
            // Le status existe déjà
            $this->logError(
                'Deployment Step Type already exists', 0
            );
        } else {
            // On récupère l'id max pour le statut de la tache
            $s = $this->PDO->prepare(
                'SELECT max(ref_deploymentstep_type_id) from ref_deploymentstep_type'
            );
            $s->execute();
            $deployment_Step_Type_id = (int)($s->fetch()?:[0])[0]+1;


            $deployment_Step_order = 0;

            if ($d->deploymentstep_type_order == 0) {
                // On récupère l'order max de task_status
                $s = $this->PDO->prepare(
                    'SELECT max(deploymentstep_type_order) from ref_deploymentstep_type'
                );
                $s->execute();
                $deployment_Step_order = (int)($s->fetch()?:[0])[0]+1;

            } else {
                $deployment_Step_order = $d->deploymentstep_type_order;
            }

            // Insertion du nouveau statut de tache
            $s = $this->PDO->prepare(
                'INSERT into ref_deploymentstep_type(ref_deploymentstep_type_id, deploymentstep_type_name, deploymentstep_default_description, deploymentstep_type_order) values(:deploymentstep_type_id, 
                :deploymentstep_type_name,:deploymentstep_type_name, :deploymentstep_type_order)'
            );
            $s->execute(
                [
                    'deploymentstep_type_id' => $deployment_Step_Type_id,
                    'deploymentstep_type_name' => $d->deploymentstep_type_name,
                    'deploymentstep_type_order' => $deployment_Step_order,
                ]
            );
        }

        return true;
    }

    /**
     * Recuperer les subjects en fonction de la recherche
     * 
     * @return array
     */
    function getSearchSubjectList()
    {
        $d = $this->checkParams(
            [
                'SearchInSubject' => 'string'
            ]
        );
        $d = $this->initOptionalParams('filterjson', 'json', '{}');     

        // error_log($d->SearchInSubject);
        
        $assign = 0;
        $statusTask = 0;


        $searchLike = "'%".$d->SearchInSubject."%'";

        
        $sql = "SELECT 
            u3.subject_user_pin,
            esr.risk_exec,
            esr.risk_exec_ok,
            esr.risk_exec_ko,
            esr.total_risk,
            edsre.risk_delivery_exec_ok,
            edsre.risk_delivery_exec_ko,
            s.ei_subject_id,
            s.ei_subject_external_id,
            s.title,
            p.ei_pool_id,
            p.pool_color,
            p.pool_name,
            d.ei_delivery_id,
            d.delivery_name,
            rds.is_final,
            st.ref_subject_type_id,
            st.type_name,
            st.type_icon,
            ss.ref_subject_status_id,
            ss.status_name,
            ss.color AS status_color,
            ss.status_icon,
            sp.ref_subject_priority_id,
            sp.priority_name,
            sp.color AS priority_color,
            sp.priority_picto,
            u.username,
            u.picture_path,
            u2.username AS in_charge_username,
            u2.picture_path AS in_charge_picture_path,
            s.created_at,
            DATEDIFF(NOW(), s.created_at) AS diff_days
            from 
            ei_subject s
                LEFT OUTER JOIN
            ei_pool p ON s.ei_pool_id = p.ei_pool_id
                LEFT OUTER JOIN
            ei_delivery d ON s.ei_delivery_id = d.ei_delivery_id
                LEFT OUTER JOIN 
            ref_delivery_status rds ON d.ref_delivery_type_status_id = rds.ref_delivery_type_status_id
                LEFT OUTER JOIN
            ref_subject_type st ON s.ref_subject_type_id = st.ref_subject_type_id
                LEFT OUTER JOIN
            ref_subject_status ss ON s.ref_subject_status_id = ss.ref_subject_status_id
                LEFT OUTER JOIN
            ref_subject_priority sp ON s.ref_subject_priority_id = sp.ref_subject_priority_id
                LEFT OUTER JOIN
            ei_user u ON s.creator_id = u.ei_user_id
                LEFT OUTER JOIN
            ei_user u2 ON s.ei_subject_user_in_charge = u2.ei_user_id
                LEFT OUTER JOIN
            (SELECT 
                 current_subject_id, concat('[',GROUP_CONCAT('{\"username\":\"',username,'\",\"picture_path\":\"',picture_path,'\"}' SEPARATOR ', '),']')
                  AS subject_user_pin
            FROM
                ei_user
            GROUP BY current_subject_id) u3 ON u3.current_subject_id = s.ei_subject_id
            LEFT OUTER JOIN
            (SELECT 
                esr.ei_subject_id,
                    SUM((SELECT 
                            COUNT(1)
                        FROM
                            ei_function_stat efs
                        WHERE
                            efs.nb_ok > 1
                                AND efs.ei_function_id = esr.ei_function_id
                                AND ei_iteration_id = 1)) AS risk_exec,
                    COUNT(*) AS total_risk,
                    SUM((SELECT 
                            COUNT(1)
                        FROM
                            ei_function_stat efs
                        WHERE
                            efs.last_status = 'ok'
                                AND efs.ei_function_id = esr.ei_function_id
                                AND ei_iteration_id = 1)) AS risk_exec_ok,
                    SUM((SELECT 
                            COUNT(1)
                        FROM
                            ei_function_stat efs
                        WHERE
                            efs.last_status = 'ko'
                                AND efs.ei_function_id = esr.ei_function_id
                                AND ei_iteration_id = 1)) AS risk_exec_ko
            FROM
                ei_subject_risk esr
            LEFT OUTER JOIN ei_function_stat eess ON eess.ei_function_id = esr.ei_function_id
            WHERE
                esr.ei_subject_id = esr.ei_subject_id
            GROUP BY esr.ei_subject_id) esr ON esr.ei_subject_id = s.ei_subject_id
                LEFT OUTER JOIN
            ei_delivery_subject_risk_exec edsre ON edsre.ei_subject_id = s.ei_subject_id
            where s.ei_subject_version_id=(select max(s2.ei_subject_version_id) 
            from ei_subject s2 where s2.ei_subject_id=s.ei_subject_id) and (UPPER(s.title) LIKE UPPER($searchLike) OR UPPER(s.ei_subject_external_id) LIKE UPPER($searchLike))";

        if ($d->filterjson != '') {
            // error_log(json_encode($d->filterjson));
            foreach ($d->filterjson as $key => $value) {
                // echo $key . " => " . $value . "<br>";
                // error_log($key);
                foreach ($d->filterjson[$key] as $key2 => $value2) {
                    // foreach du dessus pas obliger
                    // error_log($key2);
                    if (count($value2) > 0) {
                        switch ($key2) {
                        default:
                            $sql .= " and s." . $key2 . " in (";
                            foreach ($value2 as $i => $list) {
                                // error_log($list);
                                
                                // error_log(count($value2));
                                if ($i == count($value2)-1) {
                                    $sql .= $list; 
                                } else {
                                    $sql .= $list . ","; 
                                }
                            }
                            $sql .= ")"; 
                            break;
                        case 'assign_id':
                            $sql .= " and s.ei_subject_id in (SELECT distinct tl.object_id as subject_id from ei_task t 
                            left outer join ei_task_link tl on tl.ei_task_id=t.ei_task_id where t.ei_user_id in (";
                            foreach ($value2 as $i => $list) {
                                // error_log($list);
                                
                                // error_log(count($value2));
                                if ($i == count($value2)-1) {
                                    $sql .= $list; 
                                } else {
                                    $sql .= $list . ","; 
                                }
                            }
                            $sql .= ")";
                            $assign = 1;
                            break;
                        case 'ref_task_status':
                            $temp = '';
                            foreach ($value2 as $i => $list) {
                                // error_log($list);
                                
                                // error_log(count($value2));
                                if ($i == count($value2)-1) {
                                    $temp .= $list; 
                                } else {
                                    $temp .= $list . ","; 
                                }
                            }

                            if ($assign == 1) {
                                $sql .= 'and t.ref_task_status_id in (';
                                $sql .= $temp;
                                $sql .= ')';
                            }
                            $sql .= " and s.ei_subject_id in (SELECT distinct tl.object_id as subject_id from ei_task t left outer join 
                            ei_task_link tl on tl.ei_task_id=t.ei_task_id
                            where t.ref_task_status_id in (";
                            // foreach ($value2 as $i => $list) {
                            //     // error_log($list);
                                
                            //     // error_log(count($value2));
                            //     if ($i == count($value2)-1) {
                            //         $sql .= $list; 
                            //     } else {
                            //         $sql .= $list . ","; 
                            //     }
                            // }
                            $sql .= $temp;
                            $sql .= "))"; 
                            $statusTask = 1;
                            break;
                        case 'ref_task_type':
                            $typeTask = 1;
                            $sql .= "and s.ei_subject_id in (SELECT distinct tl.object_id as subject_id from ei_task t left outer join 
                            ei_task_link tl on tl.ei_task_id=t.ei_task_id
                            where t.ref_task_type_id in (";
                            foreach ($value2 as $i => $list) {
                                // error_log($list);
                                
                                // error_log(count($value2));
                                if ($i == count($value2)-1) {
                                    $sql .= $list; 
                                } else {
                                    $sql .= $list . ","; 
                                }
                            }
                            // error_log($statusTask);
                            // error_log($assign);
                            if (!$statusTask) {
                                $sql .= "))";
                                // error_log('pas de status task');
                            }
                            if ($statusTask && !$assign) {
                                $sql .= ")"; 
                                // error_log('status task');
                            } 
                            if ($statusTask == 1 && $assign == 0) {
                                // error_log("1 et 0");
                                $sql.= ")";
                            }
                            if ($statusTask == 1 && $assign == 1) {
                                // error_log("1 et 1");
                                $sql.= "))";
                            }
                            
                            break;
                        }
                    }
                }
                // error_log($sql);
            }
        }
        if ($assign) {
            $sql .= ')';
        }
        $sql .= " order by s.ei_subject_id desc";

        // error_log($sql);
        $s = $this->PDO->prepare($sql);
        $s->execute(
            [
                // 'searchLike' => $searchLike
            ]
        );
        $SubjectList = $s->fetchAll(PDO::FETCH_ASSOC);
        $this->setData($SubjectList);

        return true;
    }

    /**
     * Récupération de l'image de l'utilisateur
     * 
     * @return true
     */
    function getUserImageProfil()
    {

        // Récupération de la version max de la version
        $s = $this->PDO->prepare(
            'SELECT ei_user_id, username, picture_path FROM ei_user where ei_user_id=:ei_user_id'
        );
        $s->execute(
            [
                'ei_user_id' => $this->user['ei_user_id']
            ]
        );
        $imageProfilUser = $s->fetchAll(PDO::FETCH_ASSOC);
        $this->setData($imageProfilUser);

    }

    /**
     * Récupération de l'image de tout les utilisateurs
     * 
     * @return true
     */
    function getAllUserImageProfil()
    {

        // Récupération de la version max de la version
        $s = $this->PDO->prepare(
            'SELECT ei_user_id, username, picture_path FROM ei_user'
        );
        $s->execute([]);
        $imageProfilUser = $s->fetchAll(PDO::FETCH_ASSOC);
        $this->setData($imageProfilUser);

    }

    /**
     * Supprime l'impact 
     * 
     * @return true
     */
    function deleteSubjectRisk()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int',
                'ei_function_id' => 'int'

            ]
        );
        $s = $this->PDO->prepare(
            "DELETE FROM `ei_subject_risk` WHERE (`ei_subject_id` = :SubID) and (`ei_function_id` = :FunID) and (`risk_type` = 'manual');"
        );
        $s->execute(
            [
                'SubID' => $d->ei_subject_id,
                'FunID' => $d->ei_function_id
            ]
        );
    }

    /**
     * Supprime le filtre subject en fav 
     * 
     * @return true
     */
    function deleteFavoriteSubjectFilter()
    {
        $d = $this->checkParams(
            [
                'ei_subject_filter_id' => 'int',

            ]
        );
        $s = $this->PDO->prepare(
            "DELETE FROM `ei_subject_filter` WHERE `ei_subject_filter_id`=:ei_subject_filter_id;"
        );
        $s->execute(
            [
                'ei_subject_filter_id' => $d->ei_subject_filter_id,
            ]
        );
    }

    /**
     * Update Favorite Subject Filter 
     * 
     * @return true
     */
    function updateFavoriteSubjectFilter()
    {
        $d = $this->checkParams(
            [
                'ei_subject_filter_id' => 'int',
                'ei_subject_filter_name' => 'html',

            ]
        );
        $s = $this->PDO->prepare(
            "UPDATE `ei_subject_filter` 
            SET `ei_subject_filter_name`=:ei_subject_filter_name 
            WHERE `ei_subject_filter_id`=:ei_subject_filter_id;"
        );
        $s->execute(
            [
                'ei_subject_filter_id' => $d->ei_subject_filter_id,
                'ei_subject_filter_name' => $d->ei_subject_filter_name,
            ]
        );
    }


    /**
     * Récupère le score de chaque scénario en fonction du subject et des fonctions
     * 
     * @return true
     */
    function getBestScenarios() 
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int' 
            ]
        );
        $s = $this->PDO->prepare(
            "with functiondtl as (
            select svc.ei_scenario_id,svc.ei_subject_id as ei_scenario_subject_id, svc.ei_scenario_version_id,svc.ei_scenario_version_block_id, svc.ei_scenario_version_column_id,  svc.ei_function_id , 
            count(1) as nb_function_count
            from ei_scenario_version_cell svc
            where 
            svc.type = 'FUNCTION'
            group by svc.ei_scenario_id,svc.ei_subject_id, svc.ei_scenario_version_id,svc.ei_scenario_version_block_id, svc.ei_scenario_version_column_id ,  svc.ei_function_id ),
            # TOUS LES SCENARS QUI ONT DES FONCTIONS ET LE SUBJECT ET LES FONCTIONS RATTACHées
            scenario_subject_active as (
            select  ei_scenario_id, ei_subject_id, count(1) as nb_scenario_env_active
            from ei_scenario_version_environment a 
            where a.effective_date = (select max(aa.effective_date) 
            from ei_scenario_version_environment aa where aa.ei_scenario_id = a.ei_scenario_id and aa.ei_environment_id = a.ei_environment_id and aa.effective_date <=now())
            group by ei_scenario_id, ei_subject_id 
            ) ,
            # LES SCENARS ET SUBJ QUI SONT DANS UN ENV RECENT
            last_ei_scenario_version as
            (select  ei_scenario_id, ei_subject_id, ei_scenario_version_id as last_ei_scenario_version_id
            from ei_scenario_version  a 
            where a.ei_scenario_version_id = (select max(aa.ei_scenario_version_id) 
            from ei_scenario_version aa where aa.ei_scenario_id = a.ei_scenario_id and aa.ei_subject_id = a.ei_subject_id)
            group by ei_scenario_id, ei_subject_id),
            # DERNIERE VERSION DES SCENARS
            totub as (
            select ei_scenario_id, ei_function_id from ei_scenario_version_cell where type = 'FUNCTION' group by ei_scenario_id,ei_function_id
            ),
            scenarFunRisk as (
            select tb.ei_scenario_id, tb.ei_function_id
            from totub tb 
            left join ei_subject_risk sr on sr.ei_function_id = tb.ei_function_id
            where sr.ei_subject_id = :id
            and sr.ei_function_id = tb.ei_function_id
            ),
            recupFun as (select sfr.ei_function_id, cs.ei_scenario_id 
            FROM ei_subject_campaign_scenario  cs
            inner join scenarFunRisk as sfr on cs.ei_scenario_id=sfr.ei_scenario_id
            where cs.ei_subject_id=:id
            group by sfr.ei_function_id),
            # fonctions déjà presentent dans le subject
            test as (
            select    fd.ei_scenario_id,fd.ei_scenario_subject_id, fd.ei_scenario_version_id,fd.ei_scenario_version_block_id, 
            fd.ei_scenario_version_column_id, 
            concat('-', group_concat(   concat('F',fd.ei_function_id)   order by fd.ei_function_id separator '-' ),'-') as ei_function_id_present, 
            case when fd.ei_function_id = rf.ei_function_id then (sum(10) + sum(nb_function_count)) else (sum(1000) + sum(nb_function_count)) END as score,
            ssa.nb_scenario_env_active, 
            case when  lsv.last_ei_scenario_version_id = fd.ei_scenario_version_id then 100 else 1 END as version_score
            from  ei_subject_risk sr
            inner join functiondtl fd on fd.ei_function_id = sr.ei_function_id
            left outer join scenario_subject_active ssa on ssa.ei_scenario_id = fd.ei_scenario_id and ssa.ei_subject_id = fd.ei_scenario_subject_id
            inner join last_ei_scenario_version  lsv on lsv.ei_scenario_id = fd.ei_scenario_id and lsv.ei_subject_id = fd.ei_scenario_subject_id
            left outer join recupFun rf on fd.ei_function_id = rf.ei_function_id
            where   sr.ei_subject_id = :id
            group by sr.ei_subject_id,  fd.ei_scenario_id,fd.ei_scenario_subject_id, fd.ei_scenario_version_id,fd.ei_scenario_version_block_id, fd.ei_scenario_version_column_id,ssa.nb_scenario_env_active
            order by score desc),
            # FUN IMPACTS PRESENTS DANS SCENAR ET SCORE en fonction de coeff impact et coeff actu
            final as (
            select tt.ei_scenario_id, tt.ei_scenario_subject_id, tt.ei_scenario_version_id, tt.ei_function_id_present, case when  tt.ei_scenario_id = rf.ei_scenario_id then 0 else (tt.score + (tt.nb_scenario_env_active * tt.version_score)) end as scoreFinal
            from test tt
            left outer join recupFun as rf on tt.ei_scenario_id=rf.ei_scenario_id
            )
            # ON VERIFIE QUE YA PAS DEJA LE SCENAR DANS LE SUBJECT et on ajoute le coeff d'actualité
            select fi.ei_scenario_id, nom.scenario_name, fi.ei_scenario_subject_id, fi.ei_scenario_version_id, li.ei_ai_blockfunctionstep as composition, fi.ei_function_id_present, max(fi.scoreFinal) as scoreScenar
            from final fi
            inner join ei_scenario nom on fi.ei_scenario_id = nom.ei_scenario_id
            inner join ei_ai_blockfunctionlist li on fi.ei_scenario_id = li.ei_scenario_id 
            WHERE fi.scoreFinal IS NOT NULL and fi.scoreFinal != 0
            group by ei_scenario_id 
            order by fi.scoreFinal desc    
            limit 5;
            # meilleur scenar en fonction des impacts et des scenarios et fonctions deja presentent"
        );
        $s->execute(
            [
                'id' => $d->ei_subject_id
            ]
        );
        $score = $s->fetchall(PDO::FETCH_ASSOC);
        // on repasse de l'écriture simplifiée "-F1-F21-F8-F4..." aux vrais noms
        for ($i=0; $i < sizeof($score); $i++) { 
            // d'abord on met la composition en lettre
            $functs = $score[$i]['composition'];
            $arrayFunc = explode('-', $functs);
            unset($arrayFunc[0]);
            unset($arrayFunc[sizeof($arrayFunc)]);
            $arrayFinal = [];
            foreach ($arrayFunc as $val) {
                $valsansF = str_replace("F", "", $val);
                $s = $this->PDO->prepare(
                    "SELECT function_name FROM ei_function where ei_function_id = :id;"
                );
                $s->execute(
                    [
                        'id' => $valsansF
                    ]
                );
                $nom = $s->fetchAll(PDO::FETCH_ASSOC);
                array_push($arrayFinal, $nom[0]['function_name']);
            } 
            $string = "";
            $compteur = 0;
            foreach ($arrayFinal as $nom) {
                if ($compteur == sizeof($arrayFinal)-1) {
                    $string = $string.$nom;
                } else {
                    $string = $string.$nom." / ";
                }
                $compteur++;
            }     
            $score[$i]['composition'] = $string;

            // on passe au fonctions impactées
            $functs = $score[$i]['ei_function_id_present'];
            $arrayFunc = explode('-', $functs);
            unset($arrayFunc[0]);
            unset($arrayFunc[sizeof($arrayFunc)]);
            $arrayFinal = [];
            foreach ($arrayFunc as $val) {
                $valsansF = str_replace("F", "", $val);
                $s = $this->PDO->prepare(
                    "SELECT function_name FROM ei_function where ei_function_id = :id;"
                );
                $s->execute(
                    [
                        'id' => $valsansF
                    ]
                );
                $nom = $s->fetchAll(PDO::FETCH_ASSOC);
                array_push($arrayFinal, $nom[0]['function_name']);
            } 
            $string = "";
            $compteur = 0;
            foreach ($arrayFinal as $nom) {
                if ($compteur == sizeof($arrayFinal)-1) {
                    $string = $string.$nom;
                } else {
                    $string = $string.$nom." / ";
                }
                $compteur++;
            }     
            $score[$i]['ei_function_id_present'] = $string;
        }
        $this->setData($score);
    } 
}