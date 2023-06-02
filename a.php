<?php
/**
 * Scenario file  
 * 
 * PHP version 5
 * 
 * @category User
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
 * Device class 
 * 
 * @category User
 * 
 * @package Backend
 * 
 * @author EISGE <kalifast@eisge.com>
 * 
 * @license kalifast.com Kalifast
 * 
 * @link kalifast.com
 */
class User extends BaseApi
{
    /**
     * Modification de l'image de profil de l'utilisateur
     * 
     * @return true
     */
    function AddImageProfil()
    {
        $d = $this->checkParams(
            [
                'picture_path' => 'sfssddstring'
            ]
        );

        $s = $this->PDO->prepare(
            'UPDATE ei_user set picture_path=:picture_path where ei_user_id=:ei_user_id'
        );
        $s->execute(
            [
                'picture_path' => $d->picture_path,
                'ei_user_id' => $this->user['ei_user_id']
            ]
        );

        $this->setData($d->picture_path);

        return true;
    }

    /**
     * Ajout de l'url dans la table d'historique
     * 
     * @return true
     */
    function AddUserHistory()
    {
        $d = $this->checkParams(
            [
                'history_url' => 'string'
            ]
        );

        $s = $this->PDO->prepare(
            'INSERT IGNORE INTO `ei_user_history` (`ei_user_id`, `history_url`, `history_datetime`) VALUES (:ei_user_id, :history_url, now())'
        );
        $s->execute(
            [
                'history_url' => $d->history_url,
                'ei_user_id' => $this->user['ei_user_id']
            ]
        );

        return true;
    }

    /**
     * Récupération de l'histoirque de l'utilisateur
     * 
     * @return true
     */
    function GetHistory()
    {
        $d = $this->checkParams(
            [
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT 
                *
            FROM
                ei_user_history
            WHERE
                ei_user_id = :ei_user_id
            ORDER BY history_datetime DESC
            LIMIT 20;'
        );
        $s->execute(
            [
                'ei_user_id' => $this->user['ei_user_id']
            ]
        );

        $history_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($history_list);

        return true;
    }

    /**
     * Suppression d'un rôle d'un utilisateur
     * 
     * @return true
     */
    function deleteUserRole()
    {
        $d = $this->checkParams(
            [
                'ei_api_role_id' => 'string',
                'ei_user_id' => 'int'
            ]
        );
        
        $s = $this->PDO->prepare(
            'DELETE from ei_api_role_user where ei_api_role_id=:ei_api_role_id and ei_user_id=:ei_user_id'
        );
        $s->execute(
            [
                'ei_api_role_id' => $d->ei_api_role_id,
                'ei_user_id' => $d->ei_user_id
            ]
        );

        return true;
    }

    /**
     * Récupération de la pool actuelle de l'utilisateur
     * 
     * @return int
     */
    function getApplicationModuleModeLibraryAction()
    {
        $s = $this->PDO->prepare(
            " SELECT distinct
					m.ei_api_application_id,
					m.ei_api_application_module_id,
					m.ei_api_application_module_mode_id,
					m.ei_api_library_id,
					m.ei_api_library_action_id
				FROM
					ei_api_application_module_mode_library_action m
				WHERE
					(EXISTS( SELECT 
							'X'
						FROM
							ei_api_application_module_mode_permission md,
							ei_api_role_permission p,
							ei_api_role_user u
						WHERE
							md.ei_api_application_id = m.ei_api_application_id
								AND md.ei_api_application_module_id = m.ei_api_application_module_id
								AND md.ei_api_application_module_mode_id = m.ei_api_application_module_mode_id
								AND md.ei_api_permission_id = p.ei_api_permission_id
								AND p.ei_api_role_id = u.ei_api_role_id
								AND u.ei_user_id = :ei_user_id)
						OR EXISTS( SELECT 
							'X'
						FROM
							ei_api_role_user r
						WHERE
							ei_user_id = :ei_user_id
								AND ei_api_role_id = 'SUPERMAN'))
           "
        );
        $s->execute(
            [
                'ei_user_id' => $this->user['ei_user_id']
            ]
        );
        $pool_id = (int)($s->fetch()?:[0])[0];

        $this->setData($pool_id);
        
        return true;
    }

    /**
     * Récupération de la liste des modes pour l'application et le module 
     * 
     * @return array
     */
    function getApplicationModuleModeByApplicationModule()
    {
        $d = $this->checkParams(
            [
                'ei_api_application_id' => 'string',
                'ei_api_application_module_id' => 'string'
            ]
        );

        $s = $this->PDO->prepare(
            "SELECT distinct m.ei_api_application_id, m.ei_api_application_module_id, m.ei_api_application_module_mode_id
            from ei_api_application_module_mode m 
            where m.ei_api_application_id=:ei_api_application_id 
            and m.ei_api_application_module_id=:ei_api_application_module_id 
            and
				(EXISTS( SELECT 
							'X'
						FROM
							ei_api_application_module_mode_permission md,
							ei_api_role_permission p,
							ei_api_role_user u
						WHERE
							md.ei_api_application_id = m.ei_api_application_id
								AND md.ei_api_application_module_id = m.ei_api_application_module_id
								AND md.ei_api_application_module_mode_id = m.ei_api_application_module_mode_id
								AND md.ei_api_permission_id = p.ei_api_permission_id
								AND p.ei_api_role_id = u.ei_api_role_id
								AND u.ei_user_id = :ei_user_id)
						OR EXISTS( SELECT 
							'X'
						FROM
							ei_api_role_user r
						WHERE
							ei_user_id = :ei_user_id
                                AND ei_api_role_id = 'SUPERMAN'))
                "
        );
        $s->execute(
            [
                'ei_api_application_id' => $d->ei_api_application_id,
                'ei_api_application_module_id' => $d->ei_api_application_module_id,
                'ei_user_id' =>  $this->user['ei_user_id']
            ]
        );
        $application_module_mode_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($application_module_mode_list);

        return true;
    }

    /**
     * Récupération de la pool actuelle de l'utilisateur
     * 
     * @return int
     */
    function getCurrentPoolId()
    {
        $s = $this->PDO->prepare(
            'SELECT current_pool_id from ei_user where ei_user_id=
            :ei_user_id'
        );
        $s->execute(
            [
                'ei_user_id' => $this->user['ei_user_id']
            ]
        );
        $pool_id = (int)($s->fetch()?:[0])[0];

        $this->setData($pool_id);

        return true;
    }

    /**
     * Récupération de la pool actuelle de l'utilisateur
     * 
     * @return int
     */
    function getCurrentDeliveryId()
    {
        $s = $this->PDO->prepare(
            'SELECT 
                 es.ei_delivery_id, ed.delivery_name
            FROM
                ei_subject es, ei_delivery ed
            WHERE ed.ei_delivery_id=es.ei_delivery_id and
                es.ei_subject_version_id = (SELECT 
                        MAX(es2.ei_subject_version_id)
                    FROM
                        ei_subject es2
                    WHERE
                        ei_subject_id = :ei_subject_id)
                    AND ei_subject_id = :ei_subject_id'
        );
        $s->execute(
            [
                'ei_subject_id' => $this->user['current_subject_id']
            ]
        );
        $delivery_id = $s->fetch(PDO::FETCH_ASSOC);

        $this->setData($delivery_id);

        return true;
    }

    /**
     * Récupération des données pour la navigation en bas a gauche
     * 
     * @return true
     */
    function getNavBottomLeft()
    {
        $d = $this->checkParams(
            [
                'user_id' => 'int'
            ]
        );

        $currentSubjectId = $this->callClass(
            "User", 
            "getCurrentSubjectId", 
            [
                'null' => 'null'
            ]
        );
        $getEnvList = $this->callClass(
            "User", 
            "getEnvironmentList", 
            [
                'user_id' => $d->user_id
            ]
        );
        $currentDeliveryId = $this->callClass(
            "User", 
            "getCurrentDeliveryId", 
            [
                'null' => 'null'
            ]
        );

        $data['currentSubjectId'] = $currentSubjectId->getdata();
        $data['getEnvList'] = $getEnvList->getdata();
        $data['currentDeliveryId'] = $currentDeliveryId->getdata();
        
        $this->setData($data);
    }

    /**
     * Récupération de l'intervention actuelle de l'utilisateur
     * 
     * @return true
     */
    function getCurrentSubjectId()
    {
        $s = $this->PDO->prepare(
            'SELECT current_subject_id from ei_user where ei_user_id=
            :ei_user_id'
        );
        $s->execute(
            [
                'ei_user_id' => $this->user['ei_user_id']
            ]
        );
        $subject_id = (int)($s->fetch()?:[0])[0];

        $this->setData(
            [
                'subject_id' => $subject_id
            ]
        );

        return true;
    }
    

    /**
     * Récupération de l'env courant de l'utilisateur et de la liste
     * 
     * @return array
     */
    function getEnvironmentList()
    {
        $d = $this->checkParams(
            [
                'user_id' => 'int'
            ]
        );

        if ($d->user_id != 0) {
            // Récupération de l'id de l'env courant de l'utilisateur
            $s = $this->PDO->prepare(
                'SELECT current_environment_id from ei_user where ei_user_id=
                :ei_user_id'
            );
            $s->execute(
                [
                    'ei_user_id' => $d->user_id
                ]
            );
            $current_env_id = (int)($s->fetch()?:[0])[0];

            // Récupération de la liste des env
            $s = $this->PDO->prepare(
                'SELECT * from ei_environment'
            );
            $s->execute();
            $env_list = $s->fetchAll(PDO::FETCH_ASSOC);

            $this->setData(
                [
                    'current_env_id' => $current_env_id,
                    'env_list' => $env_list
                ]
            );
        } else {
            // Récupération de la liste des env
            $s = $this->PDO->prepare(
                'SELECT e.ei_environment_id, e.name, ei.iteration_description as iteration_name, e.order from ei_environment e 
                left outer join ei_iteration ei on e.current_iteration_id=ei.ei_iteration_id'
            );
            $s->execute();
            $env_list = $s->fetchAll(PDO::FETCH_ASSOC);

            $this->setData($env_list);
        }

        return true;
    }

    /**
     * Récupération de la liste des paramètres
     * 
     * @return array
     */
    function getParamsList()
    {
        $s = $this->PDO->prepare(
            'SELECT * from ref_param'
        );
        $s->execute();
        $param_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($param_list);
    }

    /**
     * Récupération de la liste des valeurs du paramètre (surchargés ou non) de l'utilisateur pour les environnements
     * 
     * @return array
     */
    function getParamValueList()
    {
        $d = $this->checkParams(
            [
            ]
        );

        // Récupération des valeurs globales du paramètre
        $s = $this->PDO->prepare(
            'SELECT e.ei_environment_id, rp.name, rp.ref_param_id, ep.value as default_value, ifnull(euep.value,ep.value) as value
            from ei_environment e
            inner join ref_param rp
            left outer join ei_environment_param ep
            on e.ei_environment_id=ep.ei_environment_id
            and ep.ref_param_id = rp.ref_param_id
            left outer join ei_user_environment_param euep 
            on e.ei_environment_id=euep.ei_environment_id 
            and euep.ref_param_id=rp.ref_param_id 
            and euep.ei_user_id=:ei_user_id;'
        );
        $s->execute(
            [
                'ei_user_id' => $this->user['ei_user_id']
            ]
        );
        $param_values = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($param_values);
    }

    /**
     * Récupération des préférences de l'utilisateur pour les executions
     * 
     * @return array
     */
    function getPlayparamColumn() 
    {
        $s = $this->PDO->prepare(
            'SELECT * from ei_user_playparam_column where ei_user_id=
            :ei_user_id'
        );
        $s->execute(
            [
                'ei_user_id' => $this->user['ei_user_id']
            ]
        );
        $preferences = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($preferences);

        return true;
    }

    /**
     * Récupération du dernier device et browser utilisé pour la colonne
     * 
     * @return array
     */
    function getPlayparamColumnDevice()
    {
        $d = $this->checkParams(
            [
                'ei_column_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT upc.*, d.device_identifier, ddt.hidden_name as driver_type, 
            dbt.hidden_name as browser_type from ei_user_playparam_column upc 
            inner join ei_device d on upc.device_id=d.id 
            inner join ref_device_driver_type ddt on upc.device_driver_type_id=ddt.id
            inner join ref_device_browser_type dbt on 
            upc.device_browser_type_id=dbt.id where ei_column_id=:ei_column_id 
            and ei_user_id=:ei_user_id'
        );
        $s->execute(
            [
                'ei_column_id' => $d->ei_column_id,
                'ei_user_id' => $this->user['ei_user_id']
            ]
        );
        $device_info = $s->fetch(PDO::FETCH_ASSOC);

        $this->setData($device_info);

        return true;
    }

    /**
     * Récupération des applications disponibles pour l'utilisateur
     * 
     * @return array
     */
    function getUserApplication()
    {
        $d = $this->initOptionalParams('ei_api_application_id', 'string', '');

        $application_list = [];
        if ($d->ei_api_application_id != '') {
            $s = $this->PDO->prepare(
                'SELECT distinct ei_api_application_id from ei_user_application_module_mode_library_action_vw where ei_user_id=:ei_user_id 
                and ei_api_application_id=:ei_api_application_id'
            );
            $s->execute(
                [
                    'ei_user_id' => $this->user['ei_user_id'],
                    'ei_api_application_id' => $d->ei_api_application_id
                ]
            );
            $application_list = $s->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $s = $this->PDO->prepare(
                'SELECT distinct ei_api_application_id from ei_user_application_module_mode_library_action_vw where ei_user_id=:ei_user_id'
            );
            $s->execute(
                [
                    'ei_user_id' => $this->user['ei_user_id']
                ]
            );
            $application_list = $s->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->setData($application_list);

        return true;
    }

    /**
     * Récupération des applications et modules disponibles pour l'utilisateur
     * 
     * @return array
     */
    function getUserApplicationModule()
    {
        $d = $this->initOptionalParams('ei_api_application_id', 'string', '');
        $d = $this->initOptionalParams('ei_api_application_module_id', 'string', '');

        $application_module_list = [];
        if ($d->ei_api_application_id != '' && $d->ei_api_application_module_id != '') {
            $s = $this->PDO->prepare(
                'SELECT ei_api_application_id, ei_api_application_module_id from ei_user_application_module_mode_library_action_vw where ei_user_id=:ei_user_id
                and ei_api_application_id=:ei_api_application_id and ei_api_application_module_id=:ei_api_application_module_id
                group by ei_api_application_id, ei_api_application_module_id'
            );
            $s->execute(
                [
                    'ei_user_id' => $this->user['ei_user_id'],
                    'ei_api_application_id' => $d->ei_api_application_id,
                    'ei_api_application_module_id' => $d->ei_api_application_module_id
                ]
            );
            $application_module_list = $s->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $s = $this->PDO->prepare(
                'SELECT ei_api_application_id, ei_api_application_module_id from ei_user_application_module_mode_library_action_vw where ei_user_id=:ei_user_id
                group by ei_api_application_id, ei_api_application_module_id'
            );
            $s->execute(
                [
                    'ei_user_id' => $this->user['ei_user_id']
                ]
            );
            $application_module_list = $s->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->setData($application_module_list);

        return true;
    }

    /**
     * Récupération des applications, modules et modes disponibles pour l'utilisateur
     * 
     * @return array
     */
    function getUserApplicationModuleMode()
    {
        $d = $this->initOptionalParams('ei_api_application_id', 'string', '');
        $d = $this->initOptionalParams('ei_api_application_module_id', 'string', '');
        $d = $this->initOptionalParams('ei_api_application_module_mode_id', 'string', '');

        $application_module_mode_list = [];
        if ($d->ei_api_application_id != '' && $d->ei_api_application_module_id != '' && $d->ei_api_application_module_mode_id != '') {
            $s = $this->PDO->prepare(
                'SELECT ei_api_application_id, ei_api_application_module_id, ei_api_application_module_mode_id 
                from ei_user_application_module_mode_library_action_vw where ei_user_id=:ei_user_id
                and ei_api_application_id=:ei_api_application_id and ei_api_application_module_id=:ei_api_application_module_id
                and ei_api_application_module_mode_id=:ei_api_application_module_mode_id
                group by ei_api_application_id, ei_api_application_module_id, ei_api_application_module_mode_id'
            );
            $s->execute(
                [
                    'ei_user_id' => $this->user['ei_user_id'],
                    'ei_api_application_id' => $d->ei_api_application_id,
                    'ei_api_application_module_id' => $d->ei_api_application_module_id,
                    'ei_api_application_module_mode_id' => $d->ei_api_application_module_mode_id
                ]
            );
            $application_module_mode_list = $s->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $s = $this->PDO->prepare(
                'SELECT ei_api_application_id, ei_api_application_module_id, ei_api_application_module_mode_id 
                from ei_user_application_module_mode_library_action_vw where ei_user_id=:ei_user_id
                group by ei_api_application_id, ei_api_application_module_id, ei_api_application_module_mode_id'
            );
            $s->execute(
                [
                    'ei_user_id' => $this->user['ei_user_id']
                ]
            );
            $application_module_mode_list = $s->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->setData($application_module_mode_list);

        return true;
    }


    /**
     * Verifier les perms des user
     * 
     * @return array
     */
    function verifyUserApplicationModuleMode()
    {
        $d = $this->checkParams(
            [
                'ei_api_application_id' => 'string'
            ]
        );

        $application_module_mode_list = [];
        
            $s = $this->PDO->prepare(
                'SELECT ei_api_application_id, ei_api_application_module_id, ei_api_application_module_mode_id ,ei_api_library_id,ei_api_library_action_id
                from ei_user_application_module_mode_library_action_vw where ei_user_id=:ei_user_id and ei_api_application_id=:application
                group by ei_api_application_id, ei_api_application_module_id, ei_api_application_module_mode_id'
            );
            $s->execute(
                [
                    'ei_user_id' => $this->user['ei_user_id'],
                    'application' => $d->ei_api_application_id
                ]
            );
            $application_module_mode_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($application_module_mode_list);

        return true;
    }

    /**
     * Récupération des applications, modules, modes, libraries et actions disponibles pour l'utilisateur
     * 
     * @return array
     */
    function getUserApplicationModuleModeLibraryAction()
    {
        $d = $this->initOptionalParams('ei_api_application_id', 'string', '');
        $d = $this->initOptionalParams('ei_api_application_module_id', 'string', '');
        $d = $this->initOptionalParams('ei_api_application_module_mode_id', 'string', '');
        $d = $this->initOptionalParams('ei_api_library_id', 'string', '');
        $d = $this->initOptionalParams('ei_api_library_action_id', 'string', '');

        $application_module_mode_library_action_list = [];
        if ($d->ei_api_application_id != '' && $d->ei_api_application_module_id != '' && $d->ei_api_application_module_mode_id != '' 
            && $d->ei_api_library_id != '' && $d->ei_api_library_action_id !== ''
        ) {
            $s = $this->PDO->prepare(
                'SELECT ei_api_application_id, ei_api_application_module_id, ei_api_application_module_mode_id, ei_api_library_id,
                ei_api_library_action_id
                from ei_user_application_module_mode_library_action_vw where ei_user_id=:ei_user_id
                and ei_api_application_id=:ei_api_application_id and ei_api_application_module_id=:ei_api_application_module_id
                and ei_api_application_module_mode_id=:ei_api_application_module_mode_id and ei_api_library_id=:ei_api_library_id
                and ei_api_library_action_id=:ei_api_library_action_id
                group by ei_api_application_id, ei_api_application_module_id, ei_api_application_module_mode_id, ei_api_library_id, ei_api_library_action_id'
            );
            $s->execute(
                [
                    'ei_user_id' => $this->user['ei_user_id'],
                    'ei_api_application_id' => $d->ei_api_application_id,
                    'ei_api_application_module_id' => $d->ei_api_application_module_id,
                    'ei_api_application_module_mode_id' => $d->ei_api_application_module_mode_id,
                    'ei_api_library_id' => $d->ei_api_library_id,
                    'ei_api_library_action_id' => $d->ei_api_library_action_id
                ]
            );
            $application_module_mode_library_action_list = $s->fetchAll(PDO::FETCH_ASSOC);
        } else if ($d->ei_api_application_id != '' && $d->ei_api_application_module_id != '' && $d->ei_api_application_module_mode_id != '' ) {
            $s = $this->PDO->prepare(
                'SELECT ei_api_application_id, ei_api_application_module_id, ei_api_application_module_mode_id, ei_api_library_id,
                ei_api_library_action_id
                from ei_user_application_module_mode_library_action_vw where ei_user_id=:ei_user_id
                and ei_api_application_id=:ei_api_application_id and ei_api_application_module_id=:ei_api_application_module_id
                and ei_api_application_module_mode_id=:ei_api_application_module_mode_id
                group by ei_api_application_id, ei_api_application_module_id, ei_api_application_module_mode_id, ei_api_library_id, ei_api_library_action_id'
            );
            $s->execute(
                [
                    'ei_user_id' => $this->user['ei_user_id'],
                    'ei_api_application_id' => $d->ei_api_application_id,
                    'ei_api_application_module_id' => $d->ei_api_application_module_id,
                    'ei_api_application_module_mode_id' => $d->ei_api_application_module_mode_id
                ]
            );
            $application_module_mode_library_action_list = $s->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $s = $this->PDO->prepare(
                'SELECT ei_api_application_id, ei_api_application_module_id, ei_api_application_module_mode_id, ei_api_library_id,
                ei_api_library_action_id
                from ei_user_application_module_mode_library_action_vw where ei_user_id=:ei_user_id
                group by ei_api_application_id, ei_api_application_module_id, ei_api_application_module_mode_id, ei_api_library_id, ei_api_library_action_id'
            );
            $s->execute(
                [
                    'ei_user_id' => $this->user['ei_user_id']
                ]
            );
            $application_module_mode_library_action_list = $s->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->setData($application_module_mode_library_action_list);

        return true;
    }

    /**
     * Modification de l'option pour voir la popup
     * 
     * @return true
     */
    function optionScenarioCellPopUp()
    {
        $d = $this->checkParams(
            [
                'option' => 'string'
            ]
        );
        $s = $this->PDO->prepare(
            "UPDATE ei_user SET scenario_cell_popup=:option WHERE ei_user_id=:ei_user_id;"
        );
        $s->execute(
            [
                'option' => $d->option,
                'ei_user_id' => $this->user['ei_user_id']
            ]
        );
        
    
        return true;
    }

    /**
     * Récuperation de l'option choisi pour l'affichage des popup sur les scenario
     * 
     * @return true
     */
    function getOptionScenarioCellPopUp()
    {
        $d = $this->checkParams([]);
        $s = $this->PDO->prepare(
            "SELECT scenario_cell_popup from ei_user where ei_user_id=:ei_user_id;"
        );
        $s->execute(
            [
                'ei_user_id' => $this->user['ei_user_id']
            ]
        );

        $option_for_user = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($option_for_user);
        
    
        return true;
    }

    /**
     * Récuperation de toutes les technos
     * 
     * @return true
     */
    function getTechno()
    {
        

        $d = $this->checkParams([]);
        $s = $this->PDO->prepare(
            "SELECT * from ref_techno"
        );
        $s->execute(
            [
            ]
        );

        $techno_list = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($techno_list);
        
    
        return true;
    }

    /**
     * Récuperation deu  nom executionrepport
     * 
     * @return true
     */
    function getExecutionRepportName()
    {
        

        $d = $this->checkParams([]);
        $s = $this->PDO->prepare(
            "SELECT execution_repport_name_format FROM ei_user where ei_user_id=:ei_user_id;"
        );
        $s->execute(
            [
                'ei_user_id' => $this->user['ei_user_id']
            ]
        );

        $techno_list = $s->fetch(PDO::FETCH_ASSOC);

        $this->setData($techno_list);
        
    
        return true;
    }

    /**
     * Modification de l'environnement actuel de l'utilisateur courant
     * 
     * @return true
     */
    function updateCurrentEnvironment()
    {
        $d = $this->checkParams(
            [
                'user_id' => 'int',
                'environment_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'UPDATE ei_user set current_environment_id=:current_environment_id 
            where ei_user_id=:ei_user_id'
        );
        $s->execute(
            [
                'current_environment_id' => $d->environment_id,
                'ei_user_id' => $d->user_id
            ]
        );
    
        return true;
    }

    /**
     * Modification de la technos par default
     * 
     * @return true
     */
    function updateDefaultTechno()
    {
        $d = $this->checkParams(
            [
                'techno_id' => 'string'
            ]
        );
        // error_log($d->techno_id);
        $s = $this->PDO->prepare(
            "UPDATE `ei_user` SET `ref_techno_id_default`=:techno_id WHERE `ei_user_id`=:ei_user_id"
        );
        $s->execute(
            [
                'techno_id' => $d->techno_id,
                'ei_user_id' => $this->user['ei_user_id']
            ]
        );
    
        return true;
    }

    /**
     * Modification de execution repport
     * 
     * @return true
     */
    function updateExecutionRepportFormatName()
    {
        $d = $this->checkParams(
            [
                'executionrepportformatname' => 'string'
            ]
        );
        $s = $this->PDO->prepare(
            "UPDATE `ei_user` SET `execution_repport_name_format`=:executionrepportformatname WHERE `ei_user_id`=:user_id"
        );
        $s->execute(
            [
                'executionrepportformatname' => $d->executionrepportformatname,
                'user_id' => $this->user['ei_user_id']
            ]
        );
    
        return true;
    }

    /**
     * Recuperation de la techno par defaut d'un user
     * 
     * @return true
     */
    function getDefaultTechno()
    {
        $d = $this->checkParams(
            [
            ]
        );
        $s = $this->PDO->prepare(
            "SELECT ref_techno_id_default FROM ei_user where ei_user_id=:ei_user_id;"
        );
        $s->execute(
            [
                'ei_user_id' => $this->user['ei_user_id']
            ]
        );

        $techno_by_default = $s->fetch(PDO::FETCH_ASSOC);

        $this->setData($techno_by_default['ref_techno_id_default']);
    
        return true;
    }

    /**
     * Modification de la pool actuelle de l'utilisateur
     * 
     * @return true
     */
    function updateCurrentPool()
    {
        $d = $this->checkParams(
            [
                'ei_pool_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'UPDATE ei_user set current_pool_id=:ei_pool_id where 
            ei_user_id=:ei_user_id'
        );
        $s->execute(
            [
                'ei_pool_id' => $d->ei_pool_id,
                'ei_user_id' => $this->user['ei_user_id']
            ]
        );

        return true;
    }

    /**
     * Modification de l'intervention actuelle de l'utilisateur 
     * 
     * @return true
     */
    function updateCurrentSubject()
    {
        $d = $this->checkParams(
            [
                'ei_subject_id' => 'int'
            ]
        );

        $s = $this->PDO->prepare(
            'UPDATE ei_user set current_subject_id=:ei_subject_id where 
            ei_user_id=:ei_user_id'
        );
        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id,
                'ei_user_id' => $this->user['ei_user_id']
            ]
        );

        $s = $this->PDO->prepare(
            'INSERT INTO `ei_subject_user_pin_log` (`ei_subject_id`, `ei_user_id`,pin_datetime) VALUES (:ei_subject_id, :ei_user_id,NOW())'
        );
        $s->execute(
            [
                'ei_subject_id' => $d->ei_subject_id,
                'ei_user_id' => $this->user['ei_user_id']
            ]
        );

        return true;
    }

    /**
     * Modification / surcharge d'un paramètre pour l'utilisateur et l'environnement
     * 
     * @return true
     */
    function updateParamValue()
    {
        $d = $this->checkParams(
            [
                'ei_environment_id' => 'int',
                'ref_param_id' => 'int',
                'value' => 'string'
            ]
        );
        // error_log($d->ei_environment_id);
        // error_log($d->ref_param_id);
        // error_log($d->value);
        // On regarde si la ligne existe déjà dans ei_user_environment_param
        $s = $this->PDO->prepare(
            'SELECT count(*) from ei_user_environment_param where ei_user_id=:ei_user_id and ei_environment_id=:ei_environment_id
            and ref_param_id=:ref_param_id'
        );
        $s->execute(
            [
                'ei_user_id' => $this->user['ei_user_id'],
                'ei_environment_id' => $d->ei_environment_id,
                'ref_param_id' => $d->ref_param_id
            ]
        );
        $line_exists = (int)($s->fetch()?:[0])[0];
        // error_log($line_exists);
        if ($line_exists == 0) {
            // On insère la ligne
            $s = $this->PDO->prepare(
                'INSERT into ei_user_environment_param(ei_user_id, ei_environment_id, ref_param_id, value, created_at, updated_at)
                values(:ei_user_id, :ei_environment_id, :ref_param_id, :value, now(), now())'
            );
            $s->execute(
                [
                    'value' => $d->value,
                    'ei_user_id' => $this->user['ei_user_id'],
                    'ei_environment_id' => $d->ei_environment_id,
                    'ref_param_id' => $d->ref_param_id
                ]
            );
        } else {
            if ($d->value != '') {
                // On modifie la ligne
                $s = $this->PDO->prepare(
                    'UPDATE ei_user_environment_param set value=:value where ei_user_id=:ei_user_id and ei_environment_id=:ei_environment_id
                    and ref_param_id=:ref_param_id'
                );
                $s->execute(
                    [
                        'value' => $d->value,
                        'ei_user_id' => $this->user['ei_user_id'],
                        'ei_environment_id' => $d->ei_environment_id,
                        'ref_param_id' => $d->ref_param_id
                    ]
                );
            } else {
                // On supprime la ligne (la valeur est vide, on remet celle par défaut)
                $s = $this->PDO->prepare(
                    'DELETE from ei_user_environment_param where ei_user_id=:ei_user_id and ei_environment_id=:ei_environment_id
                    and ref_param_id=:ref_param_id'
                );
                $s->execute(
                    [
                        'ei_user_id' => $this->user['ei_user_id'],
                        'ei_environment_id' => $d->ei_environment_id,
                        'ref_param_id' => $d->ref_param_id
                    ]
                );

                $this->setData('Ask refresh');
            }
        }

        return true;
    }

    /**
     * Modification / insertion des préférences de l'utilisateur pour les executions 
     * d'un scenario
     * 
     * @return true
     */
    function updatePlayparamColumn()
    {
        $d = $this->checkParams(
            [
                'ei_column_id' => 'int',
                'on_error' => 'string',
                'play_mode' => 'string',
                'screenshot_beginning' => 'string',
                'screenshot_end' => 'string',
                'browser_type' => 'string',
                'device_identifier' => 'string',
                'driver_type' => 'string'
            ]
        );

        // error_log($d->ei_column_id);
        // error_log($d->on_error);
        // error_log($d->play_mode);
        // error_log($d->screenshot_beginning);
        // error_log($d->screenshot_end);
        // error_log($d->browser_type);
        // error_log($d->device_identifier);
        // error_log($d->driver_type);

        // On vérifie que la ligne liée à l'utilisateur et la colonne existe
        $s = $this->PDO->prepare(
            'SELECT * from ei_user_playparam_column where ei_user_id=
            :ei_user_id and ei_column_id=:ei_column_id'
        );
        $s->execute(
            [
                'ei_user_id' => $this->user['ei_user_id'],
                'ei_column_id' => $d->ei_column_id
            ]
        );
        $col_preferences = $s->fetch(PDO::FETCH_ASSOC);

        // Récupération du device id, pour garder en mémoire le device et le browser 
        // utilisé pour la colonne
        $s = $this->PDO->prepare(
            "
				SELECT d.id AS device_id,
				rddt.id AS device_driver_type_id, 
				rdbt.id AS device_browser_type_id 
				FROM ei_device d 
				LEFT OUTER JOIN ei_device_driver dd 
					ON dd.device_id=d.id
				LEFT OUTER JOIN ref_device_driver_type rddt 
					ON rddt.id=dd.driver_type_id
				LEFT OUTER JOIN ei_device_driver_browser ddb 
					ON ddb.device_driver_id=dd.id
				LEFT OUTER JOIN ref_device_browser_type rdbt 
					ON rdbt.id= ddb.browser_type_id
				WHERE rddt.hidden_name = :driver_type 
				AND  rdbt.hidden_name = :browser_type 
				AND ( d.device_identifier =  :device_identifier  or    :device_identifier = '0'  )
            "
        );
        

        $s->execute(
            [
                'driver_type' => $d->driver_type,
                'browser_type' => $d->browser_type,
                'device_identifier' => $d->device_identifier
            ]
        );
        
       
        $device_info = $s->fetch(PDO::FETCH_ASSOC);

        // error_log(json_encode($device_info));

        // La colonne de préférence n'existe pas, on l'ajoute donc
        // if ($device_info) {
        //     error_log(json_encode($device_info));
        // }
        
        if ($device_info) {
            if ($col_preferences == false) {
                $s = $this->PDO->prepare(
                    'INSERT into ei_user_playparam_column(ei_user_id, ei_column_id,
                    on_error, play_mode, screenshot_beginning, screenshot_end, 
                    device_id, device_driver_type_id, device_browser_type_id) VALUES(
                    :ei_user_id, :ei_column_id, :on_error, :play_mode, 
                    :screenshot_beginning, :screenshot_end, :device_id, 
                    :device_driver_type_id, :device_browser_type_id)'
                );
                $s->execute(
                    [
                        'ei_user_id' => $this->user['ei_user_id'],
                        'ei_column_id' => $d->ei_column_id,
                        'on_error' => $d->on_error,
                        'play_mode' => $d->play_mode,
                        'screenshot_beginning' => $d->screenshot_beginning,
                        'screenshot_end' => $d->screenshot_end,
                        'device_id' => $device_info['device_id'],
                        'device_driver_type_id' => $device_info['device_driver_type_id'],
                        'device_browser_type_id' => $device_info['device_browser_type_id'],
                    ]
                );
            } else {
                // On modifie juste les préférences de cette colonne
                // error_log(json_encode($device_info));
                $s = $this->PDO->prepare(
                    'UPDATE ei_user_playparam_column set on_error=:on_error, 
                    play_mode=:play_mode, screenshot_beginning=:screenshot_beginning,
                    screenshot_end=:screenshot_end, device_id=:device_id, 
                    device_driver_type_id=:device_driver_type_id,
                    device_browser_type_id=:device_browser_type_id 
                    where ei_user_id=:ei_user_id and
                    ei_column_id=:ei_column_id'
                );
                $s->execute(
                    [
                        'on_error' => $d->on_error,
                        'play_mode' => $d->play_mode,
                        'screenshot_beginning' => $d->screenshot_beginning,
                        'screenshot_end' => $d->screenshot_end,
                        'device_id' => $device_info['device_id'],
                        'device_driver_type_id' => $device_info['device_driver_type_id'],
                        'device_browser_type_id' => $device_info['device_browser_type_id'],
                        'ei_user_id' => $this->user['ei_user_id'],
                        'ei_column_id' => $d->ei_column_id
                    ]
                );
            }
        }

        return true;
    }

    /**
     * Récupération de la liste des utilisateurs et de leur ratio/effort
     * 
     * @return array
     */
    function getUserListWithEffortRatio() 
    {
        $d = $this->checkParams(
            [
                'ref_task_type_id' => 'int'
            ]
        );
        // error_log($d ->ref_task_type_id);
        $s = $this->PDO->prepare(
            'SELECT eu.ei_user_id, eu.username, eut.ei_user_id, eut.ref_task_type_id, eut.effortbyday, eut.effdt
            from ei_user eu inner join ei_user_tasktype_effortbyday eut 
            on eu.ei_user_id=eut.ei_user_id where eut.ref_task_type_id=:ref_task_type_id'
        );
        $s->execute(
            [
                'ref_task_type_id' => $d->ref_task_type_id,
            ]
        );
        $user_list_with_effort = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($user_list_with_effort);

        return true;
    }

    /**
     * Modification du ratio d'un user
     * 
     * @return array
     */
    function updateUserListWithEffortRatio() 
    {
        $d = $this->checkParams(
            [
                'effortbyday' => 'float',
                'ei_user_id' => 'int',
                'ref_task_type_id' => 'int'
            ]
        );
        // error_log($d->effortbyday);
        // error_log($d->ei_user_id);
        // error_log($d->ref_task_type_id);

        // On vérifie que la ligne liée à l'utilisateur et la colonne existe
        $s = $this->PDO->prepare(
            'SELECT * from ei_user_tasktype_effortbyday where ei_user_id=:ei_user_id and ref_task_type_id=:ref_task_type_id'
        );
        $s->execute(
            [
                'ei_user_id' => $d->ei_user_id,
                'ref_task_type_id' => $d ->ref_task_type_id
            ]
        );
        $line_exist = $s->fetch(PDO::FETCH_ASSOC);

        if ($line_exist == true) {

            $s = $this->PDO->prepare(
                'UPDATE ei_user_tasktype_effortbyday set effortbyday=:daybyeffortratio where ei_user_id=:ei_user_id'
            );

            $s->execute(
                [
                    'daybyeffortratio' => $d->daybyeffortratio,
                    'ei_user_id' => $d->ei_user_id
                ]
            );

        }
        

        return true;
    }

    /**
     * Ajouter un user a un task type
     * 
     * @return array
     */
    function addUserToTaskType() 
    {
        $d = $this->checkParams(
            [
                'daybyeffortratio' => 'float',
                'ei_user_id' => 'int',
                'ref_task_type_id' => 'int'
            ]
        );

        // error_log($d->daybyeffortratio);
        // error_log($d->ei_user_id);
        // error_log($d->ref_task_type_id);

        $s = $this->PDO->prepare(
            'INSERT Into ei_user_tasktype_effortbyday(ei_user_id,ref_task_type_id,effortbyday) 
            values(:ei_user_id,:ref_task_type_id,:daybyeffortratio)'
        );
        $s->execute(
            [
                'daybyeffortratio' => $d->daybyeffortratio,
                'ei_user_id' => $d->ei_user_id,
                'ref_task_type_id' => $d ->ref_task_type_id
            ]
        );

        return true;
    }

    /**
     * Recuperer l'ordre d'affichage des div dans la modal debug par ei_user_id
     * 
     * @return array
     */
    function getOrderDivModalDebug() 
    {
        $d = $this->checkParams(
            [
                'ei_user_id' => 'int',
            ]
        );

        $s = $this->PDO->prepare(
            'SELECT div_position_debug_modal FROM ei_user where ei_user_id=:ei_user_id;'
        );
        $s->execute(
            [
                'ei_user_id' => $d->ei_user_id,
            ]
        );

        $divOrder = $s->fetchAll(PDO::FETCH_ASSOC);

        $this->setData($divOrder);

        return true;
    }

    /**
     * Update l'ordre d'affichage des div dans la modal debug par ei_user_id
     * 
     * @return array
     */
    function updateOrderDivModalDebug() 
    {
        $d = $this->checkParams(
            [
                'valuedivOrder' => 'string',
            ]
        );
        $s = $this->PDO->prepare(
            'UPDATE `ei_user` SET `div_position_debug_modal`=:valuedivOrder WHERE `ei_user_id`=:ei_user_id'
        );
        $s->execute(
            [
                'ei_user_id' => $this->user['ei_user_id'],
                'valuedivOrder' => $d->valuedivOrder,
            ]
        );


        return true;
    }
}