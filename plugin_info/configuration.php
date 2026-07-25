<?php


// Last Modified : 2026/07/22 08:26:03


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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
  include_file('desktop', '404', 'php');
  die();
}
?>



<form class="form-horizontal">
  <fieldset>
        <div class=" form-group">
      <label class="col-sm-3 control-label">{{Utiliser un cron autonome (via le moteur des tâches)}}</label>
      <div class="col-sm-3">
        <select style="width: 150px;" id="sel_CronSpecifique" class="configKey form-control" data-l1key="CronSpecifique">
          <option value="">{{Non}}</option>
          <option value="1">{{Oui}}</option>
        </select>
      </div>
    </div>
  </fieldset>
</form>
<script>
  document.getElementById('bt_savePluginConfig').addEventListener('click', function(event) {
    event.preventDefault();

    const selectElement = document.getElementById('sel_CronSpecifique');
    const selectedValue = selectElement.value;
    var paramsAJAX = {
      url: 'plugins/SNMP3/core/ajax/SNMP3.ajax.php',
      data: {
        action: 'enable_cron',
        enable: selectedValue
      },
      dataType: 'json',
      success: function(data) {},
      error: function(request, status, error) {
        handleAjaxError(request, status, error);
      }
    }
    domUtils.ajax(paramsAJAX);

  });
</script>