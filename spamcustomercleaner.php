<?php
/**
 * SpamCustomerCleaner - PrestaShop Module
 *
 * @author    Genisoft / LeBiggyFood
 * @copyright 2026
 * @license   Commercial / Proprietary
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/classes/SpamDetector.php';

class SpamCustomerCleaner extends Module
{
    public function __construct()
    {
        $this->name = 'spamcustomercleaner';
        $this->tab = 'administration';
        $this->version = '1.0.1';
        $this->author = 'Genisoft web';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Scanner & Nettoyeur Anti-Spam Clients');
        $this->description = $this->l('Détecte avec précision et supprime en masse les faux comptes spam et leurs adresses sans risque pour vos vrais clients.');
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => '9.99.99'];
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        $defaultConfigs = [
            'SCC_CHECK_URLS' => 1,
            'SCC_CHECK_KEYWORDS' => 1,
            'SCC_CHECK_MIXED_CASE' => 1,
            'SCC_CHECK_CONSONANTS' => 1,
            'SCC_CHECK_VOWELS' => 1,
            'SCC_CHECK_CONSONANT_START' => 1,
            'SCC_CHECK_SAME_NAME' => 1,
            'SCC_CHECK_DISPOSABLE' => 1,
            'SCC_CHECK_TLDS' => 1,
            'SCC_CHECK_RANDOM_EMAIL' => 1,
            'SCC_WHITELIST' => "lefi0\ngenisoft\nadmin\nbiggyfood",
            'SCC_BATCH_SIZE' => 250,
            'SCC_HONEYPOT_ACTIVE' => 1,
            'SCC_SHOP_URL' => 'https://shop.genisoft.fr/',
        ];

        foreach ($defaultConfigs as $key => $val) {
            Configuration::updateValue($key, $val);
        }

        $this->installTab('AdminParentCustomer', 'AdminSpamCustomerCleaner', 'Scanner Anti-Spam');

        return $this->registerHook('displayBackOfficeHeader') &&
               $this->registerHook('validateCustomerFormFields');
    }

    public function uninstall()
    {
        $this->uninstallTab('AdminSpamCustomerCleaner');

        $configs = [
            'SCC_CHECK_URLS',
            'SCC_CHECK_KEYWORDS',
            'SCC_CHECK_MIXED_CASE',
            'SCC_CHECK_CONSONANTS',
            'SCC_CHECK_VOWELS',
            'SCC_CHECK_CONSONANT_START',
            'SCC_CHECK_SAME_NAME',
            'SCC_CHECK_DISPOSABLE',
            'SCC_CHECK_TLDS',
            'SCC_CHECK_RANDOM_EMAIL',
            'SCC_WHITELIST',
            'SCC_BATCH_SIZE',
            'SCC_HONEYPOT_ACTIVE',
        ];

        foreach ($configs as $cfg) {
            Configuration::deleteByName($cfg);
        }

        return parent::uninstall();
    }

    public function installTab($parentClass, $tabClass, $tabName)
    {
        $idParent = (int) Tab::getIdFromClassName($parentClass);
        if (!$idParent) {
            $idParent = (int) Tab::getIdFromClassName('DEFAULT');
        }

        $tab = new Tab();
        $tab->class_name = $tabClass;
        $tab->module = $this->name;
        $tab->id_parent = $idParent;
        $tab->active = 1;

        $languages = Language::getLanguages(true);
        foreach ($languages as $lang) {
            $tab->name[$lang['id_lang']] = ($lang['iso_code'] === 'fr') ? 'Scanner Anti-Spam' : 'Anti-Spam Scanner';
        }

        return $tab->save();
    }

    public function uninstallTab($tabClass)
    {
        $idTab = (int) Tab::getIdFromClassName($tabClass);
        if ($idTab) {
            $tab = new Tab($idTab);
            return $tab->delete();
        }
        return true;
    }

    public function hookDisplayBackOfficeHeader()
    {
        // Only load assets on our module page or controller
        if (Tools::getValue('configure') === $this->name || Tools::getValue('controller') === 'AdminSpamCustomerCleaner') {
            $this->context->controller->addCSS($this->_path . 'views/css/admin.css');
            $this->context->controller->addJS($this->_path . 'views/js/admin.js');
        }
    }

    /**
     * Honeypot / Live Bot Blocking Hook
     */
    public function hookValidateCustomerFormFields($params)
    {
        if (!Configuration::get('SCC_HONEYPOT_ACTIVE', 1)) {
            return;
        }

        $fields = $params['fields'];
        $firstname = '';
        $lastname = '';
        $email = '';

        foreach ($fields as $field) {
            if ($field->getName() === 'firstname') {
                $firstname = $field->getValue();
            } elseif ($field->getName() === 'lastname') {
                $lastname = $field->getValue();
            } elseif ($field->getName() === 'email') {
                $email = $field->getValue();
            }
        }

        $reasons = [];
        $dummy = [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'nb_orders' => 0,
        ];

        if (SpamDetector::isSpam($dummy, $reasons)) {
            foreach ($fields as $field) {
                if ($field->getName() === 'firstname' || $field->getName() === 'email') {
                    $field->addError($this->l('Votre inscription n\'a pas pu être validée. Merci de vérifier vos informations.'));
                }
            }
        }
    }

    /**
     * Module configuration page
     */
    public function getContent()
    {
        // Handle AJAX directly inside getContent for 100% reliability
        if (Tools::getValue('ajax') == 1) {
            $action = Tools::getValue('action');
            if ($action === 'scan') {
                $this->ajaxProcessScan();
            } elseif ($action === 'delete_batch') {
                $this->ajaxProcessDeleteBatch();
            } elseif ($action === 'delete_single') {
                $this->ajaxProcessDeleteSingle();
            } elseif ($action === 'activate_license') {
                $this->ajaxProcessActivateLicense();
            } elseif ($action === 'scan_contact') {
                $this->ajaxProcessScanContact();
            } elseif ($action === 'delete_contact_batch') {
                $this->ajaxProcessDeleteContactBatch();
            }
            exit;
        }

        $output = '';

        // Save license key
        if (Tools::isSubmit('submitSccLicense')) {
            $key = trim(Tools::getValue('SCC_LICENSE_KEY'));
            Configuration::updateValue('SCC_LICENSE_KEY', $key);
            if (SpamDetector::isPro()) {
                $output .= $this->displayConfirmation($this->l('Félicitations ! Votre licence PRO a été validée avec succès. Toutes les fonctionnalités illimitées sont désormais débloquées !'));
            } else {
                $output .= $this->displayError($this->l('Clé de licence non reconnue. Veuillez vérifier votre saisie ou commander votre licence sur notre boutique https://shop.genisoft.fr/.'));
            }
        }

        // Save settings
        if (Tools::isSubmit('submitSccConfig')) {
            Configuration::updateValue('SCC_CHECK_URLS', (int) Tools::getValue('SCC_CHECK_URLS'));
            Configuration::updateValue('SCC_CHECK_KEYWORDS', (int) Tools::getValue('SCC_CHECK_KEYWORDS'));
            Configuration::updateValue('SCC_CHECK_MIXED_CASE', (int) Tools::getValue('SCC_CHECK_MIXED_CASE'));
            Configuration::updateValue('SCC_CHECK_CONSONANTS', (int) Tools::getValue('SCC_CHECK_CONSONANTS'));
            Configuration::updateValue('SCC_CHECK_VOWELS', (int) Tools::getValue('SCC_CHECK_VOWELS'));
            Configuration::updateValue('SCC_CHECK_CONSONANT_START', (int) Tools::getValue('SCC_CHECK_CONSONANT_START'));
            Configuration::updateValue('SCC_CHECK_SAME_NAME', (int) Tools::getValue('SCC_CHECK_SAME_NAME'));
            Configuration::updateValue('SCC_CHECK_DISPOSABLE', (int) Tools::getValue('SCC_CHECK_DISPOSABLE'));
            Configuration::updateValue('SCC_CHECK_TLDS', (int) Tools::getValue('SCC_CHECK_TLDS'));
            Configuration::updateValue('SCC_CHECK_RANDOM_EMAIL', (int) Tools::getValue('SCC_CHECK_RANDOM_EMAIL'));
            Configuration::updateValue('SCC_WHITELIST', Tools::getValue('SCC_WHITELIST'));
            Configuration::updateValue('SCC_BATCH_SIZE', (int) Tools::getValue('SCC_BATCH_SIZE'));
            Configuration::updateValue('SCC_HONEYPOT_ACTIVE', (int) Tools::getValue('SCC_HONEYPOT_ACTIVE'));

            $submittedShopUrl = trim(Tools::getValue('SCC_SHOP_URL'));
            if (!empty($submittedShopUrl)) {
                Configuration::updateValue('SCC_SHOP_URL', $submittedShopUrl);
            }

            $output .= $this->displayConfirmation($this->l('Paramètres de détection mis à jour avec succès.'));
        }

        // CSV Export direct download
        if (Tools::isSubmit('exportSpamCsv')) {
            $this->exportCsv();
            exit;
        }

        $cronToken = Configuration::get('SCC_CRON_TOKEN');
        if (empty($cronToken)) {
            $cronToken = md5(_COOKIE_KEY_ . 'spamcustomercleaner_cron');
            Configuration::updateValue('SCC_CRON_TOKEN', $cronToken);
        }

        $stats = SpamDetector::getGlobalStats();
        $currentToken = Tools::getValue('token');
        $currentAjaxUrl = AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . $currentToken . '&ajax=1';
        $cronUrl = Tools::getShopDomainSsl(true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/cron.php?token=' . $cronToken;

        $getConfig = function ($key, $default = 1) {
            $val = Configuration::get($key);
            if ($val === false || $val === null || $val === '') {
                return $default;
            }
            return (bool) $val;
        };

        $whitelist = Configuration::get('SCC_WHITELIST');
        if ($whitelist === false || $whitelist === null) {
            $whitelist = "lefi0\ngenisoft\nadmin\nbiggyfood";
        }

        $batchSize = (int) Configuration::get('SCC_BATCH_SIZE');
        if ($batchSize <= 0) {
            $batchSize = 250;
        }

        $shopUrl = Configuration::get('SCC_SHOP_URL');
        if (empty($shopUrl)) {
            $shopUrl = 'https://shop.genisoft.fr/';
        }

        $licenseKey = Configuration::get('SCC_LICENSE_KEY');
        if (empty($licenseKey)) {
            $licenseKey = '';
        }

        $this->context->smarty->assign([
            'stats' => $stats,
            'is_pro' => SpamDetector::isPro(),
            'shop_url' => $shopUrl,
            'license_key' => $licenseKey,
            'cron_url' => $cronUrl,
            'free_limit' => SpamDetector::FREE_DELETE_LIMIT,
            'remaining_free' => SpamDetector::getRemainingFreeDeletions(),
            'module_dir' => $this->_path,
            'module_version' => $this->version . '.' . time(),
            'current_ajax_url' => $currentAjaxUrl,
            'token' => $currentToken,
            'config_action_url' => AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . $currentToken,
            'cfg_check_urls' => $getConfig('SCC_CHECK_URLS', 1),
            'cfg_check_keywords' => $getConfig('SCC_CHECK_KEYWORDS', 1),
            'cfg_check_mixed_case' => $getConfig('SCC_CHECK_MIXED_CASE', 1),
            'cfg_check_consonants' => $getConfig('SCC_CHECK_CONSONANTS', 1),
            'cfg_check_vowels' => $getConfig('SCC_CHECK_VOWELS', 1),
            'cfg_check_consonant_start' => $getConfig('SCC_CHECK_CONSONANT_START', 1),
            'cfg_check_same_name' => $getConfig('SCC_CHECK_SAME_NAME', 1),
            'cfg_check_disposable' => $getConfig('SCC_CHECK_DISPOSABLE', 1),
            'cfg_check_tlds' => $getConfig('SCC_CHECK_TLDS', 1),
            'cfg_check_random_email' => $getConfig('SCC_CHECK_RANDOM_EMAIL', 1),
            'cfg_whitelist' => $whitelist,
            'cfg_batch_size' => $batchSize,
            'cfg_honeypot' => $getConfig('SCC_HONEYPOT_ACTIVE', 1),
            'cfg_shop_url' => $shopUrl,
        ]);

        return $output . $this->display(__FILE__, 'views/templates/admin/configure.tpl');
    }

    /**
     * AJAX Action: Run full scan
     */
    public function ajaxProcessScan()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $criteria = SpamDetector::getDefaultCriteria();
            $scanResult = SpamDetector::scan($criteria);

            die(json_encode([
                'success' => true,
                'total_scanned' => $scanResult['total_scanned'],
                'spam_count' => $scanResult['spam_count'],
                'clean_count' => $scanResult['clean_count'],
                'spam_with_addr' => $scanResult['spam_with_addr'],
                'items' => $scanResult['items'],
            ]));
        } catch (Exception $e) {
            die(json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]));
        }
    }

    /**
     * AJAX Action: Delete a batch of customer IDs
     */
    public function ajaxProcessDeleteBatch()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $customerIds = Tools::getValue('customer_ids');
            $deleteAddresses = (bool) Tools::getValue('delete_addresses', 1);

            if (empty($customerIds) || !is_array($customerIds)) {
                die(json_encode([
                    'success' => false,
                    'error' => 'Aucun identifiant client transmis pour la suppression.',
                ]));
            }

            $res = SpamDetector::deleteBatch($customerIds, $deleteAddresses);

            die(json_encode($res));
        } catch (Exception $e) {
            die(json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]));
        }
    }

    /**
     * AJAX Action: Delete a single customer
     */
    public function ajaxProcessDeleteSingle()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $idCustomer = (int) Tools::getValue('id_customer');
            $deleteAddresses = (bool) Tools::getValue('delete_addresses', 1);

            if ($idCustomer <= 0) {
                die(json_encode(['success' => false, 'error' => 'ID client invalide.']));
            }

            $res = SpamDetector::deleteBatch([$idCustomer], $deleteAddresses);

            die(json_encode([
                'success' => true,
                'customers_deleted' => $res['customers_deleted'],
                'addresses_deleted' => $res['addresses_deleted'],
            ]));
        } catch (Exception $e) {
            die(json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]));
        }
    }

    /**
     * AJAX Action: Validate and activate license key
     */
    public function ajaxProcessActivateLicense()
    {
        header('Content-Type: application/json; charset=utf-8');

        $key = trim(Tools::getValue('license_key'));
        Configuration::updateValue('SCC_LICENSE_KEY', $key);

        if (SpamDetector::isPro()) {
            die(json_encode([
                'success' => true,
                'is_pro' => true,
                'message' => 'Licence PRO activée avec succès !',
            ]));
        } else {
            die(json_encode([
                'success' => false,
                'is_pro' => false,
                'error' => 'Format de clé de licence invalide.',
            ]));
        }
    }

    /**
     * AJAX Action: Scan contact spam threads
     */
    public function ajaxProcessScanContact()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $threads = SpamDetector::scanContactSpam();
            die(json_encode([
                'success' => true,
                'total_spam_contact' => count($threads),
                'items' => $threads,
                'is_pro' => SpamDetector::isPro(),
            ]));
        } catch (Exception $e) {
            die(json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]));
        }
    }

    /**
     * AJAX Action: Delete batch of contact spam threads
     */
    public function ajaxProcessDeleteContactBatch()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!SpamDetector::isPro()) {
            die(json_encode([
                'success' => false,
                'upgrade_required' => true,
                'error' => 'Le nettoyage automatique des messages contact requiert la version PRO.',
            ]));
        }

        try {
            $threadIds = Tools::getValue('thread_ids');
            if (empty($threadIds) || !is_array($threadIds)) {
                die(json_encode(['success' => false, 'error' => 'Aucun message sélectionné.']));
            }
            $deleted = SpamDetector::deleteContactSpamBatch($threadIds);
            die(json_encode([
                'success' => true,
                'deleted_count' => $deleted,
            ]));
        } catch (Exception $e) {
            die(json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]));
        }
    }

    /**
     * Export all flagged spam customers as CSV download
     */
    public function exportCsv()
    {
        $scan = SpamDetector::scan();
        $filename = 'spam_customers_backup_' . date('Y-m-d_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $out = fopen('php://output', 'w');
        // UTF-8 BOM for Excel
        fputs($out, "\xEF\xBB\xBF");

        fputcsv($out, ['ID Client', 'Prénom', 'Nom', 'Email', 'Date Inscription', 'Nb Adresses', 'Motifs Détection'], ';');

        foreach ($scan['items'] as $item) {
            fputcsv($out, [
                $item['id_customer'],
                $item['firstname'],
                $item['lastname'],
                $item['email'],
                $item['date_add'],
                $item['nb_addr'],
                implode(' | ', $item['reasons']),
            ], ';');
        }

        fclose($out);
        exit;
    }
}
