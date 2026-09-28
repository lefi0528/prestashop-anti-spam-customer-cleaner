<?php
/**
 * SpamCustomerCleaner - Automated CRON Endpoint
 *
 * @author    Genisoft web
 * @copyright 2026
 * @license   Commercial / Proprietary
 */

include_once dirname(__FILE__) . '/../../config/config.inc.php';
include_once dirname(__FILE__) . '/spamcustomercleaner.php';
include_once dirname(__FILE__) . '/classes/SpamDetector.php';

header('Content-Type: application/json; charset=utf-8');

$token = Tools::getValue('token');
$expectedToken = Configuration::get('SCC_CRON_TOKEN');
if (empty($expectedToken)) {
    $expectedToken = md5(_COOKIE_KEY_ . 'spamcustomercleaner_cron');
    Configuration::updateValue('SCC_CRON_TOKEN', $expectedToken);
}

if (empty($token) || $token !== $expectedToken) {
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => 'Invalid or missing CRON security token.']));
}

if (!SpamDetector::isPro()) {
    $shopUrl = Configuration::get('SCC_SHOP_URL');
    if (empty($shopUrl) || $shopUrl === 'https://shop.genisoft.fr/') {
        $shopUrl = 'https://shop.genisoft.fr/b/kCO5W';
    }

    http_response_code(402);
    die(json_encode([
        'success' => false,
        'error' => 'La fonction CRON nécessite la version PRO de SpamCustomerCleaner.',
        'upgrade_url' => $shopUrl,
    ]));
}

$result = SpamDetector::runCron();
die(json_encode($result));
