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




/* Permet la réorganisation des commandes dans l'équipement */
$("#table_cmd").sortable({
    axis: "y",
    cursor: "move",
    items: ".cmd",
    placeholder: "ui-state-highlight",
    tolerance: "intersect",
    forcePlaceholderSize: true
})

$("#table_cmd").delegate(".listEquipementInfo", 'click', function () {
    var el = $(this)
    jeedom.cmd.getSelectModal({ cmd: { type: 'info' } }, function (result) {
        var calcul = el.closest('tr').find('.cmdAttr[data-l1key=configuration][data-l2key=' + el.data('input') + ']')
        calcul.atCaret('insert', result.human)
    })
})

$("#table_cmd").delegate(".listEquipementAction", 'click', function () {
    var el = $(this)
    var subtype = $(this).closest('.cmd').find('.cmdAttr[data-l1key=subType]').value()
    jeedom.cmd.getSelectModal({ cmd: { type: 'action', subType: subtype } }, function (result) {
        var calcul = el.closest('tr').find('.cmdAttr[data-l1key=configuration][data-l2key=' + el.attr('data-input') + ']')
        calcul.atCaret('insert', result.human);
    })
})

$('.eqLogicAttr[data-l1key=configuration][data-l2key="version"]').on('change', function () {
    $('.snmp_protocole').hide();
    $('.snmp_' + $(this).value()).show();
});


/* Fonction permettant l'affichage des commandes dans l'équipement */
function addCmdToTable(_cmd) {

    if (!isset(_cmd)) {
        var _cmd = { configuration: {} }
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {}
    }

    let internal_type = _cmd.configuration.internal_type;
    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">'
    tr += '<td class="hidden-xs">'
    tr += '<span class="cmdAttr" data-l1key="id"></span>'
    tr += '</td>'
    tr += '<td>'
    tr += '<div class="input-group">'
    tr += '<input class="cmdAttr form-control input-sm roundedLeft" data-l1key="name" placeholder="{{Nom de la commande}}">'
    tr += '<span class="input-group-btn"><a class="cmdAction btn btn-sm btn-default" data-l1key="chooseIcon" title="{{Choisir une icône}}"><i class="fas fa-icons"></i></a></span>'
    tr += '<span class="cmdAttr input-group-addon roundedRight" data-l1key="display" data-l2key="icon" style="font-size:19px;padding:0 5px 0 0!important;"></span>'
    tr += '</div>'
    tr += '<select class="cmdAttr form-control input-sm" data-l1key="value" style="display:none;margin-top:5px;" title="{{Commande info liée}}">'
    tr += '<option value="">{{Aucune}}</option>'
    tr += '</select>'
    tr += '</td>'
    tr += '<td>';
    tr += '<input class="cmdAttr form-control input-sm " data-l1key="logicalId" placeholder="{{logicalID}}">'
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
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> Tester</a>'
    }
    tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove" title="{{Supprimer la commande}}"></i></td>'
    tr += '</tr>'
    $('#table_cmd tbody').append(tr)
    var tr = $('#table_cmd tbody tr').last()
    jeedom.eqLogic.buildSelectCmd({
        id: $('.eqLogicAttr[data-l1key=id]').value(),
        filter: { type: 'info' },
        error: function (error) {
            $('#div_alert').showAlert({ message: error.message, level: 'danger' })
        },
        success: function (result) {
            tr.find('.cmdAttr[data-l1key=value]').append(result)
            tr.setValues(_cmd, '.cmdAttr')
            jeedom.cmd.changeType(tr, init(_cmd.subType))
        }
    })
}


function printEqLogic(_eqLogic) {

    $SNMP3type = _eqLogic.configuration.type;
}


$('#bt_TestConnexionSNMP3').on('click', function () {
    $.ajax({// fonction permettant de faire de l'ajax
        type: "POST", // methode de transmission des données au fichier php
        url: "plugins/SNMP3/core/ajax/SNMP3.ajax.php", // url du fichier php
        // LA FONCTION create_command DOIT ETRE DEFINIE DANS LE FICHIER CI-DESSUS
        data: {
            action: "test_connexion",
            id: $('.eqLogicAttr[data-l1key=id]').value(),
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, $('#div_DetectBin'));
        },
        success: function (data) { // si l'appel a bien fonctionné
            if (data.state != 'ok') {
                $('#div_alert').showAlert({ message: data.result, level: 'danger' });
                return;
            }
            // window.location.reload();  // si on recharge la fenetre, on perd le message envoyé par test_connexion
        }
    });
});

$("#table_cmd").sortable({ axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true });


$('#bt_create_info_command').on('click', function () {

    bootbox.prompt('{{OID}}' + ' ?', function (result) {

        if (result !== null && result != '') {

            $.ajax({// fonction permettant de faire de l'ajax
                type: "POST", // methode de transmission des données au fichier php
                url: "plugins/SNMP3/core/ajax/SNMP3.ajax.php", // url du fichier php
                // LA FONCTION create_command DOIT ETRE DEFINIE DANS LE FICHIER CI-DESSUS
                data: {
                    action: "create_command",
                    id: $('.eqLogicAttr[data-l1key=id]').value(),
                    id_commande: result,
                    _info: 'X',
                    _action: '',
                    _refresh: '',
                },
                dataType: 'json',
                error: function (request, status, error) {
                    handleAjaxError(request, status, $('#div_DetectBin'));
                },
                success: function (data) { // si l'appel a bien fonctionné
                    if (data.state != 'ok') {
                        $('#div_alert').showAlert({ message: data.result, level: 'danger' });
                        return;
                    }
                    window.location.reload();
                }
            });
        }
    });
});

$('#bt_create_action_command').on('click', function () {

    bootbox.prompt('{{OID}}' + ' ?', function (result) {

        if (result !== null && result != '') {

            $.ajax({// fonction permettant de faire de l'ajax
                type: "POST", // methode de transmission des données au fichier php
                url: "plugins/SNMP3/core/ajax/SNMP3.ajax.php", // url du fichier php
                // LA FONCTION create_command DOIT ETRE DEFINIE DANS LE FICHIER CI-DESSUS
                data: {
                    action: "create_command",
                    id: $('.eqLogicAttr[data-l1key=id]').value(),
                    id_commande: result,
                    _info: '',
                    _action: 'X',
                    _refresh: '',
                },
                dataType: 'json',
                error: function (request, status, error) {
                    handleAjaxError(request, status, $('#div_DetectBin'));
                },
                success: function (data) { // si l'appel a bien fonctionné
                    if (data.state != 'ok') {
                        $('#div_alert').showAlert({ message: data.result, level: 'danger' });
                        return;
                    }
                    window.location.reload();
                }
            });
        }
    });
});


$('#bt_create_refresh_command').on('click', function () {

    bootbox.prompt('{{OID}}' + ' ?', function (result) {

        if (result !== null && result != '') {

            $.ajax({// fonction permettant de faire de l'ajax
                type: "POST", // methode de transmission des données au fichier php
                url: "plugins/SNMP3/core/ajax/SNMP3.ajax.php", // url du fichier php
                // LA FONCTION create_command DOIT ETRE DEFINIE DANS LE FICHIER CI-DESSUS
                data: {
                    action: "create_command",
                    id: $('.eqLogicAttr[data-l1key=id]').value(),
                    id_commande: result,
                    _info: '',
                    _action: '',
                    _refresh: 'X',
                },
                dataType: 'json',
                error: function (request, status, error) {
                    handleAjaxError(request, status, $('#div_DetectBin'));
                },
                success: function (data) { // si l'appel a bien fonctionné
                    if (data.state != 'ok') {
                        $('#div_alert').showAlert({ message: data.result, level: 'danger' });
                        return;
                    }
                    window.location.reload();
                }
            });
        }
    });
});