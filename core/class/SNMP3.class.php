<?php

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

    //snmpget -v 3 -n "" -u admin_snmp_2024 -a MD5 -A "Camille" -x DES -X "Camille" -l authPriv 192.168.1.5 .1.3.6.1.4.1.6574.1.5.1.0

    public function test_connexion()
    {

        //snmpget -v 3 -n "" -u admin_snmp_2024 -a MD5 -A "Camille" -x DES -X "Camille" -l authPriv 192.168.1.5 .1.3.6.1.4.1.6574.1.5.1.0


        log::add('SNMP3', 'info', __('test_connexion ', __FILE__));

        $version = $this->getConfiguration('version');
        if ($version == '3') {
            $community = $this->getConfiguration('security_name');
        } else {
            $community = $this->getConfiguration('community');
        }

        try {
            $session = new SNMP(
                $version,
                $this->getConfiguration('localhost'),
                $community,
                $this->getConfiguration('timeout'),
                $this->getConfiguration('retries')
            );
        } catch (SNMPException $e) {
            $error = 'SNMP session creation error ' . $e->getCode() . ' ' . $e->getMessage();
            log::add('SNMP3', 'error', $error);
            $error = 'Connexion KO : ' . $error;
            throw new Exception($error);
        }
        if ($session->getErrno()  != '0') {
            $error = 'SNMP session creation error ' . $session->getErrno() . ' '  . $session->getError();
            log::add('SNMP3', 'error', $error);
            $error = 'Connexion KO : ' . $error;
            throw new Exception($error);
        }

        $session->valueretrieval = SNMP_VALUE_PLAIN;

        try {

            $result = $session->setSecurity(
                $this->getConfiguration('security_level'),
                $this->getConfiguration('auth_protocol'),
                $this->getConfiguration('auth_passphrase'),
                $this->getConfiguration('privacy_protocol'),
                $this->getConfiguration('privacy_passphrase'),
                $this->getConfiguration('context_name'),
                $this->getConfiguration('context_engineid'),
            );
        } catch (SNMPException $e) {
            $error = 'setSecurity error ' . $e->getCode() . ' ' . $e->getMessage();
            log::add('SNMP3', 'error', $error);
            $session->close();
            $error = 'Connexion KO : ' . $error;
            throw new Exception($error);
        }
        if ($session->getErrno()  != '0') {
            $error = 'setSecurity error ' . $e->getCode() . ' ' . $e->getMessage();
            log::add('SNMP3', 'error', $error);
            $session->close();
            $error = 'Connexion KO : ' . $error;
            throw new Exception($error);
        }

        $oid = '1.3.6.1.2.1.1.6.0';
        try {
            $sysLocation = $session->get($oid);
        } catch (SNMPException $e) {
            $error = 'get error ' . $e->getCode() . ' ' . $e->getMessage();
            log::add('SNMP3', 'error', $error);
            $session->close();
            $error = 'Connexion KO : ' . $error;
            throw new Exception($error);
        }

        if ($session->getErrno()  != '0') {
            $error = 'get error ' . $session->getErrno() . ' '  . $session->getError();
            log::add('SNMP3', 'error', $error);
            $session->close();
            $error = 'Connexion KO : ' . $error;
            throw new Exception($error);
        } else {
            log::add('SNMP3', 'info', 'Connexion OK : sysLocation' . $sysLocation);
            $session->close();
            event::add(
                'jeedom::alert',
                array(
                    'level' => 'success',
                    'page' => 'SNMP3',
                    'message' => 'Connexion OK : sysLocation -> ' . $sysLocation,
                )
            );
        }
    }

    public static function loadMIBS()
    {

        log::add('SNMP3', 'debug', __('loadMIBS ', __FILE__));

        // load all MIBS in plugins/SNMP3/data/mibs directory
        $dirPath = __DIR__ . '/../../data/mibs';
        $files = glob($dirPath . "/*.txt");
        foreach ($files as $mib_file) {
            if (is_file($mib_file)) {
                if (snmp_read_mib($mib_file) == false) {
                    $error = 'Cannot load mib ' . $mib_file;
                    log::add('SNMP3', 'error', $error);
                } else {
                    log::add('SNMP3', 'info', 'MIB ' . $mib_file . ' loaded');
                }
            }
        }
    }

    public static function openSession($_eqLogic)
    {
        // load MIBS
        SNMP3::loadMIBS();

        log::add('SNMP3', 'debug', __('openSession ', __FILE__));

        $version = $_eqLogic->getConfiguration('version');
        if ($version == '3') {
            $community = $_eqLogic->getConfiguration('security_name');
        } else {
            $community = $_eqLogic->getConfiguration('community');
        }

        try {
            self::$_session = new SNMP(
                $version,
                $_eqLogic->getConfiguration('localhost'),
                $community,
                $_eqLogic->getConfiguration('timeout'),
                $_eqLogic->getConfiguration('retries')
            );
        } catch (SNMPException $e) {
            $error = 'SNMP session creation error ' . $e->getCode() . ' ' . $e->getMessage();
            log::add('SNMP3', 'error', $error);
            return false;
        }
        if (self::$_session->getErrno()  != '0') {
            $error = 'SNMP session creation error ' . self::$_session->getErrno() . ' '  . self::$_session->getError();
            log::add('SNMP3', 'error', $error);
            return false;
        }

        self::$_session->valueretrieval = SNMP_VALUE_PLAIN;

        try {

            $result = self::$_session->setSecurity(
                $_eqLogic->getConfiguration('security_level'),
                $_eqLogic->getConfiguration('auth_protocol'),
                $_eqLogic->getConfiguration('auth_passphrase'),
                $_eqLogic->getConfiguration('privacy_protocol'),
                $_eqLogic->getConfiguration('privacy_passphrase'),
                $_eqLogic->getConfiguration('context_name'),
                $_eqLogic->getConfiguration('context_engineid'),
            );
        } catch (SNMPException $e) {
            $error = 'setSecurity error ' . $e->getCode() . ' ' . $e->getMessage();
            log::add('SNMP3', 'error', $error);
            self::$_session->close();
            return false;
        }
        if (self::$_session->getErrno()  != '0') {
            $error = 'setSecurity error ' . self::$_session->getErrno() . ' '  . self::$_session->getError();;
            log::add('SNMP3', 'error', $error);
            self::$_session->close();
            return false;
        }
        if (self::$_session == null) {
            log::add('SNMP3', 'info', 'Session not created');
        }
        return true;
    }
    public static function closeSession()
    {

        log::add('SNMP3', 'debug', __('closeSession ', __FILE__));
        if (self::$_session != null) {
            @self::$_session->close();
        }
    }

    public static function getOID($_oid)
    {
        log::add('SNMP3', 'debug', __('getOID ', __FILE__) . ' ' . $_oid);
        self::$_snmp_error = true;
        self::$_snmp_error_message = '';
        if (self::$_session == null) {
            self::$_snmp_error_message = 'Function get: Session not initialized';
            log::add('SNMP3', 'error', self::$_snmp_error_message);
            return false;
        }
        try {
            $result = self::$_session->get($_oid);
        } catch (SNMPException $e) {
            self::$_snmp_error_message = 'Function get: error (exception) ' . $e->getCode() . ' ' . $e->getMessage();
            log::add('SNMP3', 'error', self::$_snmp_error_message);
            return false;
        }

        if (self::$_session->getErrno()  != '0') {
            self::$_snmp_error_message = 'Function get: error ' . self::$_session->getErrno() . ' '  . self::$_session->getError();
            log::add('SNMP3', 'error', self::$_snmp_error_message);
            return false;
        } else {
            log::add('SNMP3', 'info', __('getOID ', __FILE__) . ' ' . $_oid . ' --> ' . $result);
            self::$_snmp_error = false;
            return $result;
        }
    }

    public static function setOID($_oid, $_type, $_value)
    {
        self::$_snmp_error = true;
        self::$_snmp_error_message = '';
        log::add('SNMP3', 'info', __('setOID ', __FILE__) . ' ' . $_oid . ' type ' . $_type . ' value ' . $_value);
        if (self::$_session == null) {
            self::$_snmp_error_message = 'Function set: Session not initialized';
            log::add('SNMP3', 'error', self::$_snmp_error_message);
            return false;
        }
        try {
            $result = self::$_session->set($_oid, $_type, $_value);
        } catch (SNMPException $e) {
            self::$_snmp_error_message = 'Function set: error (exception) ' . $e->getCode() . ' ' . $e->getMessage();
            log::add('SNMP3', 'error', self::$_snmp_error_message);
            return false;
        }

        if (self::$_session->getErrno()  != '0') {
            self::$_snmp_error_message = 'Function set: error ' . self::$_session->getErrno() . ' '  . self::$_session->getError();
            log::add('SNMP3', 'error', self::$_snmp_error_message);
            return false;
        } else {
            log::add('SNMP3', 'info', __('setOID ', __FILE__) . ' ' . $_oid . ' --> ' . $_value);
            return true;
        }
    }

    public function create_command($_oid, $info, $action, $refresh)
    {
        log::add('SNMP3', 'info', __('create_command', __FILE__) . ' ' . $this->name . ' OID ' . $_oid . ' Info ' . $info . ' Action ' . $action . ' Refresh ' . $refresh);

        if (SNMP3::openSession($this)) {

            if ($info != '') {
                $this->create_info_command($_oid);
            }
            if ($action != '') {
                $this->create_action_command($_oid);
            }
            if ($refresh != '') {
                $this->create_refresh_command($_oid);
            }
            SNMP3::closeSession();
        } else {
            $error = 'Cannot create sesion';
            throw new Exception($error);
        }
    }

    private function create_info_command($_oid)
    // crée la commande type info
    {
        if (is_object(cmd::byEqLogicIdAndLogicalId($this->getId(), $_oid))) {
            log::add('SNMP3', 'info', __('create_info_command ', __FILE__) . $this->name . '  commande déjà créée ' . $_oid);
            return '0';
        }

        // lit l'OID
        SNMP3::getOID($_oid);
        if (self::$_snmp_error == true) {
            throw new Exception(self::$_snmp_error_message);
        }

        $name = $_oid;

        log::add('SNMP3', 'info', __('create_info_command ', __FILE__) . $this->name . '  création commande ' . $name);

        $cmd = new SNMP3Cmd();

        // BD: pour éviter les problèmes de conversion par exemple quand le nom contient le caractere /
        $cmd->setName($name);
        $name = $cmd->getName();

        // teste si le nom de la commande est déjà attribué
        // si oui, ajoute à la fin un numéro afin d'avoir un nom unique
        if (is_object(cmd::byEqLogicIdCmdName($this->getId(), $name))) {
            $count = 1;
            while (is_object(cmd::byEqLogicIdCmdName($this->getId(), substr($name, 0, 100) . "..." . $count))) {
                $count++;
            }
            $cmd->setName(substr($name, 0, 1) . "..." . $count);
            log::add('SNMP3', 'info', 'Rename as ' . substr($name, 0, 100) . "..." . $count);
        } else {
            $cmd->setName($name);
        }

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
    }

    private function create_refresh_command($_oid)
    // crée la commande type refresh
    {

        if (is_object(cmd::byEqLogicIdAndLogicalId($this->getId(), 'R_' . $_oid))) {
            log::add('SNMP3', 'info', __('create_refresh_command ', __FILE__) . $this->name . '  commande refresh déjà créée ' . 'R_' . $_oid);
            return '0';
        }

        // lit l'OID
        SNMP3::getOID($_oid);
        if (self::$_snmp_error == true) {
            throw new Exception(self::$_snmp_error_message);
        }

        $cmd_info = cmd::byEqLogicIdAndLogicalId($this->getId(), $_oid);
        if (is_object($cmd_info)) {
            $name = $cmd_info->getName(); // cmmande info liée
        } else {
            $name = $_oid;
        }
        $name = $name . ' Refresh';

        log::add('SNMP3', 'info', __('create_refresh_command ', __FILE__) . $this->name . '  création commande ' . $name);

        $cmd = new SNMP3Cmd();

        // BD: pour éviter les problèmes de conversion par exemple quand le nom contient le caractere /
        $cmd->setName($name);
        $name = $cmd->getName();

        // teste si le nom de la commande est déjà attribué    
        // si oui, ajoute à la fin un numéro afin d'avoir un nom unique
        if (is_object(cmd::byEqLogicIdCmdName($this->getId(), $name))) {
            $count = 1;
            while (is_object(cmd::byEqLogicIdCmdName($this->getId(), substr($name, 0, 100) . "..." . $count))) {
                $count++;
            }
            $cmd->setName(substr($name, 0, 100) . "..." . $count);
            log::add('SNMP3', 'info', 'Rename as ' . substr($name, 0, 100) . "..." . $count);
        } else {
            $cmd->setName($name);
        }
        $cmd->setEqLogic_id($this->getId());
        $cmd->setLogicalId('R_' . $_oid); // le logical id est égal à 'R_' plus l'id de l'OID
        $cmd->setConfiguration('infoId', $_oid);
        $cmd->setIsVisible(1);
        $cmd->setOrder(time());
        $cmd->setConfiguration('internal_type', 'R_OID');
        $cmd->setType('action');
        $cmd->setSubType('other');
        $cmd->save();
    }


    private function create_action_command($_oid)
    // crée la commande type action
    {

        if (is_object(cmd::byEqLogicIdAndLogicalId($this->getId(), 'A_' . $_oid))) {
            log::add('SNMP3', 'info', __('create_action_command ', __FILE__) . $this->name . '  commande action déjà créée ' . 'A_' . $_oid);
            return '0';
        }

        // lit l'OID
        SNMP3::getOID($_oid);
        if (self::$_snmp_error == true) {
            throw new Exception(self::$_snmp_error_message);
        }

        $cmd_info = cmd::byEqLogicIdAndLogicalId($this->getId(), $_oid);
        if (is_object($cmd_info)) {
            $name = $cmd_info->getName(); // cmmande info liée
        } else {
            $name = $_oid;
        }

        $name = $name . ' Action';

        log::add('SNMP3', 'info', __('create_action_command ', __FILE__) . $this->name . '  création commande ' . $name);

        $cmd = new SNMP3Cmd();

        // BD: pour éviter les problèmes de conversion par exemple quand le nom contient le caractere /
        $cmd->setName($name);
        $name = $cmd->getName();

        // teste si le nom de la commande est déjà attribué    
        // si oui, ajoute à la fin un numéro afin d'avoir un nom unique
        if (is_object(cmd::byEqLogicIdCmdName($this->getId(), $name))) {
            $count = 1;
            while (is_object(cmd::byEqLogicIdCmdName($this->getId(), substr($name, 0, 100) . "..." . $count))) {
                $count++;
            }
            $cmd->setName(substr($name, 0, 100) . "..." . $count);
            log::add('SNMP3', 'info', 'Rename as ' . substr($name, 0, 100) . "..." . $count);
        } else {
            $cmd->setName($name);
        }

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
            $this->setConfiguration('retries', '-1');
        }
    }

    public function preUpdate()
    {
        if ($this->getIsEnable()) {
        }
    }

    public function preSave()
    {
        if ($this->getIsEnable()) {
        }
    }

    public function preRemove()
    {

        return true;
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



    public function cron()
    {
        log::add('SNMP3', 'info', 'Lancement de cron');
        SNMP3::cron_update(__FUNCTION__);
    }
    public function cron5()
    {
        sleep(5);
        log::add('SNMP3', 'info', 'Lancement de cron5');
        SNMP3::cron_update(__FUNCTION__);
    }
    public function cron10()
    {
        sleep(10);
        log::add('SNMP3', 'info', 'Lancement de cron10');
        SNMP3::cron_update(__FUNCTION__);
    }
    public function cron15()
    {
        sleep(15);
        log::add('SNMP3', 'info', 'Lancement de cron15');
        SNMP3::cron_update(__FUNCTION__);
    }
    public function cron30()
    {
        sleep(20);
        log::add('SNMP3', 'info', 'Lancement de cron30');
        SNMP3::cron_update(__FUNCTION__);
    }

    public function cronHourly()
    {
        sleep(25);
        log::add('SNMP3', 'info', 'Lancement de cronHourly');
        SNMP3::cron_update(__FUNCTION__);
    }

    public function cronDaily()
    {
        sleep(30);
        log::add('SNMP3', 'info', 'Lancement de cronDaily');
        SNMP3::cron_update(__FUNCTION__);
    }
    public function cron_update($_cron)
    {
        foreach (eqLogic::byTypeAndSearchConfiguration('SNMP3', '"type":"SNMP3"') as $eqLogic) {
            if ($eqLogic->getIsEnable()) {
                SNMP3::SNMP3_Update($eqLogic, $_cron);
            }
        }
    }

    public function SNMP3_Update($_eqLogic, $_cron)
    {
        log::add('SNMP3', 'info', 'SNMP3_Update SNMP3 : ' . $_eqLogic->getName() . ' cron ' . $_cron);
        if (SNMP3::openSession($_eqLogic)) {
            $_eqLogic_refresh_cmd = $_eqLogic->getCmd(null, 'updatetime');
            foreach ($_eqLogic->getCmd() as $cmd) {
                if ($cmd->getConfiguration('internal_type') == 'OID' && $cmd->getConfiguration('isCollected') == 1 && ($cmd->getConfiguration('cron') == $_cron || $_cron == 'refresh')) {
                    if ($_eqLogic->refresh_info_cmd($cmd) == true) {
                        $_eqLogic_refresh_cmd->event(date("d/m/Y H:i", (time())));
                    }
                }
            }
            SNMP3::closeSession();
        }
    }

    function refresh_info_cmd($_cmd)
    {
        log::add('SNMP3', 'debug', 'Read OID ' . $_cmd->getLogicalId() . ' ' . $_cmd->getName());
        $_oid = $_cmd->getLogicalId();
        // lit l'OID
        $value = SNMP3::getOID($_oid);
        if (self::$_snmp_error == false) {
            $_cmd->event($value);
            return true;
        } else {
            return false;
        }
    }
}

class SNMP3Cmd extends cmd
{

    public function execute($_options = null)
    {

        // Refresh toutes les infos
        $eqLogic = $this->getEqLogic();
        if (!is_object($eqLogic) || $eqLogic->getIsEnable() != 1) {
            throw new \Exception(__('Equipement desactivé impossible d\éxecuter la commande : ' . $this->getHumanName(), __FILE__));
        }

        // Refresh toutes les infos
        if ($this->getLogicalId() == 'refresh') {
            log::add('SNMP3', 'info', __('execute ', __FILE__) . '  refresh');
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
                    $error = 'Type d action non défini : ' . $this->getSubType();
                    log::add('SNMP3', 'warning', $error);
                    throw new \Exception($error);
                    break;
            }

            $return = false;
            if (SNMP3::openSession($eqLogic)) {
                // update l'OID
                $return = SNMP3::setOID($oid, $type, $value);
                SNMP3::closeSession();
            }

            if ($return == true) {
                log::add('SNMP3', 'info', 'MAJ ' . $_oid . ' OK');
            } else {
                $error = 'OID: ' . $oid . ' error ' . SNMP3::$_snmp_error_message;
                log::add('SNMP3', 'error', $error);
                throw new Exception($error);
            }
            return $return;
        }

        // Commande refresh
        if (substr($this->getLogicalId(), 0, 2) == 'R_') {
            $oid = substr($this->getLogicalId(), 2); // remove 'R_'

            $cmd = cmd::byEqLogicIdAndLogicalId($eqLogic->getId(), $oid);
            if (!is_object($cmd)) {
                $error = 'OID non trouvé ' . $oid;
                log::add('SNMP3', 'debug', $error);
                throw new Exception($error);
                $return = false;
            }
            if (SNMP3::openSession($eqLogic)) {
                $return = $eqLogic->refresh_info_cmd($cmd);
                SNMP3::closeSession();
                if ($return == false) {
                    $error = 'OID: ' . $oid . ' error ' . SNMP3::$_snmp_error_message;
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
