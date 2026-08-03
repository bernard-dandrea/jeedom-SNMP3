<?php

// Last Modified : 2026/08/03 11:14:02

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';


class SNMP3 extends eqLogic
{

    private static $_session = null;
    private static $_snmp_error = null;
    public static $_snmp_error_message = null;

    public function encrypt()
    {
        $this->setConfiguration('auth_passphrase', utils::encrypt($this->getConfiguration('auth_passphrase')));
        $this->setConfiguration('privacy_passphrase', utils::encrypt($this->getConfiguration('privacy_passphrase')));
    }

    public function decrypt()
    {
        $this->setConfiguration('auth_passphrase', utils::decrypt($this->getConfiguration('auth_passphrase')));
        $this->setConfiguration('privacy_passphrase', utils::decrypt($this->getConfiguration('privacy_passphrase')));
    }


    public static function enable_cron($_enable)
    {
        $cron_SNMP3 = cron::byClassAndFunction('SNMP3', 'update');
        $schedule = '* * * * *';
        if ($_enable == '1') {
            logSNMP3(__('Activation du cron de SNMP3', __FILE__), 'debug');
            if (!is_object($cron_SNMP3)) {
                $cron_SNMP3 = new cron();
                $cron_SNMP3->setClass('SNMP3');
                $cron_SNMP3->setFunction('update');
                $cron_SNMP3->setEnable(1);
                $cron_SNMP3->setDeamon(0);
                $cron_SNMP3->setSchedule($schedule);
                $cron_SNMP3->setTimeout(1);
            } else {
                $cron_SNMP3->setEnable(1);
            }
            $cron_SNMP3->save();
        } else {
            logSNMP3(__('Désactivation du cron de SNMP3', __FILE__), 'debug');
            if (is_object($cron_SNMP3)) {
                $cron_SNMP3->remove();
            }
        }
    }
    //snmpget -v 3 -n "" -u admin_snmp_2024 -a MD5 -A "Camille" -x DES -X "Camille" -l authPriv 192.168.1.5 .1.3.6.1.4.1.6574.1.5.1.0

    public function test_connexion()
    {

        //snmpget -v 3 -n "" -u admin_snmp_2024 -a MD5 -A "Camille" -x DES -X "Camille" -l authPriv 192.168.1.5 .1.3.6.1.4.1.6574.1.5.1.0


        logSNMP3('test_connexion', 'info');

        $version = $this->getConfiguration('version', '1');
        if ($version == '3') {
            $community = $this->getConfiguration('security_name', 'admin');
        } else {
            $community = $this->getConfiguration('community', 'public');
        }

        try {
            $session = new SNMP(
                $version,
                $this->getConfiguration('localhost', '127.0.0.1'),
                $community
            );
        } catch (\Throwable $e) {
            $error = __('Erreur création session SNMP', __FILE__) . ' ' . $e->getCode() . ' ' . $e->getMessage();
            logSNMP3($error);
            $error = __('Connexion KO', __FILE__) . ' : ' . $error;
            // throw new Exception($error);
            return 'KO ' . $error;
        }
        if ($session->getErrno()  != '0') {
            $error = __('Erreur création session SNMP', __FILE__) . ' ' . $session->getErrno() . ' '  . $session->getError();
            logSNMP3(__('Connexion KO', __FILE__) . ' : '  . $error);
            // throw new Exception($error);
            return 'KO ' . $error;
        }

        $session->valueretrieval = SNMP_VALUE_PLAIN;

        try {

            $result = $session->setSecurity(
                $this->getConfiguration('security_level', 'noAuthNoPriv'),
                $this->getConfiguration('auth_protocol'),
                $this->getConfiguration('auth_passphrase'),
                $this->getConfiguration('privacy_protocol'),
                $this->getConfiguration('privacy_passphrase'),
                $this->getConfiguration('context_name'),
                $this->getConfiguration('context_engineid'),
            );
        } catch (\Throwable $e) {
            $error = 'setSecurity ' . __('erreur', __FILE__) . ' ' . $e->getCode() . ' ' . $e->getMessage();
            logSNMP3($error);
            $session->close();
            $error =  __('Connexion KO', __FILE__) . ' : '  . $error;
            return 'KO ' . $error;
        }
        if ($session->getErrno()  != '0') {
            $error = 'setSecurity ' .  __('erreur', __FILE__) . ' ' . $session->getErrno() . ' ' . $session->getError();
            logSNMP3($error);
            $session->close();
            $error =  __('Connexion KO', __FILE__) . ' : '  . $error;
            // throw new Exception($error);
            return 'KO ' . $error;
        }

        $oid = '1.3.6.1.2.1.1.6.0';
        try {
            $sysLocation = $session->get($oid);
        } catch (\Throwable $e) {
            $error = __('Erreur', __FILE__) . ' get ' . $e->getCode() . ' ' . $e->getMessage();
            logSNMP3($error);
            $session->close();
            $error =  __('Connexion KO', __FILE__) . ' : '  . $error;
            // throw new Exception($error);
            return 'KO ' . $error;
        }

        if ($session->getErrno()  != '0') {
            $error = __('Erreur', __FILE__) . ' get ' . $session->getErrno() . ' '  . $session->getError();
            logSNMP3($error);
            $session->close();
            $error =  __('Connexion KO', __FILE__) . ' : '  . $error;
            // throw new Exception($error);
            return 'KO ' . $error;
        } else {
            $return = __('Connexion OK', __FILE__) . ' : ' . ' :  sysLocation (1.3.6.1.2.1.1.6.0) -> ' . $sysLocation;
            logSNMP3($return, 'info');
            $session->close();
            return 'OK ' . $return;
        }
    }

    public static function loadMIBS()
    {

        logSNMP3('loadMIBS ', 'debug');

        // load all MIBS in plugins/SNMP3/data/mibs directory
        $dirPath = __DIR__ . '/../../data/mibs';
        $files = glob($dirPath . "/*.txt");
        foreach ($files as $mib_file) {
            if (is_file($mib_file)) {
                if (snmp_read_mib($mib_file) == false) {
                    $error = __('Impossible de charger le MIB', __FILE__) . ' ' . $mib_file;
                    logSNMP3($error);
                } else {
                    logSNMP3('MIB ' . $mib_file . ' ' . __('chargé', __FILE__), 'debug');
                }
            }
        }
    }

    public static function openSession($_eqLogic, $_mode = '')
    {
        // load MIBS
        SNMP3::loadMIBS();

        logSNMP3('openSession ', 'debug');

        $version = $_eqLogic->getConfiguration('version', '1');
        if ($version == '3') {
            $community = $_eqLogic->getConfiguration('security_name', 'admin');
        } else {
            $community = $_eqLogic->getConfiguration('community', 'public');
            if ($_mode == 'RW') {
                $community = $_eqLogic->getConfiguration('community_rw', $community);
            }
        }
        $timeout = $_eqLogic->getConfiguration('timeout', '-1');
        if (is_numeric($timeout)) {
            if ($timeout > 0) {
                $timeout = $timeout * 1000; // ms ->pico secondes
            } else {
                $timeout = -1;
            }
        } else {
            $timeout = -1;
        }

        try {
            self::$_session = new SNMP(
                $version,
                $_eqLogic->getConfiguration('localhost'),
                $community,
                $timeout,
                0  // retry géré par le programme
            );
        } catch (\Throwable $e) {
            $error = __('Erreur création session SNMP', __FILE__) . ' ' . $e->getCode() . ' ' . $e->getMessage();
            logSNMP3($error);
            return false;
        }
        if (self::$_session->getErrno()  != '0') {
            $error = __('Erreur création session SNMP', __FILE__) . ' ' . self::$_session->getErrno() . ' '  . self::$_session->getError();
            logSNMP3($error);
            return false;
        }

        self::$_session->valueretrieval = SNMP_VALUE_PLAIN;

        try {

            $result = self::$_session->setSecurity(
                $_eqLogic->getConfiguration('security_level', 'noAuthNoPriv'),
                $_eqLogic->getConfiguration('auth_protocol'),
                $_eqLogic->getConfiguration('auth_passphrase'),
                $_eqLogic->getConfiguration('privacy_protocol'),
                $_eqLogic->getConfiguration('privacy_passphrase'),
                $_eqLogic->getConfiguration('context_name'),
                $_eqLogic->getConfiguration('context_engineid'),
            );
        } catch (\Throwable $e) {
            $error = __('Erreur', __FILE__) . ' setSecurity ' . $e->getCode() . ' ' . $e->getMessage();
            logSNMP3($error);
            self::$_session->close();
            return false;
        }
        if (self::$_session->getErrno()  != '0') {
            $error = __('Erreur', __FILE__) . ' setSecurity ' . self::$_session->getErrno() . ' '  . self::$_session->getError();;
            logSNMP3($error);
            self::$_session->close();
            return false;
        }
        if (self::$_session == null) {
            logSNMP3(__('Session non créée', __FILE__), 'info');
        }
        return true;
    }
    public static function closeSession()
    {

        logSNMP3('closeSession', 'debug');
        if (self::$_session != null) {
            @self::$_session->close();
        }
    }

    public static function getOID($_oid, $_retry = 1)
    {
        logSNMP3('getOID' . ' ' . $_oid, 'debug');
        self::$_snmp_error = true;
        self::$_snmp_error_message = '';
        if (self::$_session == null) {
            self::$_snmp_error_message = __('Fonction', __FILE__) . ' get: ' . __('Session non initialisée', __FILE__);
            logSNMP3(self::$_snmp_error_message, 'error');
            return false;
        }

        $essai = 0;
        while ($essai < $_retry) {
            try {
                error_reporting(E_ALL & ~E_WARNING); // désactive les warnings PHP
                $result = self::$_session->get($_oid);
                error_reporting(E_ALL); // réactive les warnings PHP
            } catch (\Throwable $e) {
                self::$_snmp_error_message = __('Fonction', __FILE__) . ' get: ' . __('erreur (exception)', __FILE__) . ' ' . $e->getCode() . ' ' . $e->getMessage();
                logSNMP3(self::$_snmp_error_message, 'error');
                return false;
            }
            if (self::$_session->getErrno() == '0') {
                break;
            }
            $essai = $essai + 1;
            if ($essai < $_retry) {
                logSNMP3(__('Fonction', __FILE__) . ' ' . 'get: ' . __('erreur', __FILE__) . ' ' . self::$_session->getErrno() . ' '  . self::$_session->getError() . __(' -> nouvel essai', __FILE__), 'warning');
            }
        }

        if (self::$_session->getErrno()  != '0') {
            self::$_snmp_error_message =  __('Fonction', __FILE__) . ' get: ' . __('erreur (exception)', __FILE__) . self::$_session->getErrno() . ' '  . self::$_session->getError();
            logSNMP3(self::$_snmp_error_message, 'error');
            return false;
        } else {
            logSNMP3('getOID ' . ' ' . $_oid . ' --> ' . $result, 'info');
            self::$_snmp_error = false;
            return $result;
        }
    }

    public static function setOID($_oid, $_type, $_value)
    {
        self::$_snmp_error = true;
        self::$_snmp_error_message = '';
        logSNMP3(__('setOID', __FILE__) . ' ' . ' ' . $_oid . ' ' . __('type', __FILE__) . ' ' . $_type . ' ' . __('valeur', __FILE__) . ' ' . $_value, 'info');
        if (self::$_session == null) {
            self::$_snmp_error_message = __('Fonction', __FILE__) . ' set: ' . __('Session non initialisée', __FILE__);
            logSNMP3(self::$_snmp_error_message, 'error');
            return false;
        }
        try {
            error_reporting(E_ALL & ~E_WARNING); // désactive les warnings PHP
            $result = self::$_session->set($_oid, $_type, $_value);
            error_reporting(E_ALL); // réactive les warnings PHP
        } catch (\Throwable $e) {
            self::$_snmp_error_message = __('Fonction', __FILE__) . ' set: ' . __('erreur (exception)', __FILE__) . ' ' . $e->getCode() . ' ' . $e->getMessage();
            logSNMP3(self::$_snmp_error_message, 'error');
            return false;
        }

        if (self::$_session->getErrno()  != '0') {
            self::$_snmp_error_message = __('Fonction', __FILE__) . ' set: ' . __('erreur', __FILE__) . ' ' . self::$_session->getErrno() . ' '  . self::$_session->getError();
            logSNMP3(self::$_snmp_error_message, 'error');
            return false;
        } else {
            logSNMP3('setOID' . ' ' . $_oid . ' --> ' . $_value, 'info');
            return true;
        }
    }

    public function create_command($_oid, $info, $action, $refresh)
    {
        logSNMP3('create_command' . ' ' . $this->getName() . ' OID ' . $_oid . ' Info ' . $info . ' Action ' . $action . ' Refresh ' . $refresh, 'info');

        if (SNMP3::openSession($this)) {

            if ($info != '') {
                $return = $this->create_info_command($_oid);
            }
            if ($action != '') {
                $return = $this->create_action_command($_oid);
            }
            if ($refresh != '') {
                $return = $this->create_refresh_command($_oid);
            }
            SNMP3::closeSession();
            return $return;
        } else {
            $error = __('Impossible de créer la session', __FILE__);
            return 'KO ' . $error;
        }
    }

    private function create_info_command($_oid)
    // crée la commande type info
    {
        if (is_object(cmd::byEqLogicIdAndLogicalId($this->getId(), $_oid))) {
            logSNMP3('create_info_command ' . $this->getName() . ' ' . __('commande déjà créée', __FILE__) . ' ' . $_oid, 'info');
            return '0';
        }

        // lit l'OID
        SNMP3::getOID($_oid);
        if (self::$_snmp_error == true) {
            return 'KO ' . self::$_snmp_error_message;
        }

        $name = $_oid;

        logSNMP3('create_info_command ' . $this->getName() . ' ' . __('création commande', __FILE__) . ' ' . $name, 'info');

        $cmd = new SNMP3Cmd();

        // BD: pour éviter les problèmes de conversion par exemple quand le nom contient le caractere /
        $cmd->setName($name);
        $name = $cmd->getName();

        $cmd->setName(SNMP3_getUniqueCmdName($this->getId(), $name));

        // crée la commande de type INFO
        $cmd->setEqLogic_id($this->getId());
        $cmd->setLogicalId($_oid); // le logical id est égal à l'id de l'OID
        $cmd->setConfiguration('infoId', $_oid);
        $cmd->setIsVisible(1);
        $cmd->setConfiguration('isPrincipale', '0');
        $cmd->setOrder(time());
        $cmd->setConfiguration('isCollected', '1');
        $cmd->setConfiguration('internal_type', 'OID');
        $cmd->setTemplate('dashboard', 'core::line');
        $cmd->setTemplate('mobile', 'core::line');
        $cmd->setType('info');
        $cmd->setDisplay('generic_type', 'GENERIC_INFO');
        $cmd->setSubType('string');

        $cmd->save();
        return 'OK ';
    }

    private function create_refresh_command($_oid)
    // crée la commande type refresh
    {

        if (is_object(cmd::byEqLogicIdAndLogicalId($this->getId(), 'R_' . $_oid))) {
            logSNMP3('create_refresh_command ' . $this->getName() . ' ' . __('commande refresh déjà créée', __FILE__) . ' ' . 'R_' . $_oid, 'info');
            return '0';
        }

        // lit l'OID
        SNMP3::getOID($_oid);
        if (self::$_snmp_error == true) {
            return 'KO ' . self::$_snmp_error_message;
        }

        $cmd_info = cmd::byEqLogicIdAndLogicalId($this->getId(), $_oid);
        if (is_object($cmd_info)) {
            $name = $cmd_info->getName(); // commande info liée
        } else {
            $name = $_oid;
        }
        $name = $name . ' Refresh';

        logSNMP3('create_refresh_command ' . $this->getName() . ' ' . __('création commande', __FILE__) . ' ' . $name, 'info');

        $cmd = new SNMP3Cmd();

        // BD: pour éviter les problèmes de conversion par exemple quand le nom contient le caractere /
        $cmd->setName($name);
        $name = $cmd->getName();
        $cmd->setName(SNMP3_getUniqueCmdName($this->getId(), $name));

        $cmd->setEqLogic_id($this->getId());
        $cmd->setLogicalId('R_' . $_oid); // le logical id est égal à 'R_' plus l'id de l'OID
        $cmd->setConfiguration('infoId', $_oid);
        $cmd->setIsVisible(1);
        $cmd->setOrder(time());
        $cmd->setConfiguration('internal_type', 'R_OID');
        $cmd->setType('action');
        $cmd->setSubType('other');
        $cmd->save();
        return 'OK ';
    }


    private function create_action_command($_oid)
    // crée la commande type action
    {

        if (is_object(cmd::byEqLogicIdAndLogicalId($this->getId(), 'A_' . $_oid))) {
            logSNMP3('create_action_command ' . $this->getName() . ' ' . __('commande action déjà créée', __FILE__) . ' ' . 'A_' . $_oid, 'info');
            return '0';
        }

        // lit l'OID
        SNMP3::getOID($_oid);
        if (self::$_snmp_error == true) {
            return 'KO ' . self::$_snmp_error_message;
        }

        $cmd_info = cmd::byEqLogicIdAndLogicalId($this->getId(), $_oid);
        if (is_object($cmd_info)) {
            $name = $cmd_info->getName(); // commande info liée
        } else {
            $name = $_oid;
        }

        $name = $name . ' Action';

        logSNMP3('create_action_command ' . $this->getName() . ' ' . __('création commande', __FILE__) . ' ' . $name, 'info');

        $cmd = new SNMP3Cmd();

        // BD: pour éviter les problèmes de conversion par exemple quand le nom contient le caractere /
        $cmd->setName($name);
        $name = $cmd->getName();


        $cmd->setName(SNMP3_getUniqueCmdName($this->getId(), $name));

        $cmd->setEqLogic_id($this->getId());
        $cmd->setLogicalId('A_' . $_oid); // le logical id est égal à 'A_' plus l'id de l'OID
        $cmd->setConfiguration('infoId', $_oid);
        $cmd->setIsVisible(1);
        if (is_object($cmd_info)) {
            $cmd->setValue($cmd_info->getID()); // commande info liée
        }
        $cmd->setOrder(time());
        $cmd->setConfiguration('internal_type', 'A_OID');

        $cmd->setType('action');
        $cmd->setSubType('message');

        $cmd->save();
        return 'OK ';
    }



    public function preInsert()
    {
        if ($this->getConfiguration('type', '') == "") {
            $this->setConfiguration('type', 'SNMP3');
        }
        if ($this->getConfiguration('timeout', '') == "") {
            $this->setConfiguration('timeout', '-1');
        }
        if ($this->getConfiguration('retries', '') == "") {
            $this->setConfiguration('retries', '3');
        }
    }

    public function postInsert()
    {
        $this->postUpdate();
    }

    public function postUpdate()
    {
        unset($cmd);
        $cmd = $this->getCmd(null, 'updatetime');
        if (!is_object($cmd)) {
            $cmd = new SNMP3Cmd();
            $cmd->setName('Dernier refresh');
            $cmd->setEqLogic_id($this->getId());
            $cmd->setLogicalId('updatetime');
            $cmd->setUnite('');
            $cmd->setType('info');
            $cmd->setSubType('string');
            $cmd->setIsHistorized(0);
            $cmd->save();
        }

        unset($cmd);
        $cmd = $this->getCmd(null, 'Refresh');
        if (!is_object($cmd)) {
            $cmd = new SNMP3Cmd();
            $cmd->setName('Refresh');
            $cmd->setEqLogic_id($this->getId());
            $cmd->setType('action');
            $cmd->setSubType('other');
            $cmd->setLogicalId('refresh');
            $cmd->setIsVisible(1);
            $cmd->setDisplay('generic_type', 'GENERIC_INFO');
            $cmd->save();
        }
    }

    public static function cron()
    {
        $cron_SNMP3 = cron::byClassAndFunction('SNMP3', 'update');
        if (!is_object($cron_SNMP3)) {
            logSNMP3(__('Lancement de', __FILE__) . ' ' . 'cron', 'info');
            SNMP3::update();
        }
    }

    public static function update()
    {
        logSNMP3(__('Lancement de', __FILE__) . ' ' . 'update', 'info');
        foreach (eqLogic::byTypeAndSearchConfiguration('SNMP3', '"type":"SNMP3"') as $eqLogic) {
            if ($eqLogic->getIsEnable()) {
                SNMP3::SNMP3_Update($eqLogic);
            }
        }
    }


    public static function SNMP3_Update($_eqLogic, $_context = 'cron')
    {
        logSNMP3('SNMP3_Update SNMP3 : ' . $_eqLogic->getName() . ' (' . $_context . ')', 'info');
        if (SNMP3::openSession($_eqLogic)) {
            $retry = $_eqLogic->getConfiguration('retries');
            if (is_numeric($retry) == false) {
                $retry = 1;
            } else {
                if ($retry <= 0) {
                    $retry = 1;
                }
            }
            foreach ($_eqLogic->getCmd() as $cmd) {
                if ($cmd->getConfiguration('internal_type') == 'OID' && $cmd->getConfiguration('isCollected') == 1) {
                    $run = false;
                    if ($_context == 'refresh') {
                        $run = true;
                    } else {
                        $autorefresh = '';
                        switch ($cmd->getConfiguration('cron')) {
                            case "cron":
                                $autorefresh = '*/1 * * * *';
                                break;
                            case "cron5":
                                $autorefresh = '*/5 * * * *';
                                break;
                            case "cron10":
                                $autorefresh = '*/10 * * * *';
                                break;
                            case "cron15":
                                $autorefresh = '*/15 * * * *';
                                break;
                            case "cron30":
                                $autorefresh = '*/30 * * * *';
                                break;
                            case "cronHourly":
                                $autorefresh = '0 * * * *';
                                break;
                            case "cronDaily":
                                $autorefresh = '0 0 * * *';
                                break;
                        }
                        if ($autorefresh != '') {
                            $c = new Cron\CronExpression($autorefresh, new Cron\FieldFactory);
                            if ($c->isDue()) {
                                $run = true;
                            }
                        }
                    }

                    if ($run == true) {
                        if ($_eqLogic->refresh_info_cmd($cmd, $retry) == true) {
                            $_eqLogic_refresh_cmd = $_eqLogic->getCmd(null, 'updatetime');
                            $_eqLogic->checkAndUpdateCmd($_eqLogic_refresh_cmd, date("d/m/Y H:i", (time())));
                        }
                    }
                }
            }
            SNMP3::closeSession();
        }
    }

    function refresh_info_cmd($_cmd, $_retry)
    {
        logSNMP3($_cmd->getName() . ': ' . __('Lecture', __FILE__) . ' OID ' . $_cmd->getLogicalId() . ' ' . $_cmd->getName(), 'info');
        $_oid = $_cmd->getLogicalId();
        // lit l'OID
        $value = SNMP3::getOID($_oid, $_retry);
        if (self::$_snmp_error == false) {
            $eqLogic = $_cmd->getEqlogic();
            $eqLogic->checkAndUpdateCmd($_cmd, $value);
            return true;
        } else {
            return false;
        }
    }
}

function SNMP3_getUniqueCmdName($eqLogicId, $name)
{
    // teste si le nom de la commande est déjà attribué
    // si oui, ajoute à la fin un numéro afin d'avoir un nom unique
    if (!is_object(cmd::byEqLogicIdCmdName($eqLogicId, $name))) {
        return $name;
    }

    $count = 1;
    while (is_object(cmd::byEqLogicIdCmdName($eqLogicId, substr($name, 0, 100) . "..." . $count))) {
        $count++;
    }
    $name = substr($name, 0, 100) . "..." . $count;
    logSNMP3(__('Renomme en', __FILE__) . ' ' . $name, 'info');
    return $name;
}

class SNMP3Cmd extends cmd
{

    public function execute($_options = null)
    {

        // Refresh toutes les infos
        $eqLogic = $this->getEqLogic();
        if (!is_object($eqLogic) || $eqLogic->getIsEnable() != 1) {
            throw new \Exception(__('Equipement desactivé impossible d\éxecuter la commande ', __FILE__) . ' : ' . $this->getHumanName());
        }

        // Refresh toutes les infos
        if ($this->getLogicalId() == 'refresh') {
            logSNMP3('execute refresh');
            SNMP3::SNMP3_Update($eqLogic, 'refresh');
            return true;
        }


        // Commande action
        if (substr($this->getLogicalId(), 0, 2) == 'A_') {
            $oid = substr($this->getLogicalId(), 2); // remove 'A_'

            switch ($this->getSubType()) {
                case "select":
                    $type = 's';  // string
                    $value = $_options['select'];
                    break;
                case "slider":
                    $type = 'd';  // decimal
                    $value = $_options['slider'];
                    break;
                case "message":
                    $type = $_options['title'];
                    if ($type == '') {
                        $type = 's';
                    }
                    $value = $_options['message'];
                    break;
                default:
                    $error = __('Type d\'action non défini ', __FILE__) . ' : ' . $this->getSubType();
                    logSNMP3($error, 'warning');
                    throw new \Exception($error);
                    break;
            }

            $return = false;
            if (SNMP3::openSession($eqLogic, 'RW')) {
                // update l'OID
                $return = SNMP3::setOID($oid, $type, $value);
                SNMP3::closeSession();
            }

            if ($return == true) {
                logSNMP3(__('MAJ', __FILE__) . ' ' . $_oid . ' OK', 'info');
            } else {
                $error = 'OID: ' . $oid . ' ' . __('erreur:', __FILE__) . ' ' . SNMP3::$_snmp_error_message;
                logSNMP3($error);
                throw new Exception($error);
            }
            return $return;
        }

        // Commande refresh
        if (substr($this->getLogicalId(), 0, 2) == 'R_') {
            $oid = substr($this->getLogicalId(), 2); // remove 'R_'

            $cmd = cmd::byEqLogicIdAndLogicalId($eqLogic->getId(), $oid);
            if (!is_object($cmd)) {
                $error = 'OID ' . $oid . ' ' . __('non trouvé', __FILE__);
                logSNMP3($error);
                throw new Exception($error);
                $return = false;
            }
            if (SNMP3::openSession($eqLogic)) {
                $return = $eqLogic->refresh_info_cmd($cmd);
                SNMP3::closeSession();
                if ($return == false) {
                    $error = 'OID: ' . $oid . ' ' . __('erreur', __FILE__) . ' ' . SNMP3::$_snmp_error_message;
                    logSNMP3($error);
                    throw new Exception($error);
                }
            }
            return $return;
        }
    }


    public function dontRemoveCmd()
    {

        if ($this->getLogicalId() == 'updatetime' || $this->getLogicalId() == 'refresh') {
            return true;
        }
        return false;
    }
}
function logSNMP3($_error, $_type = 'error')
{
    log::add('SNMP3', $_type, $_error);
}
