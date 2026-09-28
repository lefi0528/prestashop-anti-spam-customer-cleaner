<?php
/**
 * SpamCustomerCleaner - PrestaShop Module
 *
 * @author    Genisoft web
 * @copyright 2026 Genisoft web
 * @license   Commercial / Proprietary
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'spamcustomercleaner/classes/SpamDetector.php';

class AdminSpamCustomerCleanerController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
    }

    public function initContent()
    {
        parent::initContent();

        // Redirect to module configuration or render module content
        $module = Module::getInstanceByName('spamcustomercleaner');
        if ($module) {
            $this->content = $module->getContent();
            $this->context->smarty->assign(['content' => $this->content]);
        }
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
}
