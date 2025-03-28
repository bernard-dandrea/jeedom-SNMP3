<?php
if (!isConnect('admin')) {
  throw new Exception('{{401 - Accès non autorisé}}');
}
// Déclaration des variables obligatoires
$plugin = plugin::byId('SNMP3');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>

<div class="row row-overflow">
  <!-- Page d'accueil du plugin -->
  <div class="col-xs-12 eqLogicThumbnailDisplay">
    <legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
    <!-- Boutons de gestion du plugin -->
    <div class="eqLogicThumbnailContainer">
      <div class="cursor eqLogicAction logoPrimary" data-action="add">
        <i class="fas fa-plus-circle"></i>
        <br>
        <span>{{Ajouter}}</span>
      </div>
      <div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
        <i class="fas fa-wrench"></i>
        <br>
        <span>{{Configuration}}</span>
      </div>
    </div>
    <legend><i class="fas fa-table"></i> {{Mes SNMP3}}</legend>
    <?php
    if (count($eqLogics) == 0) {
      echo '<br><div class="text-center" style="font-size:1.2em;font-weight:bold;">{{Aucun équipement SNMP3 trouvé, cliquer sur "Ajouter" pour commencer}}</div>';
    } else {
      // Champ de recherche
      echo '<div class="input-group" style="margin:5px;">';
      echo '<input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic">';
      echo '<div class="input-group-btn">';
      echo '<a id="bt_resetSearch" class="btn" style="width:30px"><i class="fas fa-times"></i></a>';
      echo '<a class="btn roundedRight hidden" id="bt_pluginDisplayAsTable" data-coreSupport="1" data-state="0"><i class="fas fa-grip-lines"></i></a>';
      echo '</div>';
      echo '</div>';
      // Liste des équipements du plugin
      echo '<div class="eqLogicThumbnailContainer">';
      foreach ($eqLogics as $eqLogic) {

        $opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
        echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '">';

        $file = 'plugins/SNMP3/plugin_info/' . $eqLogic->getConfiguration('icon') . '.png';
        if (file_exists(__DIR__ . '/../../../../' . $file)) {
          echo '<img src="' . $file . '" height="105" width="95">';
        } else {
          echo '<img src="' . $plugin->getPathImgIcon() . '">';
        }
        echo '<br>';
        echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
        echo '<span class="hiddenAsCard displayTableRight hidden">';
        echo ($eqLogic->getIsVisible() == 1) ? '<i class="fas fa-eye" title="{{Equipement visible}}"></i>' : '<i class="fas fa-eye-slash" title="{{Equipement non visible}}"></i>';
        echo '</span>';
        echo '</div>';
      }
      echo '</div>';
    }
    ?>
  </div> <!-- /.eqLogicThumbnailDisplay -->


  <!-- Page de présentation de l'équipement -->
  <div class="col-xs-12 eqLogic" style="display: none;">
    <!-- barre de gestion de l'équipement -->
    <div class="input-group pull-right" style="display:inline-flex;">
      <span class="input-group-btn">
        <!-- Les balises <a></a> sont volontairement fermées à la ligne suivante pour éviter les espaces entre les boutons. Ne pas modifier -->
        <a class="btn btn-sm btn-default eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i><span class="hidden-xs"> {{Configuration avancée}}</span>
        </a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i>
          {{Sauvegarder}}
        </a><a class="btn btn-sm btn-danger eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}
        </a>
      </span>
    </div>
    <!-- Onglets -->
    <ul class="nav nav-tabs" role="tablist">
      <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
      <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
      <li role="presentation"><a href="#commandtab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-list"></i> {{Commandes}}</a></li>
    </ul>
    <div class="tab-content">
      <!-- Onglet de configuration de l'équipement -->
      <div role="tabpanel" class="tab-pane active" id="eqlogictab">
        <!-- Partie gauche de l'onglet "Equipements" -->
        <!-- Paramètres généraux et spécifiques de l'équipement -->
        <form class="form-horizontal">
          <fieldset>

            <div class="col-lg-8">
              <legend><i class="fas fa-wrench"></i> {{Paramètres généraux}}</legend>
              <div class="form-group">
                <label class="col-sm-4 control-label">{{Nom du SNMP3}}</label>
                <div class="col-sm-6">
                  <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display:none;">
                  <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'SNMP3}}">
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-4 control-label">{{Objet parent}}</label>
                <div class="col-sm-6">
                  <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                    <option value="">{{Aucun}}</option>
                    <?php
                    $options = '';
                    foreach ((jeeObject::buildTree(null, false)) as $object) {
                      $options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
                    }
                    echo $options;
                    ?>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-4 control-label">{{Catégorie}}</label>
                <div class="col-sm-6">
                  <?php
                  foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                    echo '<label class="checkbox-inline">';
                    echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" >' . $value['name'];
                    echo '</label>';
                  }
                  ?>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-4 control-label">{{Options}}</label>
                <div class="col-sm-6">
                  <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked>{{Activer}}</label>
                  <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked>{{Visible}}</label>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-4 control-label"></label>
                <div class="col-sm-4">
                  <a class="btn btn-default " id="bt_TestConnexionSNMP3" '><i class="fa fa-cogs"> {{Tester la
                      connexion au SNMP3}}</i></a>
                </div>
              </div>
              <div class=" form-group">
                <label class="col-sm-4 control-label">{{Version}}</label>
                <div class="col-sm-6">
                  <select id="sel_icon" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="version">
                    <option value="0">{{v1}}</option>
                    <option value="1">{{v2c}}</option>
                    <option value="3">{{v3}}</option>
                  </select>
                </div>
              </div>
              <div class="form-group ">
                <label class="col-sm-4 control-label">{{localhost}}</label>
                <div class="col-sm-6">
                  <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="localhost"">
                </div>
              </div>
              
							<div class=" snmp_protocole snmp_0 snmp_1">
                  <div class="form-group ">
                    <label class="col-sm-4 control-label">{{community}}</label>
                    <div class="col-sm-6">
                      <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="community"">
                    </div>
                    <label class="col-sm-4 control-label">{{community RW}}</label>
                    <div class="col-sm-6">
                      <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="community_rw"">
                    </div>
                  </div>  
              </div>  
              
							<div class=" snmp_protocole snmp_3">
                      <div class=" form-group ">
                        <label class=" col-sm-4 control-label">{{security_name}}</label>
                        <div class="col-sm-6">
                          <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="security_name" />
                        </div>
                      </div>
                      <div class=" form-group">
                        <label class="col-sm-4 control-label">{{security_level}}</label>
                        <div class="col-sm-6">
                          <select id="sel_icon" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="security_level">
                            <option value="noAuthNoPriv">{{noAuthNoPriv}}</option>
                            <option value="authNoPriv">{{authNoPriv}}</option>
                            <option value="authPriv">{{authPriv}}</option>
                          </select>
                        </div>
                      </div>
                      <div class=" form-group">
                        <label class="col-sm-4 control-label">{{auth_protocol}}</label>
                        <div class="col-sm-6">
                          <select id="sel_icon" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="auth_protocol">
                            <option value="MD5">{{MD5}}</option>
                            <option value="SHA">{{SHA}}</option>
                          </select>
                        </div>
                      </div>
                      <div class="form-group ">
                        <label class="col-sm-4 control-label">{{auth_passphrase}}</label>
                        <div class="col-sm-6">
                          <input type="password" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="auth_passphrase" />
                        </div>
                      </div>
                      <div class=" form-group">
                        <label class="col-sm-4 control-label">{{privacy_protocol}}</label>
                        <div class="col-sm-6">
                          <select id="sel_icon" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="privacy_protocol">
                            <option value="DES">{{DES}}</option>
                            <option value="AES">{{AES}}</option>
                          </select>
                        </div>
                      </div>
                      <div class="form-group ">
                        <label class="col-sm-4 control-label">{{privacy_passphrase}}</label>
                        <div class="col-sm-6">
                          <input type="password" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="privacy_passphrase" />
                        </div>
                      </div>
                      <div class="form-group ">
                        <label class="col-sm-4 control-label">{{context_name}}</label>
                        <div class="col-sm-6">
                          <input type="password" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="context_name" />
                        </div>
                      </div>                      

                    </div>
                    <div class="form-group ">
                      <label class="col-sm-4 control-label">{{timeout (en millisec)}}</label>
                      <div class="col-sm-6">
                        <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="timeout"">
                </div>
              </div>   
              
            

              <div class=" form-group ">
                  <label class=" col-sm-4 control-label">{{retries}}</label>
                        <div class="col-sm-6">
                          <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="retries"">
                </div>
              </div>                
              <div class=" form-group">
                          <label class="col-sm-4 control-label">{{Icône}}</label>
                          <div class="col-sm-6">
                            <select id="sel_icon" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="icon">
                              <option value="none">{{Aucun}}</option>
                              <option value="Perso1">{{Perso1}}</option>
                              <option value="Perso2">{{Perso2}}</option>
                              <option value="Perso3">{{Perso3}}</option>
                              <option value="Perso4">{{Perso4}}</option>
                              <option value="Perso5">{{Perso5}}</option>
                              <option value="Perso6">{{Perso6}}</option>
                              <option value="Perso7">{{Perso7}}</option>
                              <option value="Perso8">{{Perso8}}</option>
                              <option value="Perso9">{{Perso9}}</option>
                            </select>
                          </div>
                        </div>




                      </div>
          </fieldset>
        </form>
      </div>

      <!-- /.tabpanel #eqlogictab-->
      <!-- Onglet des commandes de l equipement-->
      <div role="tabpanel" class="tab-pane" id="commandtab">
        <div class="input-group pull-right" style="display:inline-flex;margin-top:5px;">
          <span class="input-group-btn">
            <a class="btn btn-info btn-xs roundedLeft " id="bt_create_info_command" title=' {{Importer un paramètre}}'><i class="fas fa-plus-circle"> {{Importer un
                OID}}</i></a>
                  <a class="btn btn-info btn-xs roundedLeft " id="bt_create_refresh_command"><i class="fas fa-plus-circle"></i> {{Ajouter une commande refresh}}
                    <a class="btn btn-info btn-xs roundedLeft " id="bt_create_action_command"><i class="fas fa-plus-circle"></i> {{Ajouter une commande action}}
                    </a>
                    </span>
                </div>
                <br><br>
                <div class="table-responsive">
                  <table id="table_cmd" class="table table-bordered table-condensed">
                    <thead>
                      <tr>
                        <th class="hidden-xs" style="min-width:50px;width:70px;">ID</th>
                        <th style="min-width:200px;width:350px;">{{Nom}}</th>
                        <th>{{logicalID}}</th>
                        <th>{{Type}}</th>
                        <th style="min-width:260px;">{{Options}}</th>
                        <th>{{Scan}}</th>
                        <th>{{Valeur}}
                        </th>
                        <th style="min-width:80px;width:200px;">{{Actions}}</th>
                      </tr>
                    </thead>
                    <tbody>
                    </tbody>
                  </table>
                </div>
              </div><!-- /.tabpanel #commandtab-->

            </div><!-- /.tab-content -->
      </div><!-- /.eqLogic -->
    </div><!-- /.row row-overflow -->

    <!-- Inclusion du fichier javascript du plugin (dossier, nom_du_fichier, extension_du_fichier, id_du_plugin) -->
    <?php
    include_file('desktop', 'SNMP3', 'js', 'SNMP3');
    include_file('core', 'plugin.template', 'js');
    ?>
