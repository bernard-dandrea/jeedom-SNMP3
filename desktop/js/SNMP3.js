/* This file is part of Jeedom.
*
// Last Modified : 2026/08/03 11:30:41

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

// affiche les champs de configuration en fonction du protocole SNMP choisi
document.querySelector('.eqLogicAttr[data-l1key="configuration"][data-l2key="version"]')
    .addEventListener('change', function () {
        document.querySelectorAll('.snmp_protocole').forEach(el => {
            el.style.display = 'none';
        });
        document.querySelectorAll('.snmp_' + this.value).forEach(el => {
            el.style.display = '';
        });
    });

    
/* Fonction permettant l'affichage des commandes dans l'équipement */
function addCmdToTable(_cmd) {

    if (document.getElementById('table_cmd') == null) return
    if (document.querySelector('#table_cmd thead') == null) {
        table = '<thead>'
        table += '<tr>'
        table += '<th style="min-width:50px;width:70px;">ID</th>'
        table += '<th>{{Nom}}</th>'
        table += '<th>logicalID</th>'
        table += '<th>{{Type}}</th>'
        table += '<th style="min-width:260px;">{{Options}}</th>'
        table += '<th>{{Scan}}</th>'
        table += '<th>{{Valeur}}'
        table += '</th>'
        table += '<th style="min-width:80px;width:200px;">{{Actions}}</th>'
        table += '</tr>'
        table += '</thead>'
        table += '<tbody>'
        table += '</tbody>'
        document.getElementById('table_cmd').insertAdjacentHTML('beforeend', table)
    }

    if (!isset(_cmd)) {
        var _cmd = { configuration: {} }
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {}
    }

    let logicalId = init(_cmd.logicalId);

    let internal_type = _cmd.configuration.internal_type;
    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">'
    tr += '<td>'
    tr += '<span class="cmdAttr" data-l1key="id"></span>'
    tr += '</td>'

    tr += '<td>';
    tr += '<div class="input-group">'
    tr += '<input class="cmdAttr form-control input-sm roundedLeft" data-l1key="name" placeholder="{{Nom de la commande}}">'
    tr += '<span class="input-group-btn"><a class="cmdAction btn btn-sm btn-default" data-l1key="chooseIcon" title="{{Choisir une icône}}"><i class="fas fa-icons"></i></a></span>'
    tr += '<span class="cmdAttr input-group-addon roundedRight" data-l1key="display" data-l2key="icon" style="font-size:19px;padding:0 5px 0 0!important;"></span>'
    tr += '</div>'
    // affiche la commande liée uniquement pour les commandes actions
    if (logicalId.length >= 3 && logicalId.substr(0, 2) == 'A_') {
        tr += '<select class="hidden-xs cmdAttr form-control input-sm" data-l1key="value" style="display:none;margin-top:5px;" title="{{Commande info liée}}">'
        tr += '<option value="">{{Aucune}}</option>'
        tr += '</select>'
    }
    tr += '</td>';
    tr += '<td>';
    tr += '<input class="cmdAttr form-control input-sm " data-l1key="logicalId" placeholder="logicalID">'
    tr += '</td>';
    tr += '<td>'
    tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>'
    tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>'
    tr += '</td>'
    tr += '<td>'
    tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked/>{{Afficher}}</label> '
    tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isHistorized" checked/>{{Historiser}}</label> '
    tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="display" data-l2key="invertBinary"/>{{Inverser}}</label> '
    if (init(_cmd.type) == "info" && internal_type == "OID") {
        tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="configuration" data-l2key="isCollected" checked/>{{Update}}</label> ';
    }

    tr += '<div style="margin-top:7px;">'
    tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
    tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
    tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="unite" placeholder="Unité" title="{{Unité}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
    tr += '</div>'
    tr += '</td>'
    if (init(_cmd.type) == "info" && internal_type == "OID") {
        tr += '<td>';
        tr += '<select id="sel_cron" class="cmdAttr form-control" data-l1key="configuration" data-l2key="cron"> '
        tr += '<option value="none">{{Aucun}}</option> '
        tr += '<option value="cron">{{Toutes les minutes}}</option> '
        tr += '<option value="cron5">{{Toutes les 5 minutes}}</option> '
        tr += '<option value="cron10">{{Toutes les 10 minutes}}</option> '
        tr += '<option value="cron15">{{Toutes les 15 minutes}}</option> '
        tr += '<option value="cron30">{{Toutes les 30 minutes}}</option> '
        tr += '<option value="cronHourly">{{Toutes les heures}}</option> '
        tr += '<option value="cronDaily">{{Toutes les jours}}</option> '
        tr += '</select> '
        tr += '</td>';
    }
    else {
        tr += '<td>';
        tr += '</td>';
    }

    tr += '<td>';
    tr += '<span class="cmdAttr" data-l1key="htmlstate"></span>';
    tr += '</td>';
    tr += '<td>'

    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fas fa-cogs"></i></a> '
    if (init(_cmd.type) == "action") {
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> {{Tester}}</a>'
    }
    tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove" title="{{Supprimer la commande}}"></i></td>'
    tr += '</tr>'

    let newRow = document.createElement('tr')
    newRow.innerHTML = tr
    newRow.addClass('cmd')
    newRow.setAttribute('data-cmd_id', init(_cmd.id))
    document.getElementById('table_cmd').querySelector('tbody').appendChild(newRow)

    jeedom.eqLogic.buildSelectCmd({
        id: document.querySelector('.eqLogicAttr[data-l1key="id"]').jeeValue(),
        filter: { type: 'info' },
        error: function (error) {
            jeedomUtils.showAlert({ message: error.message, level: 'danger' })
        },
        success: function (result) {
            newRow.querySelector('.cmdAttr[data-l1key="value"]')?.insertAdjacentHTML('beforeend', result)
            newRow.setJeeValues(_cmd, '.cmdAttr')
            jeedom.cmd.changeType(newRow, init(_cmd.subType))
        }
    })


}


function printEqLogic(_eqLogic) {

    $SNMP3type = _eqLogic.configuration.type;
}


document.querySelector('#bt_TestConnexionSNMP3').addEventListener('click', function () {

    var eqLogicId = document.querySelector('.eqLogicAttr[data-l1key="id"]').value;

    var paramsAJAX = {
        type: "POST",
        url: 'plugins/SNMP3/core/ajax/SNMP3.ajax.php',
        data: {
            action: 'test_connexion',
            id: eqLogicId
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error)
        },
        success: function (data) {
            var message = data.result;
            var level = 'success';
            if (message.substr(0, 2) === 'KO') {
                level = 'warning';
            }
            if (message.length >= 4) {
                message = message.substr(3);
            }

            jeedomUtils.showAlert({
                message: message,
                level: level
            })
        }
    }
    domUtils.ajax(paramsAJAX);
});

document.querySelector('#bt_create_info_command').addEventListener('click', function () {

    var eqLogicId = document.querySelector('.eqLogicAttr[data-l1key="id"]').value;
    jeeDialog.prompt({
        message: 'OID ?'
    },
        function (result) {
            if (result === null)
                return
            if (result == '')
                result
            var paramsAJAX = {
                type: "POST",
                url: 'plugins/SNMP3/core/ajax/SNMP3.ajax.php',
                data: {
                    action: "create_command",
                    id: eqLogicId,
                    id_commande: result,
                    _info: 'X',
                    _action: '',
                    _refresh: '',
                },
                dataType: 'json',
                error: function (request, status, error) {
                    handleAjaxError(request, status, error)
                },
                success: function (data) {
                    if (data.state != 'ok') {
                        jeedomUtils.showAlert({
                            message: data.result,
                            level: 'danger'
                        })
                        return
                    }

                    var message = data.result;
                    var level = 'success';
                    if (message.substr(0, 2) === 'KO') {
                        level = 'warning';
                        if (message.length >= 4) {
                            message = message.substr(3);
                        }
                    }
                    else {
                        message = '{{OID créé}}'
                    }
                    jeedomUtils.showAlert({
                        message: message,
                        level: level
                    })

                    if (level === 'success')
                        window.location.reload();
                }
            }
            domUtils.ajax(paramsAJAX);
        })
});


document.querySelector('#bt_create_action_command').addEventListener('click', function () {

    var eqLogicId = document.querySelector('.eqLogicAttr[data-l1key="id"]').value;
    jeeDialog.prompt({
        message: 'OID ?'
    },
        function (result) {
            if (result === null)
                return
            if (result == '')
                result
            var paramsAJAX = {
                type: "POST",
                url: 'plugins/SNMP3/core/ajax/SNMP3.ajax.php',
                data: {
                    action: "create_command",
                    id: eqLogicId,
                    id_commande: result,
                    _info: '',
                    _action: 'X',
                    _refresh: '',
                },
                dataType: 'json',
                error: function (request, status, error) {
                    handleAjaxError(request, status, error)
                },
                success: function (data) {
                    if (data.state != 'ok') {
                        jeedomUtils.showAlert({
                            message: data.result,
                            level: 'danger'
                        })
                        return
                    }

                    var message = data.result;
                    var level = 'success';
                    if (message.substr(0, 2) === 'KO') {
                        level = 'warning';
                        if (message.length >= 4) {
                            message = message.substr(3);
                        }
                    }
                    else {
                        message = '{{Commande de modification de l\'OID créée}}'
                    }
                    jeedomUtils.showAlert({
                        message: message,
                        level: level
                    })

                    if (level === 'success')
                        window.location.reload();
                }
            }
            domUtils.ajax(paramsAJAX);
        })
});


document.querySelector('#bt_create_refresh_command').addEventListener('click', function () {

    var eqLogicId = document.querySelector('.eqLogicAttr[data-l1key="id"]').value;
    jeeDialog.prompt({
        message: 'OID ?'
    },
        function (result) {
            if (result === null)
                return
            if (result == '')
                result
            var paramsAJAX = {
                type: "POST",
                url: 'plugins/SNMP3/core/ajax/SNMP3.ajax.php',
                data: {
                    action: "create_command",
                    id: eqLogicId,
                    id_commande: result,
                    _info: '',
                    _action: '',
                    _refresh: 'X',
                },
                dataType: 'json',
                error: function (request, status, error) {
                    handleAjaxError(request, status, error)
                },
                success: function (data) {
                    if (data.state != 'ok') {
                        jeedomUtils.showAlert({
                            message: data.result,
                            level: 'danger'
                        })
                        return
                    }

                    var message = data.result;
                    var level = 'success';
                    if (message.substr(0, 2) === 'KO') {
                        level = 'warning';
                        if (message.length >= 4) {
                            message = message.substr(3);
                        }
                    }
                    else {
                        message = '{{Commande refresh del\'OID créée}}'
                    }
                    jeedomUtils.showAlert({
                        message: message,
                        level: level
                    })

                    if (level === 'success')
                        window.location.reload();
                }
            }
            domUtils.ajax(paramsAJAX);
        })
});