{**
 * SpamCustomerCleaner - PrestaShop Module
 *
 * @author    Genisoft web
 * @copyright 2026
 * @license   Commercial / Proprietary
 *}

<link rel="stylesheet" href="{$module_dir}views/css/admin.css?v={$module_version}">

<div class="scc-wrapper">
    <!-- Header Banner -->
    <div class="panel scc-header-panel">
        <div class="scc-header-content">
            <div class="scc-brand">
                <img src="{$module_dir}logo.png" alt="Logo" class="scc-logo-img">
                <div class="scc-title-area">
                    <div class="scc-title-line">
                        <h2>{l s='Scanner & Nettoyeur Anti-Spam Clients' mod='spamcustomercleaner'}</h2>
                        {if $is_pro}
                            <span class="badge scc-badge-pro"><i class="icon-star"></i> {l s='VERSION PRO ILLIMITÉE' mod='spamcustomercleaner'}</span>
                        {else}
                            <span class="badge scc-badge-free"><i class="icon-unlock-alt"></i> {l s='VERSION GRATUITE' mod='spamcustomercleaner'} ({$remaining_free}/{$free_limit} {l s='restants' mod='spamcustomercleaner'})</span>
                        {/if}
                    </div>
                    <p class="text-muted">
                        {l s='Développé par' mod='spamcustomercleaner'} <strong>Genisoft web</strong> &bull; {l s='Écosystème' mod='spamcustomercleaner'} <a href="{$fexa_url|escape:'html':'UTF-8'}" target="_blank" style="color: #10b981; font-weight: bold; text-decoration: none;">Fexa AI</a> &bull; {l s='Nettoyage sécurisé des faux comptes, adresses et messages avec protection absolue de vos vrais clients.' mod='spamcustomercleaner'}
                    </p>
                </div>
            </div>
            <div class="scc-header-actions">
                <button type="button" class="btn btn-primary btn-lg" id="btn-start-scan" onclick="if(typeof runScan === 'function'){ runScan(); }" style="font-weight: 600;">
                    <span style="font-size: 16px; margin-right: 5px;">🔍</span> {l s='Lancer le Scan Complet' mod='spamcustomercleaner'}
                </button>
                <a href="{$config_action_url|escape:'html':'UTF-8'}&exportSpamCsv=1" class="btn btn-default btn-lg" id="btn-export-csv">
                    <span style="font-size: 16px; margin-right: 5px;">💾</span> {l s='Sauvegarde CSV' mod='spamcustomercleaner'}
                </a>
            </div>
        </div>
    </div>

    <!-- Genisoft PRO Upgrade Banner -->
    <div class="panel scc-upgrade-box {if $is_pro}scc-upgrade-box-active{/if}">
        <div class="row">
            <div class="col-md-7">
                {if $is_pro}
                    <h3 class="text-success" style="margin-top: 0;"><i class="icon-check-circle"></i> {l s='Votre licence PRO est active !' mod='spamcustomercleaner'}</h3>
                    <p>{l s='Vous bénéficiez de toutes les fonctionnalités illimitées : suppression massive sans restriction, pilote automatique CRON et nettoyage des spams de contact.' mod='spamcustomercleaner'}</p>
                {else}
                    <div class="scc-upgrade-header">
                        <span class="scc-star-icon">⭐</span>
                        <div>
                            <h3 style="margin: 0 0 5px 0; color: #fff;">{l s='Débloquez la Version PRO Illimitée' mod='spamcustomercleaner'}</h3>
                            <p style="margin: 0; color: #e0e7ff;">
                                {l s='La version gratuite est limitée à 50 suppressions de test. Passez à la vitesse supérieure pour votre boutique !' mod='spamcustomercleaner'}
                            </p>
                        </div>
                    </div>
                    <ul class="scc-pro-benefits">
                        <li><i class="icon-check"></i> {l s='Suppression en masse' mod='spamcustomercleaner'} <strong>{l s='100% ILLIMITÉE' mod='spamcustomercleaner'}</strong> {l s='(nettoyez vos 12 700+ spams en un clic)' mod='spamcustomercleaner'}</li>
                        <li><i class="icon-check"></i> <strong>{l s='Pilote Automatique (CRON)' mod='spamcustomercleaner'}</strong> : {l s='nettoyage automatique récurrent en tâche de fond' mod='spamcustomercleaner'}</li>
                        <li><i class="icon-check"></i> <strong>{l s='Nettoyeur de Messages Contact (SAV)' mod='spamcustomercleaner'}</strong> : {l s='élimine les spams publicitaires du formulaire' mod='spamcustomercleaner'}</li>
                        <li><i class="icon-check"></i> {l s='Support technique prioritaire & mises à jour' mod='spamcustomercleaner'} <strong>Genisoft web</strong></li>
                    </ul>
                {/if}
            </div>
            <div class="col-md-5 text-right scc-upgrade-cta-col">
                {if !$is_pro}
                    <a href="{$shop_url|escape:'html':'UTF-8'}" target="_blank" class="btn btn-warning btn-lg scc-shop-btn">
                        <i class="icon-shopping-cart"></i> {l s='Acheter la licence PRO sur la Boutique Genisoft' mod='spamcustomercleaner'} ➔
                    </a>
                {/if}
                <div class="scc-license-form" style="margin-top: 15px;">
                    <div class="input-group">
                        <input type="text" id="scc-license-input" class="form-control" placeholder="{l s='Entrez votre clé de licence Genisoft' mod='spamcustomercleaner'}" value="{$license_key|escape:'html':'UTF-8'}">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-success" id="btn-activate-license">
                                <i class="icon-key"></i> {l s='Activer' mod='spamcustomercleaner'}
                            </button>
                        </span>
                    </div>
                    <small class="help-block text-left" style="color: {if $is_pro}#d1fae5{else}#c7d2fe{/if}; margin-top: 4px;">
                        {if $is_pro}{l s='Clé actuellement enregistrée' mod='spamcustomercleaner'}{else}{l s='Clé fournie instantanément après commande sur la Boutique Genisoft' mod='spamcustomercleaner'}{/if}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="row scc-kpi-row">
        <div class="col-md-3 col-sm-6">
            <div class="panel scc-kpi-card scc-kpi-total">
                <div class="scc-kpi-icon"><i class="icon-users"></i></div>
                <div class="scc-kpi-info">
                    <span class="scc-kpi-label">{l s='Total Clients BDD' mod='spamcustomercleaner'}</span>
                    <span class="scc-kpi-value" id="kpi-total">{$stats.total_customers|intval}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="panel scc-kpi-card scc-kpi-spam">
                <div class="scc-kpi-icon"><i class="icon-bug"></i></div>
                <div class="scc-kpi-info">
                    <span class="scc-kpi-label">{l s='Spams Détectés' mod='spamcustomercleaner'}</span>
                    <span class="scc-kpi-value" id="kpi-spam">--</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="panel scc-kpi-card scc-kpi-clean">
                <div class="scc-kpi-icon"><i class="icon-check-circle"></i></div>
                <div class="scc-kpi-info">
                    <span class="scc-kpi-label">{l s='Clients Sains Protégés' mod='spamcustomercleaner'}</span>
                    <span class="scc-kpi-value" id="kpi-clean">--</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="panel scc-kpi-card scc-kpi-addr">
                <div class="scc-kpi-icon"><i class="icon-envelope"></i></div>
                <div class="scc-kpi-info">
                    <span class="scc-kpi-label">{l s='Spams Contact Form' mod='spamcustomercleaner'}</span>
                    <span class="scc-kpi-value" id="kpi-contact">{$stats.contact_spam_count|intval}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs scc-nav-tabs">
        <li class="active"><a href="#tab-customers" data-toggle="tab"><i class="icon-users"></i> {l s='Comptes Clients Spam' mod='spamcustomercleaner'}</a></li>
        <li><a href="#tab-contact" data-toggle="tab"><i class="icon-envelope"></i> {l s='Messages Contact Spam' mod='spamcustomercleaner'} {if !$is_pro}<span class="badge scc-tab-pro-tag">PRO</span>{/if}</a></li>
        <li><a href="#tab-cron" data-toggle="tab"><i class="icon-time"></i> {l s='Pilote Automatique (CRON)' mod='spamcustomercleaner'} {if !$is_pro}<span class="badge scc-tab-pro-tag">PRO</span>{/if}</a></li>
        <li><a href="#tab-settings" data-toggle="tab"><i class="icon-cogs"></i> {l s='Paramètres & Sécurité' mod='spamcustomercleaner'}</a></li>
        <li><a href="#tab-fexa" data-toggle="tab" style="color: #059669; font-weight: 700;"><span style="color: #10b981; margin-right: 3px;">⚡</span> {l s='Fexa AI (SEO & Citations IA)' mod='spamcustomercleaner'} <span class="badge" style="background:#10b981; color:#fff; font-size:10px;">{l s='DÉCOUVRIR' mod='spamcustomercleaner'}</span></a></li>
    </ul>

    <div class="tab-content" style="padding-top: 15px;">
        <!-- TAB 1: CUSTOMERS SCAN & DELETE -->
        <div class="tab-pane active" id="tab-customers">
            <div class="panel" id="scan-results-panel">
                <div class="panel-heading">
                    <i class="icon-list"></i> {l s='Résultats du Scan Anti-Spam Clients' mod='spamcustomercleaner'}
                    <span class="badge" id="scan-badge-count">0</span>
                </div>

                <!-- Initial Placeholder -->
                <div id="scan-placeholder" class="text-center" style="padding: 40px 20px;">
                    <i class="icon-shield icon-4x text-muted" style="font-size: 64px; color: #ccc;"></i>
                    <h3 style="margin-top: 15px;">{l s='Prêt pour le diagnostic' mod='spamcustomercleaner'}</h3>
                    <p class="text-muted">{l s='Cliquez sur "Lancer le Scan Complet" pour analyser les 13 000+ comptes clients de votre boutique.' mod='spamcustomercleaner'}</p>
                    <button type="button" class="btn btn-primary btn-lg" onclick="runScan();">
                        <i class="icon-search"></i> {l s='Lancer le Scan' mod='spamcustomercleaner'}
                    </button>
                </div>

                <!-- Loading Spinner -->
                <div id="scan-loading" class="text-center" style="display:none; padding: 50px 20px;">
                    <i class="icon-spinner icon-spin icon-3x text-primary" style="font-size: 48px;"></i>
                    <h4 style="margin-top: 20px;">{l s='Scan heuristique des comptes clients en cours...' mod='spamcustomercleaner'}</h4>
                    <p class="text-muted">{l s='Analyse des noms, prénoms, emails, domaines, adresses et commandes...' mod='spamcustomercleaner'}</p>
                </div>

                <!-- Table View -->
                <div id="scan-table-wrapper" style="display:none;">
                    <!-- Actions & Filters Bar -->
                    <div class="scc-toolbar">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="icon-search"></i></span>
                                    <input type="text" id="scc-filter-input" class="form-control" placeholder="{l s='Filtrer par nom, email, ID ou motif...' mod='spamcustomercleaner'}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select id="scc-filter-addr" class="form-control">
                                    <option value="all">{l s='Tous les comptes spam détectés' mod='spamcustomercleaner'}</option>
                                    <option value="with_addr">{l s='Spams avec adresse uniquement' mod='spamcustomercleaner'}</option>
                                    <option value="no_addr">{l s='Spams sans adresse' mod='spamcustomercleaner'}</option>
                                </select>
                            </div>
                            <div class="col-md-4 text-right">
                                <button type="button" class="btn btn-danger btn-lg" id="btn-delete-selected">
                                    <i class="icon-trash"></i> {l s='Supprimer les Spams Détectés' mod='spamcustomercleaner'}
                                    (<span id="selected-count">0</span>)
                                </button>
                            </div>
                        </div>
                        <div class="scc-selection-bar" style="margin-top: 10px;">
                            <div class="checkbox inline-checkbox">
                                <label>
                                    <input type="checkbox" id="select-all-checkbox" checked>
                                    <strong>{l s='Tout sélectionner' mod='spamcustomercleaner'}</strong>
                                </label>
                            </div>
                            <span class="text-muted" style="margin-left: 15px;">
                                <i class="icon-info-circle"></i>
                                <span id="scan-summary-text">0 spams sélectionnés</span>
                            </span>
                            <span class="pull-right">
                                <label class="checkbox-inline">
                                    <input type="checkbox" id="opt-delete-addresses" checked>
                                    <strong>{l s='Supprimer également les adresses associées' mod='spamcustomercleaner'}</strong>
                                </label>
                            </span>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="scc-customers-table">
                            <thead>
                                <tr>
                                    <th width="40"><input type="checkbox" id="header-select-all" checked></th>
                                    <th width="70">ID</th>
                                    <th>{l s='Nom & Prénom' mod='spamcustomercleaner'}</th>
                                    <th>{l s='Email' mod='spamcustomercleaner'}</th>
                                    <th>{l s='Date d\'inscription' mod='spamcustomercleaner'}</th>
                                    <th width="120" class="text-center">{l s='Adresse(s)' mod='spamcustomercleaner'}</th>
                                    <th>{l s='Motifs de Détection' mod='spamcustomercleaner'}</th>
                                    <th width="60" class="text-right">{l s='Action' mod='spamcustomercleaner'}</th>
                                </tr>
                            </thead>
                            <tbody id="scc-table-body">
                                <!-- Populated by JS -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Bar -->
                    <div class="scc-pagination-bar clearfix">
                        <div class="pull-left">
                            <span id="pagination-info" class="text-muted"></span>
                        </div>
                        <div class="pull-right">
                            <ul class="pagination" id="scc-pagination" style="margin: 0;">
                                <!-- Populated by JS -->
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: CONTACT FORM SPAM (KILLER PRO FEATURE) -->
        <div class="tab-pane" id="tab-contact">
            <div class="panel">
                <div class="panel-heading">
                    <i class="icon-envelope"></i> {l s='Nettoyeur de Messages Contact (SAV)' mod='spamcustomercleaner'}
                </div>
                <div class="panel-body">
                    {if !$is_pro}
                        <div class="alert alert-warning">
                            <h4><i class="icon-lock"></i> {l s='Fonctionnalité réservée à la Version PRO' mod='spamcustomercleaner'}</h4>
                            <p>{l s='Les bots ne spamment pas uniquement les inscriptions : ils inondent aussi votre formulaire de contact avec des liens publicitaires, du SEO spam et du phishing.' mod='spamcustomercleaner'}</p>
                            <p>{l s='La version PRO analyse vos fils de messages (SAV) et supprime ces pollutions d\'un simple clic.' mod='spamcustomercleaner'}</p>
                            <a href="{$shop_url|escape:'html':'UTF-8'}" target="_blank" class="btn btn-warning" style="margin-top: 10px;">
                                <i class="icon-star"></i> {l s='Débloquer cette fonctionnalité sur la Boutique Genisoft' mod='spamcustomercleaner'}
                            </a>
                        </div>
                    {/if}

                    <div class="scc-toolbar">
                        <div class="row">
                            <div class="col-md-8">
                                <p style="margin: 5px 0;">
                                    <strong>{$stats.total_contact_threads|intval}</strong> {l s='fils de messages au total' mod='spamcustomercleaner'} &bull;
                                    <strong class="text-danger">{$stats.contact_spam_count|intval}</strong> {l s='spams publicitaires identifiés dans votre formulaire de contact' mod='spamcustomercleaner'}
                                </p>
                            </div>
                            <div class="col-md-4 text-right">
                                <button type="button" class="btn btn-primary" id="btn-scan-contact">
                                    <i class="icon-search"></i> {l s='Analyser les Messages' mod='spamcustomercleaner'}
                                </button>
                                <button type="button" class="btn btn-danger" id="btn-delete-contact-spam" {if !$is_pro}disabled title="{l s='Disponible en version PRO' mod='spamcustomercleaner'}"{/if}>
                                    <i class="icon-trash"></i> {l s='Nettoyer les Spams Contact' mod='spamcustomercleaner'}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="contact-spam-table-wrapper" style="display:none; margin-top: 15px;">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="40"><input type="checkbox" id="contact-select-all" checked></th>
                                    <th width="80">ID Fil</th>
                                    <th width="200">Email Expéditeur</th>
                                    <th width="150">Date</th>
                                    <th>Extrait du Message Spam</th>
                                </tr>
                            </thead>
                            <tbody id="contact-spam-tbody">
                                <!-- Populated by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 3: AUTOMATED CRON (KILLER PRO FEATURE) -->
        <div class="tab-pane" id="tab-cron">
            <div class="panel">
                <div class="panel-heading">
                    <i class="icon-time"></i> {l s='Pilote Automatique (Tâche CRON Récurrente)' mod='spamcustomercleaner'}
                </div>
                <div class="panel-body">
                    {if !$is_pro}
                        <div class="alert alert-warning">
                            <h4><i class="icon-lock"></i> {l s='Fonctionnalité réservée à la Version PRO' mod='spamcustomercleaner'}</h4>
                            <p>{l s='Sans tâche CRON, vous devez vous reconnecter régulièrement pour relancer le scan manuel. Le Pilote Automatique nettoie les nouveaux spams chaque nuit en silence.' mod='spamcustomercleaner'}</p>
                            <a href="{$shop_url|escape:'html':'UTF-8'}" target="_blank" class="btn btn-warning" style="margin-top: 10px;">
                                <i class="icon-star"></i> {l s='Débloquer le Pilote Automatique sur la Boutique Genisoft' mod='spamcustomercleaner'}
                            </a>
                        </div>
                    {/if}

                    <h4>{l s='Configuration de l\'exécution automatique' mod='spamcustomercleaner'}</h4>
                    <p class="text-muted">
                        {l s='Copiez cette URL sécurisée dans votre gestionnaire de tâches planifiées (cPanel Cron Jobs, Plesk ou un service en ligne comme cron-job.org) pour un nettoyage quotidien sans aucune intervention.' mod='spamcustomercleaner'}
                    </p>

                    <div class="well">
                        <label>{l s='URL de déclenchement sécurisée (avec jeton secret) :' mod='spamcustomercleaner'}</label>
                        <div class="input-group">
                            <input type="text" class="form-control" readonly value="{$cron_url|escape:'html':'UTF-8'}" id="scc-cron-url-input">
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default" onclick="navigator.clipboard.writeText($('#scc-cron-url-input').val()); alert('URL copiée dans le presse-papiers !');">
                                    <i class="icon-copy"></i> {l s='Copier' mod='spamcustomercleaner'}
                                </button>
                            </span>
                        </div>
                        <small class="help-block" style="margin-top: 8px;">
                            {l s='Exemple de commande cPanel (chaque nuit à 3h00) :' mod='spamcustomercleaner'}<br>
                            <code>0 3 * * * curl -s "{$cron_url|escape:'html':'UTF-8'}" > /dev/null 2>&1</code>
                        </small>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">{l s='Dernière exécution automatique' mod='spamcustomercleaner'}</div>
                        <div class="panel-body">
                            {if $stats.last_cron_date}
                                <p><strong>{l s='Date :' mod='spamcustomercleaner'}</strong> {$stats.last_cron_date|escape:'html':'UTF-8'}</p>
                                <p><strong>{l s='Bilan :' mod='spamcustomercleaner'}</strong> <span class="text-success">{$stats.last_cron_stats|escape:'html':'UTF-8'}</span></p>
                            {else}
                                <p class="text-muted"><i class="icon-info-circle"></i> {l s='Aucune exécution CRON enregistrée pour le moment.' mod='spamcustomercleaner'}</p>
                            {/if}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 4: SETTINGS & WHITELIST -->
        <div class="tab-pane" id="tab-settings">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="icon-cogs"></i> {l s='Paramètres de Détection, Liste Blanche & Monétisation' mod='spamcustomercleaner'}
                </div>
                <div class="panel-body">
                    <form action="{$config_action_url|escape:'html':'UTF-8'}" method="post" class="form-horizontal">
                        <div class="alert alert-info">
                            <i class="icon-info-circle"></i>
                            <strong>{l s='Garantie Sécurité :' mod='spamcustomercleaner'}</strong>
                            {l s='Les clients ayant passé une commande ne seront JAMAIS supprimés ni signalés, même s\'ils correspondent à une règle.' mod='spamcustomercleaner'}
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h4>{l s='Critères Heuristiques Anti-Bot' mod='spamcustomercleaner'}</h4>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="SCC_CHECK_URLS" value="1" {if $cfg_check_urls}checked{/if}>
                                        <strong>{l s='Détecter les URLs et liens dans les noms' mod='spamcustomercleaner'}</strong>
                                        <span class="help-block">{l s='Ex: Hot Helena Waiting www.xurl.es/...' mod='spamcustomercleaner'}</span>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="SCC_CHECK_KEYWORDS" value="1" {if $cfg_check_keywords}checked{/if}>
                                        <strong>{l s='Détecter les mots-clés de spam' mod='spamcustomercleaner'}</strong>
                                        <span class="help-block">{l s='Ex: dating, wants to meet, sexy, etc.' mod='spamcustomercleaner'}</span>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="SCC_CHECK_MIXED_CASE" value="1" {if $cfg_check_mixed_case}checked{/if}>
                                        <strong>{l s='Détecter les casses mixtes aléatoires (CamelCase bot)' mod='spamcustomercleaner'}</strong>
                                        <span class="help-block">{l s='Ex: GJfwUoNDU, oDglEQIqBVVMI' mod='spamcustomercleaner'}</span>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="SCC_CHECK_CONSONANTS" value="1" {if $cfg_check_consonants}checked{/if}>
                                        <strong>{l s='Détecter 5+ consonnes consécutives' mod='spamcustomercleaner'}</strong>
                                        <span class="help-block">{l s='Chaînes inintelligibles générées par script' mod='spamcustomercleaner'}</span>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="SCC_CHECK_VOWELS" value="1" {if $cfg_check_vowels}checked{/if}>
                                        <strong>{l s='Détecter l\'absence anormale de voyelles' mod='spamcustomercleaner'}</strong>
                                        <span class="help-block">{l s='Ratio de voyelles inférieur à 17% sur les mots de 5+ lettres' mod='spamcustomercleaner'}</span>
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h4>{l s='Critères E-mails & Domaines' mod='spamcustomercleaner'}</h4>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="SCC_CHECK_CONSONANT_START" value="1" {if $cfg_check_consonant_start}checked{/if}>
                                        <strong>{l s='Début de nom avec consonnes atypiques' mod='spamcustomercleaner'}</strong>
                                        <span class="help-block">{l s='Ex: Brkjj, Vkues, Qcpmu' mod='spamcustomercleaner'}</span>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="SCC_CHECK_TLDS" value="1" {if $cfg_check_tlds}checked{/if}>
                                        <strong>{l s='Détecter les extensions suspectes (.ru, .xyz, .top...)' mod='spamcustomercleaner'}</strong>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="SCC_CHECK_DISPOSABLE" value="1" {if $cfg_check_disposable}checked{/if}>
                                        <strong>{l s='Détecter les domaines d\'emails jetables & de test' mod='spamcustomercleaner'}</strong>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="SCC_CHECK_RANDOM_EMAIL" value="1" {if $cfg_check_random_email}checked{/if}>
                                        <strong>{l s='Détecter les formats d\'emails générés par bot' mod='spamcustomercleaner'}</strong>
                                        <span class="help-block">{l s='Ex: ffuexvizct85@hotmail.com' mod='spamcustomercleaner'}</span>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="SCC_HONEYPOT_ACTIVE" value="1" {if $cfg_honeypot}checked{/if}>
                                        <strong>{l s='Bloquer en direct les futures inscriptions de bots (Honeypot)' mod='spamcustomercleaner'}</strong>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label col-lg-4">{l s='Liste Blanche' mod='spamcustomercleaner'}</label>
                                    <div class="col-lg-8">
                                        <textarea name="SCC_WHITELIST" rows="3" class="form-control">{$cfg_whitelist|escape:'html':'UTF-8'}</textarea>
                                        <span class="help-block">{l s='Un email, nom ou domaine par ligne (toujours protégés)' mod='spamcustomercleaner'}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label col-lg-4">{l s='Lien Boutique Genisoft' mod='spamcustomercleaner'}</label>
                                    <div class="col-lg-8">
                                        <input type="text" name="SCC_SHOP_URL" class="form-control" value="{$cfg_shop_url|escape:'html':'UTF-8'}">
                                        <span class="help-block">{l s='Votre URL de boutique pour la commande des licences PRO' mod='spamcustomercleaner'}</span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-4">{l s='Lien Plateforme Fexa AI' mod='spamcustomercleaner'}</label>
                                    <div class="col-lg-8">
                                        <input type="text" name="SCC_FEXA_URL" class="form-control" value="{$fexa_url|escape:'html':'UTF-8'}">
                                        <span class="help-block">{l s='Lien vers votre plateforme SaaS Fexa AI (SEO & Citations IA)' mod='spamcustomercleaner'}</span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-4">{l s='Taille des lots (Batch)' mod='spamcustomercleaner'}</label>
                                    <div class="col-lg-8">
                                        <input type="number" name="SCC_BATCH_SIZE" class="form-control" value="{$cfg_batch_size|intval}" min="50" max="1000">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="panel-footer">
                            <button type="submit" name="submitSccConfig" class="btn btn-default pull-right">
                                <i class="process-icon-save"></i> {l s='Enregistrer les Paramètres' mod='spamcustomercleaner'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- TAB 5: FEXA AI MARKETING & ECOSYSTEM -->
        <div class="tab-pane" id="tab-fexa">
            <div class="panel scc-fexa-panel" style="border: 1px solid #10b981; border-radius: 8px; overflow: hidden; background: #0b132b; color: #fff; padding: 0;">
                <div class="scc-fexa-header" style="background: linear-gradient(135deg, #064e3b 0%, #022c22 100%); padding: 35px 30px; border-bottom: 1px solid #047857;">
                    <div class="row">
                        <div class="col-md-8">
                            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                                <div style="width: 50px; height: 50px; background: #10b981; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 26px; font-weight: 900; color: #fff; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);">F</div>
                                <div>
                                    <h2 style="margin: 0; color: #fff; font-size: 28px; font-weight: 800; letter-spacing: -0.5px;">Fexa <span style="color: #34d399;">AI</span></h2>
                                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 2px; color: #6ee7b7; font-weight: 700;">{l s='Copilote SEO Autonome & Moteur de Citations IA pour PrestaShop' mod='spamcustomercleaner'}</span>
                                </div>
                            </div>
                            <h3 style="color: #f0fdf4; font-size: 22px; font-weight: 700; margin-top: 15px; line-height: 1.3;">
                                {l s='Dominez Google. Faites recommander vos produits par ChatGPT & Perplexity.' mod='spamcustomercleaner'}
                            </h3>
                            <p style="color: #a7f3d0; font-size: 14px; margin-top: 10px; max-width: 650px; line-height: 1.5;">
                                {l s='Après avoir nettoyé vos spams, développez vos ventes réelles ! Fexa AI connecte directement votre Google Search Console pour propulser votre catalogue en première page et dans les moteurs IA.' mod='spamcustomercleaner'}
                            </p>
                        </div>
                        <div class="col-md-4 text-right" style="padding-top: 20px;">
                            <a href="{$fexa_url|escape:'html':'UTF-8'}" target="_blank" class="btn btn-lg" style="background: linear-gradient(135deg, #10b981, #059669); color: #fff; font-weight: 800; font-size: 15px; padding: 14px 24px; border-radius: 8px; border: none; box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4); text-transform: uppercase; letter-spacing: 0.5px; display: inline-block;">
                                <i class="icon-rocket" style="margin-right: 5px;"></i> {l s='Lancer un Audit SEO Gratuit' mod='spamcustomercleaner'} ➔
                            </a>
                            <div style="margin-top: 10px; color: #6ee7b7; font-size: 12px;">
                                <i class="icon-check"></i> {l s='Module PrestaShop 100% gratuit & sans carte bancaire' mod='spamcustomercleaner'}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="scc-fexa-body" style="padding: 30px; background: #0f172a;">
                    <div class="row">
                        <div class="col-md-4">
                            <div style="background: #1e293b; border: 1px solid #334155; border-radius: 10px; padding: 20px; height: 100%;">
                                <div style="font-size: 28px; margin-bottom: 10px;">🎯</div>
                                <h4 style="color: #38bdf8; font-weight: 700; margin-top: 0;">{l s='Striking Distance (Positions Dormantes)' mod='spamcustomercleaner'}</h4>
                                <p style="color: #94a3b8; font-size: 13px; line-height: 1.5;">
                                    {l s='Débloquez immédiatement le chiffre d\'affaires endormi de votre boutique en identifiant les mots-clés positionnés en bas de page 1 ou en page 2 prêts à grimper dans le TOP 3.' mod='spamcustomercleaner'}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div style="background: #1e293b; border: 1px solid #334155; border-radius: 10px; padding: 20px; height: 100%;">
                                <div style="font-size: 28px; margin-bottom: 10px;">🤖</div>
                                <h4 style="color: #a855f7; font-weight: 700; margin-top: 0;">{l s='GEO Radar & Citations IA' mod='spamcustomercleaner'}</h4>
                                <p style="color: #94a3b8; font-size: 13px; line-height: 1.5;">
                                    {l s='Les acheteurs demandent désormais des conseils d\'achat directement à ChatGPT, Perplexity et Claude. Fexa AI rend votre boutique éligible pour être citée et recommandée n°1.' mod='spamcustomercleaner'}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div style="background: #1e293b; border: 1px solid #334155; border-radius: 10px; padding: 20px; height: 100%;">
                                <div style="font-size: 28px; margin-bottom: 10px;">⚡</div>
                                <h4 style="color: #34d399; font-weight: 700; margin-top: 0;">{l s='Zéro Impact Serveur & Rollback 1-Clic' mod='spamcustomercleaner'}</h4>
                                <p style="color: #94a3b8; font-size: 13px; line-height: 1.5;">
                                    {l s='Connecteur PrestaShop ultra-léger (0 ms d\'impact MySQL). Calculs 100% Cloud sécurisés, sauvegarde automatique et rollback instantané de vos fiches produits.' mod='spamcustomercleaner'}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 25px; padding: 20px; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 8px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
                        <div>
                            <strong style="color: #34d399; font-size: 15px;"><i class="icon-info-circle"></i> {l s='Compatibilité Totale PrestaShop' mod='spamcustomercleaner'} :</strong>
                            <span style="color: #e2e8f0; font-size: 13px; margin-left: 5px;">{l s='PrestaShop 1.7.8, 8.x et 9.x Stable | PHP 7.4 à 8.3+' mod='spamcustomercleaner'}</span>
                        </div>
                        <div>
                            <a href="{$fexa_url|escape:'html':'UTF-8'}" target="_blank" class="btn btn-success" style="background: #10b981; border: none; font-weight: 700; padding: 8px 18px;">
                                {l s='Découvrir fexaai.com' mod='spamcustomercleaner'} ➔
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Deletion Modal -->
<div class="modal fade" id="scc-deletion-modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-danger">
                    <i class="icon-trash"></i> {l s='Suppression en masse des comptes spam' mod='spamcustomercleaner'}
                </h4>
            </div>
            <div class="modal-body">
                <div id="deletion-confirm-view">
                    <div class="alert alert-warning">
                        <h4><i class="icon-warning"></i> {l s='Confirmation requise' mod='spamcustomercleaner'}</h4>
                        <p>{l s='Vous êtes sur le point de supprimer :' mod='spamcustomercleaner'}</p>
                        <ul>
                            <li><strong id="confirm-customers-count">0</strong> {l s='comptes clients spam sélectionnés' mod='spamcustomercleaner'}</li>
                            <li>{l s='Leurs adresses postales associées (si coché)' mod='spamcustomercleaner'}</li>
                            <li>{l s='Leurs paniers vides et liaisons orphelines' mod='spamcustomercleaner'}</li>
                        </ul>
                        {if !$is_pro}
                            <div class="alert alert-info" id="free-limit-alert" style="margin-top: 10px;">
                                <i class="icon-info-circle"></i>
                                {l s='Note Version Gratuite : suppression limitée à' mod='spamcustomercleaner'} <strong id="free-limit-count">{$remaining_free}</strong> {l s='comptes restants sur ce lot. Pour supprimer l\'intégralité en une seule fois, activez la version PRO.' mod='spamcustomercleaner'}
                            </div>
                        {/if}
                        <p class="text-danger">
                            <strong>{l s='Cette action est irréversible.' mod='spamcustomercleaner'}</strong>
                            {l s='Vos vrais clients et vos commandes restent 100% protégés.' mod='spamcustomercleaner'}
                        </p>
                    </div>
                </div>

                <div id="deletion-progress-view" style="display:none;">
                    <h4>{l s='Suppression en cours par lots sécurisés...' mod='spamcustomercleaner'}</h4>
                    <div class="progress progress-striped active" style="height: 25px;">
                        <div class="progress-bar progress-bar-danger" id="scc-progress-bar" role="progressbar" style="width: 0%; font-size: 14px; line-height: 25px;">
                            0%
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6">
                            <span id="progress-status-text">Lot 0 / 0</span>
                        </div>
                        <div class="col-xs-6 text-right">
                            <span id="progress-deleted-count">0 comptes supprimés</span>
                        </div>
                    </div>
                    <div id="deletion-log" class="well well-sm" style="max-height: 150px; overflow-y: auto; margin-top: 15px; font-family: monospace; font-size: 12px; background: #222; color: #a6e22e;">
                        [Prêt au démarrage...]
                    </div>
                </div>

                <div id="deletion-completed-view" style="display:none;" class="text-center">
                    <div class="alert alert-success">
                        <h3><i class="icon-check-circle"></i> {l s='Nettoyage terminé avec succès !' mod='spamcustomercleaner'}</h3>
                        <p id="deletion-success-summary" style="font-size: 16px; margin: 15px 0;"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" id="btn-modal-cancel" data-dismiss="modal">{l s='Annuler' mod='spamcustomercleaner'}</button>
                <button type="button" class="btn btn-danger" id="btn-modal-confirm-delete">
                    <i class="icon-trash"></i> {l s='Confirmer et Lancer la Suppression' mod='spamcustomercleaner'}
                </button>
                <button type="button" class="btn btn-primary" id="btn-modal-close-refresh" style="display:none;" onclick="location.reload();">
                    <i class="icon-refresh"></i> {l s='Terminer et Actualiser la Page' mod='spamcustomercleaner'}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Genisoft PRO Upgrade Required Modal -->
<div class="modal fade" id="scc-upgrade-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: #1e3a8a; color: #fff;">
                <button type="button" class="close" data-dismiss="modal" style="color: #fff;">&times;</button>
                <h4 class="modal-title"><span style="color: #facc15;">⭐</span> {l s='Passez à la Version PRO Illimitée' mod='spamcustomercleaner'}</h4>
            </div>
            <div class="modal-body text-center" style="padding: 30px;">
                <i class="icon-lock" style="font-size: 48px; color: #f59e0b;"></i>
                <h3 style="margin-top: 15px;">{l s='Limite de la version gratuite atteinte' mod='spamcustomercleaner'}</h3>
                <p class="text-muted" style="font-size: 15px; margin: 15px 0;">
                    {l s='Vous avez utilisé vos 50 suppressions gratuites de démonstration. Pour nettoyer l\'intégralité de vos comptes spams et activer le pilote automatique CRON, commandez votre licence PRO.' mod='spamcustomercleaner'}
                </p>
                <a href="{$shop_url|escape:'html':'UTF-8'}" target="_blank" class="btn btn-warning btn-lg" style="font-size: 16px; padding: 12px 25px; margin: 10px 0;">
                    <i class="icon-shopping-cart"></i> {l s='Débloquer la version PRO sur la Boutique Genisoft' mod='spamcustomercleaner'} ➔
                </a>
                <div style="margin-top: 20px;">
                    <a href="javascript:void(0);" onclick="$('#scc-upgrade-modal').modal('hide'); $('#tab-settings-link').click();" class="text-muted">
                        {l s='Vous avez déjà acheté ? Cliquez ici pour saisir votre clé' mod='spamcustomercleaner'}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var sccAjaxUrl = "{$current_ajax_url|escape:'javascript':'UTF-8'}";
    var sccToken = "{$token|escape:'javascript':'UTF-8'}";
    var sccBatchSize = {$cfg_batch_size|intval};
    var sccIsPro = {if $is_pro}true{else}false{/if};
    var sccRemainingFree = {$remaining_free|intval};
    var sccShopUrl = "{$shop_url|escape:'javascript':'UTF-8'}";
    var sccLang = {
        select_at_least_one: "{l s='Veuillez sélectionner au moins un compte spam à supprimer.' mod='spamcustomercleaner' js=1}",
        enter_license_key: "{l s='Veuillez saisir votre clé de licence Genisoft.' mod='spamcustomercleaner' js=1}",
        validating: "{l s='Validation...' mod='spamcustomercleaner' js=1}",
        license_activated: "{l s='Félicitations ! Votre licence PRO est activée avec succès !' mod='spamcustomercleaner' js=1}",
        invalid_license: "{l s='Clé de licence invalide.' mod='spamcustomercleaner' js=1}",
        server_error: "{l s='Erreur de communication avec le serveur.' mod='spamcustomercleaner' js=1}",
        analyzing: "{l s='Analyse...' mod='spamcustomercleaner' js=1}",
        starting: "{l s='Démarrage' mod='spamcustomercleaner' js=1}",
        preparing: "{l s='Préparation de' mod='spamcustomercleaner' js=1}",
        lots: "{l s='lots' mod='spamcustomercleaner' js=1}",
        accounts: "{l s='comptes' mod='spamcustomercleaner' js=1}",
        lot: "{l s='Lot' mod='spamcustomercleaner' js=1}",
        of: "{l s='sur' mod='spamcustomercleaner' js=1}",
        deleted: "{l s='supprimés' mod='spamcustomercleaner' js=1}",
        processed: "{l s='traité :' mod='spamcustomercleaner' js=1}",
        clients: "{l s='clients,' mod='spamcustomercleaner' js=1}",
        addresses_deleted: "{l s='adresses supprimées.' mod='spamcustomercleaner' js=1}",
        done: "{l s='Terminé !' mod='spamcustomercleaner' js=1}",
        accounts_deleted: "{l s='comptes supprimés' mod='spamcustomercleaner' js=1}",
        fake_accounts_deleted: "{l s='faux comptes clients spam supprimés.' mod='spamcustomercleaner' js=1}",
        spam_addresses_deleted: "{l s='adresses postales de spam associées supprimées.' mod='spamcustomercleaner' js=1}",
        orphan_carts_cleaned: "{l s='paniers orphelins nettoyés.' mod='spamcustomercleaner' js=1}",
        safe_guarantee: "{l s='Vos vrais clients et vos commandes restent intacts.' mod='spamcustomercleaner' js=1}",
        free_limit_reached: "{l s='Limite de la version gratuite atteinte.' mod='spamcustomercleaner' js=1}",
        cleaned_test_accounts: "{l s='comptes nettoyés' mod='spamcustomercleaner' js=1}"
    };
</script>
<script type="text/javascript" src="{$module_dir}views/js/admin.js?v={$module_version}"></script>
